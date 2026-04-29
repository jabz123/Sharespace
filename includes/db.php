<?php

// handles database connection for the system using pdo
// provides helper functions to run sql queries and interact with the database
// used by controllers to retrieve, insert, update and delete data
// returns query results as arrays which controllers can convert into entity objects

//called by controllers to get db connection and run queries. 
//also has helper functions to ensure certain tables and columns exist for new features without 
//needing manual migrations.

require_once __DIR__ . '/../config.php';

class DB
{
    private static ?PDO $pdo = null;
    private static bool $categoryExpertsReady = false;
    private static bool $articleReviewWorkflowReady = false;
    private static bool $siteFeedbackSentimentReady = false;

    public static function get(): PDO //returns db connection using PDO
    {if (self::$pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            DB_HOST,
            DB_PORT,
            DB_NAME
        );
        self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        // Keep MySQL session timestamps aligned with Singapore time for NOW()/CURRENT_TIMESTAMP writes.
        self::$pdo->exec("SET time_zone = '+08:00'");
    }
        return self::$pdo;
    }

    //return all rows as array of associative arrays.
    //use sql prepared statements with ? placeholders
    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    //return first row only
    public static function first(string $sql, array $params = []): ?array
    {
        $rows = self::query($sql, $params);
        return $rows[0] ?? null;
    }

    //return no of affected rows
    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    //return last inserted row
    public static function lastId(): int
    {
        return (int) self::get()->lastInsertId();
    }

    private static function columnExists(string $table, string $column): bool
    {
        $row = self::first(
            'SELECT 1
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
             LIMIT 1',
            [DB_NAME, $table, $column]
        );

        return $row !== null;
    }

    public static function ensureCategoryExpertsTable(): void
    {
        if (self::$categoryExpertsReady) {
            return;
        }

        self::execute(
            'CREATE TABLE IF NOT EXISTS category_experts (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                category_id INT NOT NULL,
                user_id INT NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_category_expert (category_id, user_id),
                KEY idx_category_experts_user (user_id),
                CONSTRAINT fk_category_experts_category
                    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
                CONSTRAINT fk_category_experts_user
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        self::execute(
            'INSERT IGNORE INTO category_experts (category_id, user_id)
             SELECT id, admin_user_id
             FROM categories
             WHERE admin_user_id IS NOT NULL'
        );

        self::$categoryExpertsReady = true;
    }

    public static function ensureArticleReviewWorkflow(): void
    {
        if (self::$articleReviewWorkflowReady) {
            return;
        }

        self::ensureCategoryExpertsTable();

        self::execute(
            'CREATE TABLE IF NOT EXISTS article_expert_reviews (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                article_id INT NOT NULL,
                user_id INT NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT "pending",
                reviewed_at DATETIME DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_article_expert_review (article_id, user_id),
                KEY idx_article_expert_reviews_user (user_id),
                KEY idx_article_expert_reviews_status (status),
                CONSTRAINT fk_article_expert_reviews_article
                    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
                CONSTRAINT fk_article_expert_reviews_user
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        if (!self::columnExists('articles', 'review_notice')) {
            self::execute(
                'ALTER TABLE articles
                 ADD COLUMN review_notice TEXT DEFAULT NULL'
            );
        }
        if (!self::columnExists('articles', 'review_notice_pending')) {
            self::execute(
                'ALTER TABLE articles
                 ADD COLUMN review_notice_pending TINYINT(1) NOT NULL DEFAULT 0'
            );
        }
        if (!self::columnExists('articles', 'source_url')) {
            self::execute(
                'ALTER TABLE articles
                 ADD COLUMN source_url VARCHAR(2048) DEFAULT NULL'
            );
        }
        if (!self::columnExists('articles', 'verification_fingerprint')) {
            self::execute(
                'ALTER TABLE articles
                 ADD COLUMN verification_fingerprint VARCHAR(64) DEFAULT NULL'
            );
        }
        if (!self::columnExists('articles', 'verification_payload')) {
            self::execute(
                'ALTER TABLE articles
                 ADD COLUMN verification_payload LONGTEXT DEFAULT NULL'
            );
        }
        if (!self::columnExists('articles', 'verification_checked_at')) {
            self::execute(
                'ALTER TABLE articles
                 ADD COLUMN verification_checked_at DATETIME DEFAULT NULL'
            );
        }

        self::$articleReviewWorkflowReady = true;
    }

    public static function ensureSiteFeedbackSentimentColumns(): void
    {
        if (self::$siteFeedbackSentimentReady) {
            return;
        }

        if (!self::columnExists('site_feedback', 'sentiment_label')) {
            self::execute(
                'ALTER TABLE site_feedback
                 ADD COLUMN sentiment_label VARCHAR(32) DEFAULT NULL'
            );
        }

        if (!self::columnExists('site_feedback', 'sentiment_score')) {
            self::execute(
                'ALTER TABLE site_feedback
                 ADD COLUMN sentiment_score DECIMAL(4,3) DEFAULT NULL'
            );
        }

        if (!self::columnExists('site_feedback', 'sentiment_status')) {
            self::execute(
                "ALTER TABLE site_feedback
                 ADD COLUMN sentiment_status VARCHAR(32) NOT NULL DEFAULT 'pending'"
            );
        }

        self::$siteFeedbackSentimentReady = true;
    }
}
