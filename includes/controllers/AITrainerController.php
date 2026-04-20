<?php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../entities/User.php';

class AITrainerController
{
    public function __construct()
    {
        $this->ensureSchema();
        $this->seedMissingAnalyses();
        $this->ensureDefaultSettings();
    }

    public function requireTrainer(User $user): void
    {
        if ($user->role !== 'ai_trainer') {
            header('Location: /dashboard.php');
            exit;
        }
    }

    private function ensureSchema(): void
    {
        DB::execute(
            "CREATE TABLE IF NOT EXISTS ai_trainer_analyses (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                article_id INT NOT NULL,
                trust_score TINYINT NOT NULL DEFAULT 80,
                factual_accuracy TINYINT NOT NULL DEFAULT 80,
                source_quality TINYINT NOT NULL DEFAULT 80,
                bias_detection TINYINT NOT NULL DEFAULT 80,
                analysed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                notes TEXT NULL,
                UNIQUE KEY uq_ai_trainer_article (article_id),
                INDEX idx_ai_trainer_trust_score (trust_score),
                INDEX idx_ai_trainer_analysed_at (analysed_at),
                CONSTRAINT fk_ai_trainer_article
                    FOREIGN KEY (article_id) REFERENCES articles(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        DB::execute(
            "CREATE TABLE IF NOT EXISTS ai_trainer_calibration_settings (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) NOT NULL,
                setting_value VARCHAR(255) NOT NULL,
                updated_by INT NULL,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_ai_trainer_setting (setting_key),
                INDEX idx_ai_trainer_setting_updated_by (updated_by)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    private function seedMissingAnalyses(): void
    {
        $articles = DB::query(
            "SELECT a.id, a.trust_score
             FROM articles a
             LEFT JOIN ai_trainer_analyses ata ON ata.article_id = a.id
             WHERE ata.id IS NULL"
        );

        foreach ($articles as $article) {
            $trust = max(0, min(100, (int)($article['trust_score'] ?? 80)));
            $factual = max(0, min(100, $trust + 4));
            $source = max(0, min(100, $trust - 2));
            $bias = max(0, min(100, $trust - 6));

            DB::execute(
                "INSERT INTO ai_trainer_analyses
                    (article_id, trust_score, factual_accuracy, source_quality, bias_detection, analysed_at)
                 VALUES (?, ?, ?, ?, ?, NOW())",
                [(int)$article['id'], $trust, $factual, $source, $bias]
            );
        }
    }

    private function ensureDefaultSettings(): void
    {
        $defaults = [
            'publishing_threshold' => '60',
            'factual_accuracy_weight' => '60',
            'source_quality_weight' => '60',
            'bias_detection_weight' => '60',
            'strict_mode' => '0',
        ];

        foreach ($defaults as $key => $value) {
            DB::execute(
                "INSERT INTO ai_trainer_calibration_settings (setting_key, setting_value)
                 VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_key = setting_key",
                [$key, $value]
            );
        }
    }

    public function getDashboardStats(): array
    {
        $row = DB::first(
            "SELECT
                COUNT(*) AS total_articles,
                ROUND(COALESCE(AVG(trust_score), 0)) AS average_trust_score,
                SUM(CASE WHEN trust_score >= 80 THEN 1 ELSE 0 END) AS high_credibility,
                SUM(CASE WHEN trust_score < 60 THEN 1 ELSE 0 END) AS low_credibility
             FROM ai_trainer_analyses"
        ) ?? [];

        return [
            'totalArticles' => (int)($row['total_articles'] ?? 0),
            'averageTrustScore' => (int)($row['average_trust_score'] ?? 0),
            'highCredibility' => (int)($row['high_credibility'] ?? 0),
            'lowCredibility' => (int)($row['low_credibility'] ?? 0),
        ];
    }

    public function getRecentAnalyses(int $limit = 8): array
    {
        $limit = max(1, min(50, $limit));

        return DB::query(
            "SELECT
                a.id,
                a.title,
                c.name AS category_name,
                ata.trust_score,
                ata.analysed_at
             FROM ai_trainer_analyses ata
             INNER JOIN articles a ON a.id = ata.article_id
             INNER JOIN categories c ON c.id = a.category_id
             ORDER BY ata.analysed_at DESC, ata.id DESC
             LIMIT {$limit}"
        );
    }

    public function getDatasetArticles(int $limit = 25): array
    {
        $limit = max(1, min(100, $limit));

        return DB::query(
            "SELECT
                a.id,
                a.title,
                c.name AS category_name,
                ata.trust_score,
                ata.analysed_at
             FROM ai_trainer_analyses ata
             INNER JOIN articles a ON a.id = ata.article_id
             INNER JOIN categories c ON c.id = a.category_id
             ORDER BY ata.analysed_at DESC, a.published_at DESC
             LIMIT {$limit}"
        );
    }

    public function getCredibilityBreakdown(): array
    {
        $row = DB::first(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN trust_score >= 80 THEN 1 ELSE 0 END) AS high_count,
                SUM(CASE WHEN trust_score BETWEEN 60 AND 79 THEN 1 ELSE 0 END) AS medium_count,
                SUM(CASE WHEN trust_score < 60 THEN 1 ELSE 0 END) AS low_count
             FROM ai_trainer_analyses"
        ) ?? [];

        $total = max(1, (int)($row['total'] ?? 0));
        $high = (int)($row['high_count'] ?? 0);
        $medium = (int)($row['medium_count'] ?? 0);
        $low = (int)($row['low_count'] ?? 0);

        return [
            'high' => ['count' => $high, 'percent' => (int)round(($high / $total) * 100)],
            'medium' => ['count' => $medium, 'percent' => (int)round(($medium / $total) * 100)],
            'low' => ['count' => $low, 'percent' => (int)round(($low / $total) * 100)],
        ];
    }

    public function getTrustByCategory(): array
    {
        return DB::query(
            "SELECT
                c.name AS category_name,
                COUNT(*) AS article_count,
                ROUND(AVG(ata.trust_score)) AS average_trust_score
             FROM ai_trainer_analyses ata
             INNER JOIN articles a ON a.id = ata.article_id
             INNER JOIN categories c ON c.id = a.category_id
             GROUP BY c.id, c.name
             ORDER BY average_trust_score DESC, c.name ASC"
        );
    }

    public function getCalibrationSettings(): array
    {
        $rows = DB::query('SELECT setting_key, setting_value FROM ai_trainer_calibration_settings');
        $settings = [];

        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return [
            'publishing_threshold' => (int)($settings['publishing_threshold'] ?? 60),
            'factual_accuracy_weight' => (int)($settings['factual_accuracy_weight'] ?? 60),
            'source_quality_weight' => (int)($settings['source_quality_weight'] ?? 60),
            'bias_detection_weight' => (int)($settings['bias_detection_weight'] ?? 60),
            'strict_mode' => (int)($settings['strict_mode'] ?? 0),
        ];
    }

    public function saveCalibrationSettings(array $input, User $user): void
    {
        $settings = [
            'publishing_threshold' => $this->rangeValue($input['publishing_threshold'] ?? 60),
            'factual_accuracy_weight' => $this->rangeValue($input['factual_accuracy_weight'] ?? 60),
            'source_quality_weight' => $this->rangeValue($input['source_quality_weight'] ?? 60),
            'bias_detection_weight' => $this->rangeValue($input['bias_detection_weight'] ?? 60),
            'strict_mode' => isset($input['strict_mode']) ? 1 : 0,
        ];

        foreach ($settings as $key => $value) {
            DB::execute(
                "INSERT INTO ai_trainer_calibration_settings (setting_key, setting_value, updated_by)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    setting_value = VALUES(setting_value),
                    updated_by = VALUES(updated_by),
                    updated_at = NOW()",
                [$key, (string)$value, $user->id]
            );
        }
    }

    public function getRecentArticles(int $limit = 3): array
    {
        $limit = max(1, min(12, $limit));

        return DB::query(
            "SELECT
                a.id,
                a.title,
                a.excerpt,
                a.image_path,
                a.published_at,
                u.full_name AS author_name,
                c.name AS category_name,
                ata.trust_score
             FROM articles a
             INNER JOIN users u ON u.id = a.author_id
             INNER JOIN categories c ON c.id = a.category_id
             LEFT JOIN ai_trainer_analyses ata ON ata.article_id = a.id
             WHERE a.status = 'published'
             ORDER BY a.published_at DESC, a.id DESC
             LIMIT {$limit}"
        );
    }

    private function rangeValue(mixed $value): int
    {
        return max(0, min(100, (int)$value));
    }
}
