<?php

// handles article-related logic for the system
// retrieves articles and categories from the database
// creates, updates and deletes articles
// theres no html output from here, only article/category entities or result arrays

require_once __DIR__ . '/../entities/Article.php';
require_once __DIR__ . '/../entities/Category.php';
require_once __DIR__ . '/../AuditLogger.php';

class ArticleController {
    private function ensureReviewWorkflow(): void {
        DB::ensureArticleReviewWorkflow();
    }

    private function resolveTrustScore(array $input): int {
        $score = (int)($input['trust_score'] ?? 80);
        return max(0, min(100, $score));
    }

    private function assignedExpertIdsForCategory(int $categoryId): array {
        $this->ensureReviewWorkflow();

        $rows = DB::query(
            'SELECT user_id
             FROM category_experts
             WHERE category_id = ?
             ORDER BY created_at, id',
            [$categoryId]
        );

        return array_map(fn($row) => (int)$row['user_id'], $rows);
    }

    private function initializeExpertReviews(int $articleId, int $categoryId): array {
        $expertIds = $this->assignedExpertIdsForCategory($categoryId);
        if (empty($expertIds)) {
            return ['error' => 'This category has no assigned experts yet. Ask an admin to assign category experts before publishing.'];
        }

        DB::execute('DELETE FROM article_expert_reviews WHERE article_id = ?', [$articleId]);
        foreach ($expertIds as $expertId) {
            DB::execute(
                'INSERT INTO article_expert_reviews (article_id, user_id, status)
                 VALUES (?, ?, ?)',
                [$articleId, $expertId, 'pending']
            );
        }

        return ['ok' => true];
    }

    public function clearReviewNotice(int $articleId, int $authorId): void {
        $this->ensureReviewWorkflow();

        DB::execute(
            'UPDATE articles
             SET review_notice_pending = 0
             WHERE id = ? AND author_id = ?',
            [$articleId, $authorId]
        );
    }

    //returns n most recently published articles
    //maybe change this to recommended or some shit in future ig
    //returns Article[] array of objects
    public function getRecent(int $limit = 6): array {
        $rows = DB::query(
            'SELECT a.*, u.full_name AS author_name, c.name AS category_name,
            COUNT(DISTINCT v.id) AS view_count,
            COUNT(DISTINCT f.id) AS flag_count
            FROM articles a
            JOIN users u ON u.id = a.author_id
            JOIN categories c ON c.id = a.category_id
            LEFT JOIN article_views v ON v.article_id = a.id
            LEFT JOIN article_flags f ON f.article_id = a.id
            WHERE a.status = "published"
            GROUP BY a.id
            ORDER BY a.published_at DESC
            LIMIT ?',
            [$limit]
        );

        return array_map(fn($r) => new Article($r), $rows);
    }

    //returns n most recently published articles for ladning page preview.
    //returns Article[] array of objects
    public function getPreview(int $limit = 3): array {
        return $this->getRecent($limit);
    }


    //returns single article by id, or null if nothing found
    // 
    public function getById(int $id): ?Article {
        $row = DB::first(
            'SELECT a.*, u.full_name AS author_name, c.name AS category_name,
             COUNT(DISTINCT v.id) AS view_count
             FROM articles a
             JOIN users u ON u.id = a.author_id
             JOIN categories c ON c.id = a.category_id
             LEFT JOIN article_views v ON v.article_id = a.id
             WHERE a.id = ? AND a.status = "published"
             GROUP BY a.id',
            [$id]
        );
        return $row ? new Article($row) : null;
    }

    // edit articles including article draft 
    public function getByIdForAuthor(int $id, int $userId): ?Article {
    $this->ensureReviewWorkflow();
    $row = DB::first(
            "SELECT a.*, u.full_name AS author_name, c.name AS category_name
            FROM articles a
            JOIN users u ON u.id = a.author_id
            JOIN categories c ON c.id = a.category_id
            WHERE a.id = ? AND a.author_id = ?",
            [$id, $userId]
        );

        return $row ? new Article($row) : null;
    }


    //return all categories for write article
    //returns Category[] array
    public function getAllCategories(): array {
        $rows = DB::query('SELECT * FROM categories ORDER BY name');
        return array_map(fn($r) => new Category($r), $rows);
    }


    //returns all articles written by specific user, sort by date newest first
    //return Article[] array
    public function getByAuthor(int $authorId): array {
        $this->ensureReviewWorkflow();
        $rows = DB::query(
            'SELECT a.*, u.full_name AS author_name, c.name AS category_name,
             COUNT(DISTINCT v.id) AS view_count
             FROM articles a
             JOIN users u ON u.id = a.author_id
             JOIN categories c ON c.id = a.category_id
             LEFT JOIN article_views v ON v.article_id = a.id
             WHERE a.author_id = ? AND a.status = "published"
             GROUP BY a.id
             ORDER BY a.published_at DESC',
            [$authorId]
        );
        return array_map(fn($r) => new Article($r), $rows);
    }
    //returns all draft articles written by specific user
    //return Article[] array
    public function getDraftsByAuthor(int $authorId): array {
        $this->ensureReviewWorkflow();
        $rows = DB::query(
            'SELECT a.*, u.full_name AS author_name, c.name AS category_name
            FROM articles a
            JOIN users u ON u.id = a.author_id
            JOIN categories c ON c.id = a.category_id
            WHERE a.author_id = ? AND a.status = "draft"
            ORDER BY a.updated_at DESC',
            [$authorId]
        );
        return array_map(fn($r) => new Article($r), $rows);
    }

    public function getPendingByAuthor(int $authorId): array {
        $this->ensureReviewWorkflow();

        $rows = DB::query(
            'SELECT a.*, u.full_name AS author_name, c.name AS category_name
             FROM articles a
             JOIN users u ON u.id = a.author_id
             JOIN categories c ON c.id = a.category_id
             WHERE a.author_id = ? AND a.status = "pending"
             ORDER BY a.updated_at DESC',
            [$authorId]
        );
        return array_map(fn($r) => new Article($r), $rows);
    }

    //update existing article only author ownselfd can update
    //return ['ok' => true] or ['error' => '...']
    public function update(int $articleId, int $authorId, array $input): array {
        $this->ensureReviewWorkflow();
        $title      = trim($input['title']       ?? '');
        $excerpt    = trim($input['excerpt']     ?? '');
        $content    = trim($input['content']     ?? '');
        $categoryId = (int)($input['category_id'] ?? 0);
        $status = $input['status'] ?? null;
        $trustScore = $this->resolveTrustScore($input);

        if (!$title || !$excerpt || !$content || !$categoryId) {
            return ['error' => 'All fields are required.'];
        }
        if (!DB::first('SELECT id FROM categories WHERE id = ?', [$categoryId])) {
            return ['error' => 'Invalid category selected.'];
        }
        if (!DB::first('SELECT id FROM articles WHERE id = ? AND author_id = ?', [$articleId, $authorId])) {
            return ['error' => 'Article not found or permission denied.'];
        }

        $imagePath = $input['image_path'] ?? null;

        //  GET CURRENT STATUS FROM DB
        $current = DB::first('SELECT status, title FROM articles WHERE id = ?', [$articleId]);

        $status = $input['status'] ?? null;

        // Set published_at the first time a non-published article becomes published.
        $setPublishedAt = '';
        if (($current['status'] ?? '') !== 'published' && $status === 'published') {
            $setPublishedAt = ', published_at = NOW()';
        }

        DB::execute(
            "UPDATE articles
            SET title = ?, excerpt = ?, content = ?, category_id = ?, trust_score = ?, image_path = ?, status = ? $setPublishedAt, updated_at = NOW()
            WHERE id = ? AND author_id = ?",
            [$title, $excerpt, $content, $categoryId, $trustScore, $imagePath, $status, $articleId, $authorId]
        );

        $action = $status === 'draft' ? 'save_draft' : (($current['status'] ?? '') === 'draft' ? 'submit_content' : 'update_content');
        AuditLogger::log($authorId, $action, 'Article', $articleId, 'Updated article: ' . ($current['title'] ?? $title));

        return ['ok' => true];
    }

    //delete article, only author ownself can delete. comments also will all delete
    //also return ['ok' => true] or ['error' => '...']
    public function delete(int $articleId, int $authorId): array {
        $article = DB::first(
            'SELECT title FROM articles WHERE id = ? AND author_id = ?',
            [$articleId, $authorId]
        );

        $affected = DB::execute(
            'DELETE FROM articles WHERE id = ? AND author_id = ?',
            [$articleId, $authorId]
        );
        if ($affected === 0) {
            return ['error' => 'Article not found or permission denied.'];
        }

        AuditLogger::log($authorId, 'delete_content', 'Article', $articleId, 'Deleted article: ' . ($article['title'] ?? ('ID ' . $articleId)));

        return ['ok' => true];
    }

    //vvalidate article written by user and insert into db, returns result array with ok or error 
    
    public function publish(int $authorId, array $input): array {
        $this->ensureReviewWorkflow();
        $title      = trim($input['title']       ?? '');
        $excerpt    = trim($input['excerpt']     ?? '');
        $content    = trim($input['content']     ?? '');
        $categoryId = (int)($input['category_id'] ?? 0);
        $imagePath = $input['image_path'] ?? null;
        $trustScore = $this->resolveTrustScore($input);
       
        if (!$title || !$excerpt || !$content || !$categoryId) {
            return ['error' => 'All fields are required.'];
        }
       
        if (!DB::first('SELECT id FROM categories WHERE id = ?', [$categoryId])) {
            return ['error' => 'Invalid category selected.'];
        }

        DB::execute(
        'INSERT INTO articles (title, excerpt, content, author_id, category_id, trust_score, image_path, status, published_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())',
        [$title, $excerpt, $content, $authorId, $categoryId, $trustScore, $imagePath, 'published']
         );

        $articleId = DB::lastId();
        AuditLogger::log($authorId, 'submit_content', 'Article', $articleId, 'Published article: ' . $title);

        return ['ok' => true, 'id' => $articleId];
    }

    public function submitForExpertReview(int $authorId, array $input): array {
        $this->ensureReviewWorkflow();

        $title      = trim($input['title'] ?? '');
        $excerpt    = trim($input['excerpt'] ?? '');
        $content    = trim($input['content'] ?? '');
        $categoryId = (int)($input['category_id'] ?? 0);
        $imagePath  = $input['image_path'] ?? null;
        $trustScore = $this->resolveTrustScore($input);

        if (!$title || !$excerpt || !$content || !$categoryId) {
            return ['error' => 'All fields are required.'];
        }
        if (!DB::first('SELECT id FROM categories WHERE id = ?', [$categoryId])) {
            return ['error' => 'Invalid category selected.'];
        }

        $pdo = DB::get();
        $pdo->beginTransaction();

        try {
            DB::execute(
                'INSERT INTO articles (title, excerpt, content, author_id, category_id, trust_score, image_path, status, review_notice, review_notice_pending)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL, 0)',
                [$title, $excerpt, $content, $authorId, $categoryId, $trustScore, $imagePath, 'pending']
            );
            $articleId = DB::lastId();

            $reviewInit = $this->initializeExpertReviews($articleId, $categoryId);
            if (isset($reviewInit['error'])) {
                $pdo->rollBack();
                return $reviewInit;
            }

            AuditLogger::log($authorId, 'submit_content', 'Article', $articleId, 'Submitted article for category expert review: ' . $title);

            $pdo->commit();
            return ['ok' => true, 'id' => $articleId];
        } catch (\Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $error;
        }
    }

    public function resubmitForExpertReview(int $articleId, int $authorId, array $input): array {
        $this->ensureReviewWorkflow();

        $title      = trim($input['title'] ?? '');
        $excerpt    = trim($input['excerpt'] ?? '');
        $content    = trim($input['content'] ?? '');
        $categoryId = (int)($input['category_id'] ?? 0);
        $trustScore = $this->resolveTrustScore($input);
        $imagePath  = $input['image_path'] ?? null;

        if (!$title || !$excerpt || !$content || !$categoryId) {
            return ['error' => 'All fields are required.'];
        }
        if (!DB::first('SELECT id FROM categories WHERE id = ?', [$categoryId])) {
            return ['error' => 'Invalid category selected.'];
        }
        if (!DB::first('SELECT id FROM articles WHERE id = ? AND author_id = ?', [$articleId, $authorId])) {
            return ['error' => 'Article not found or permission denied.'];
        }

        $current = DB::first('SELECT title FROM articles WHERE id = ?', [$articleId]);
        $pdo = DB::get();
        $pdo->beginTransaction();

        try {
            DB::execute(
                'UPDATE articles
                 SET title = ?, excerpt = ?, content = ?, category_id = ?, trust_score = ?, image_path = ?, status = ?, review_notice = NULL, review_notice_pending = 0, updated_at = NOW()
                 WHERE id = ? AND author_id = ?',
                [$title, $excerpt, $content, $categoryId, $trustScore, $imagePath, 'pending', $articleId, $authorId]
            );

            $reviewInit = $this->initializeExpertReviews($articleId, $categoryId);
            if (isset($reviewInit['error'])) {
                $pdo->rollBack();
                return $reviewInit;
            }

            AuditLogger::log($authorId, 'submit_content', 'Article', $articleId, 'Resubmitted article for category expert review: ' . ($current['title'] ?? $title));

            $pdo->commit();
            return ['ok' => true, 'id' => $articleId];
        } catch (\Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $error;
        }
    }

    public function getByCategory($category = null, $sort = 'recent', $search = null, $limit = 12, $offset = 0): array {

        $sql = 'SELECT a.*, u.full_name AS author_name, c.name AS category_name,
                COUNT(DISTINCT v.id) AS view_count,
                COUNT(DISTINCT f.id) AS flag_count
                FROM articles a
                JOIN users u ON u.id = a.author_id
                JOIN categories c ON c.id = a.category_id
                LEFT JOIN article_views v ON v.article_id = a.id
                LEFT JOIN article_flags f ON f.article_id = a.id
                WHERE a.status = "published"';
                

        $conditions = [];
        $params = [];

        if ($category) {
            $conditions[] = 'LOWER(c.name) = ?';
            $params[] = strtolower($category);
        }

        if ($search) {
            $conditions[] = 'a.title LIKE ?';
            $params[] = "%$search%";
        }

        if ($conditions) {
            $sql .= ' AND ' . implode(' AND ', $conditions);
        }

        $sql .= ' GROUP BY a.id';

        if ($sort === 'recent') {
            $sql .= ' ORDER BY a.published_at DESC';
        } else {
            $sql .= ' ORDER BY a.trust_score DESC, a.published_at DESC';
        }

        $sql .= ' LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        $rows = DB::query($sql, $params);

        return array_map(fn($r) => new Article($r), $rows);
    }
    

    public function countByCategory($category = null, $search = null): int {

        $sql = "SELECT COUNT(*) as total
                FROM articles a
                JOIN categories c ON c.id = a.category_id
                WHERE a.status = 'published'";

        $params = [];

        if ($category) {
            $sql .= " AND LOWER(c.name) = ?";
            $params[] = strtolower($category);
        }

        if ($search) {
            $sql .= " AND a.title LIKE ?";
            $params[] = "%$search%";
        }

        return (int) DB::first($sql, $params)['total'];
    }

    // returns all articles (published + suspended) for a given author in a given category
    // used by category admin's writer-articles page
    // returns Article[] array
    public function getByAuthorAndCategory(int $authorId, int $categoryId): array {
        $rows = DB::query(
            "SELECT a.*, u.full_name AS author_name, c.name AS category_name,
             COUNT(DISTINCT v.id) AS view_count,
             COUNT(DISTINCT f.id) AS flag_count
             FROM articles a
             JOIN users u ON u.id = a.author_id
             JOIN categories c ON c.id = a.category_id
             LEFT JOIN article_views v ON v.article_id = a.id
             LEFT JOIN article_flags f ON f.article_id = a.id
             WHERE a.author_id = ? AND a.category_id = ? AND a.status IN ('published', 'suspended')
             GROUP BY a.id
             ORDER BY a.published_at DESC",
            [$authorId, $categoryId]
        );
        return array_map(fn($r) => new Article($r), $rows);
    }

    // returns all articles (published + suspended) for a given category id
    // optionally filters by a search term matching title or author name
    // used by category admin's category articles page
    // returns Article[] array
    public function getAllByCategory(int $categoryId, ?string $search = null): array {
        $sql = "SELECT a.*, u.full_name AS author_name, c.name AS category_name,
             COUNT(DISTINCT v.id) AS view_count,
             COUNT(DISTINCT f.id) AS flag_count
             FROM articles a
             JOIN users u ON u.id = a.author_id
             JOIN categories c ON c.id = a.category_id
             LEFT JOIN article_views v ON v.article_id = a.id
             LEFT JOIN article_flags f ON f.article_id = a.id
             WHERE a.category_id = ? AND a.status IN ('published', 'suspended')";

        $params = [$categoryId];

        if ($search) {
            $sql .= " AND (a.title LIKE ? OR u.full_name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sql .= " GROUP BY a.id ORDER BY a.published_at DESC";

        $rows = DB::query($sql, $params);
        return array_map(fn($r) => new Article($r), $rows);
    }

public function saveDraft(int $authorId, array $input): array {
    $this->ensureReviewWorkflow();
    $trustScore = $this->resolveTrustScore($input);

    DB::execute(
        'INSERT INTO articles (title, excerpt, content, author_id, category_id, trust_score, image_path, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        [
            $input['title'] ?? '',
            $input['excerpt'] ?? '',
            $input['content'] ?? '',
            $authorId,
            $input['category_id'] ?? null,
            $trustScore,
            $input['image_path'] ?? null,
            'draft'
        ]
    );

    AuditLogger::log($authorId, 'save_draft', 'Article', DB::lastId(), 'Saved draft: ' . ($input['title'] ?? 'Untitled draft'));

    return ['ok' => true];
}
    // save article function
    public function toggleSave(int $userId, int $articleId): bool {
        
        $existing = DB::first(
            "SELECT id FROM saved_articles WHERE user_id = ? AND article_id = ?",
            [$userId, $articleId]
        );

        if ($existing) {
            DB::execute(
                "DELETE FROM saved_articles WHERE user_id = ? AND article_id = ?",
                [$userId, $articleId]
            );
            AuditLogger::log($userId, 'remove_bookmark', 'Article', $articleId, 'Removed bookmark for article ID ' . $articleId);
            return false; // now unsaved
        }

        DB::execute(
            "INSERT INTO saved_articles (user_id, article_id) VALUES (?, ?)",
            [$userId, $articleId]
        );

        AuditLogger::log($userId, 'save_bookmark', 'Article', $articleId, 'Saved bookmark for article ID ' . $articleId);

        return true; // now saved
    }


    // get saved articles for current user
    public function getSavedArticles(int $userId, int $limit = null): array {
        $sql = "
            SELECT 
            a.*,
            u.full_name AS author_name,
            c.name AS category_name,
            s.created_at AS saved_at,
            COUNT(DISTINCT v.id) AS view_count,
            COUNT(DISTINCT cmt.id) AS comments_count,
            COUNT(DISTINCT f.id) AS flag_count
            FROM articles a
            JOIN saved_articles s ON s.article_id = a.id
            JOIN users u ON u.id = a.author_id
            JOIN categories c ON c.id = a.category_id
            LEFT JOIN article_views v ON v.article_id = a.id
            LEFT JOIN comments cmt ON cmt.article_id = a.id
            LEFT JOIN article_flags f ON f.article_id = a.id
            WHERE s.user_id = ?
            GROUP BY a.id, s.created_at
            ORDER BY s.created_at DESC
        ";
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        $rows = DB::query($sql, [$userId]);
        return array_map(fn($row) => new Article($row), $rows);
}

    public function countSavedArticles(int $userId): int {
    $row = DB::first(
        "SELECT COUNT(*) as total FROM saved_articles WHERE user_id = ?",
        [$userId]
    );

    return (int)$row['total'];
}
    // use for user-profile pagination
    public function countByAuthor(int $authorId): int {
        $result = DB::first(
            "SELECT COUNT(*) as total FROM articles WHERE author_id = ?",
            [$authorId]
        );

        return (int)($result['total'] ?? 0);
    }

    // use for user-profile pagination
    public function getByAuthorPaginated(int $authorId, int $limit, int $offset): array {

        $rows = DB::query(
            "SELECT 
                a.*,
                u.full_name AS author_name,
                c.name AS category_name,
                COUNT(DISTINCT v.id) AS view_count,
                COUNT(DISTINCT f.id) AS flag_count
            FROM articles a
            JOIN users u ON u.id = a.author_id
            JOIN categories c ON c.id = a.category_id
            LEFT JOIN article_views v ON v.article_id = a.id
            LEFT JOIN article_flags f ON f.article_id = a.id
            WHERE a.author_id = ?
            AND a.status = 'published'
            GROUP BY a.id
            ORDER BY view_count DESC, a.published_at DESC
            LIMIT ? OFFSET ?",
            [$authorId, $limit, $offset]
        );

        return array_map(fn($r) => new Article($r), $rows);
    }

 }
