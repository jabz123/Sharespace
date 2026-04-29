<?php

require_once __DIR__ . '/../db.php';

class AIVerificationController
{
    public function __construct()
    {
        $this->ensureSchema();
        $this->seedMissingAnalyses();
    }

    private function ensureSchema(): void
    {
        DB::execute(
            'CREATE TABLE IF NOT EXISTS ai_trainer_analyses (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    private function seedMissingAnalyses(): void
    {
        $articles = DB::query(
            'SELECT a.id, a.trust_score
             FROM articles a
             LEFT JOIN ai_trainer_analyses ata ON ata.article_id = a.id
             WHERE ata.id IS NULL'
        );

        foreach ($articles as $article) {
            $trust = max(0, min(100, (int) ($article['trust_score'] ?? 80)));
            $factual = max(0, min(100, $trust + 4));
            $source = max(0, min(100, $trust - 2));
            $bias = max(0, min(100, $trust - 6));

            DB::execute(
                'INSERT INTO ai_trainer_analyses
                    (article_id, trust_score, factual_accuracy, source_quality, bias_detection, analysed_at)
                 VALUES (?, ?, ?, ?, ?, NOW())',
                [(int) $article['id'], $trust, $factual, $source, $bias]
            );
        }
    }

    public function getDashboardStats(): array
    {
        $row = DB::first(
            'SELECT
                COUNT(*) AS total_articles,
                ROUND(COALESCE(AVG(trust_score), 0)) AS average_trust_score,
                SUM(CASE WHEN trust_score >= 80 THEN 1 ELSE 0 END) AS high_credibility,
                SUM(CASE WHEN trust_score < 60 THEN 1 ELSE 0 END) AS low_credibility
             FROM ai_trainer_analyses'
        ) ?? [];

        return [
            'totalArticles' => (int) ($row['total_articles'] ?? 0),
            'averageTrustScore' => (int) ($row['average_trust_score'] ?? 0),
            'highCredibility' => (int) ($row['high_credibility'] ?? 0),
            'lowCredibility' => (int) ($row['low_credibility'] ?? 0),
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

}
