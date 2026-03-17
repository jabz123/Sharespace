<?php

// handles article-related logic for the system
// retrieves articles and categories from the database
// creates, updates and deletes articles
// theres no html output from here, only article/category entities or result arrays

require_once __DIR__ . '/../entities/Article.php';
require_once __DIR__ . '/../entities/Category.php';

class ArticleController {
    //returns n most recently published articles
    //maybe change this to recommended or some shit in future ig
    //returns Article[] array of objects
    public function getRecent(int $limit = 6): array {
        $rows = DB::query(
            'SELECT a.*, u.full_name AS author_name, c.name AS category_name,
            COUNT(v.id) AS view_count
            FROM articles a
            JOIN users u ON u.id = a.author_id
            JOIN categories c ON c.id = a.category_id
            LEFT JOIN article_views v ON v.article_id = a.id
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
             COUNT(v.id) AS view_count
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
        $rows = DB::query(
            'SELECT a.*, u.full_name AS author_name, c.name AS category_name,
             COUNT(v.id) AS view_count
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

    //update existing article only author ownselfd can update
    //return ['ok' => true] or ['error' => '...']
    public function update(int $articleId, int $authorId, array $input): array {
        $title      = trim($input['title']       ?? '');
        $excerpt    = trim($input['excerpt']     ?? '');
        $content    = trim($input['content']     ?? '');
        $categoryId = (int)($input['category_id'] ?? 0);
        $status = $input['status'] ?? null;

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
        $current = DB::first('SELECT status FROM articles WHERE id = ?', [$articleId]);

        $status = $input['status'] ?? null;

        // ONLY SET published_at WHEN draft → published
        $setPublishedAt = '';
        if ($current['status'] === 'draft' && $status === 'published') {
            $setPublishedAt = ', published_at = NOW()';
        }

        DB::execute(
            "UPDATE articles
            SET title = ?, excerpt = ?, content = ?, category_id = ?, image_path = ?, status = ? $setPublishedAt, updated_at = NOW()
            WHERE id = ? AND author_id = ?",
            [$title, $excerpt, $content, $categoryId, $imagePath, $status, $articleId, $authorId]
        );

        return ['ok' => true];
    }

    //delete article, only author ownself can delete. comments also will all delete
    //also return ['ok' => true] or ['error' => '...']
    public function delete(int $articleId, int $authorId): array {
        $affected = DB::execute(
            'DELETE FROM articles WHERE id = ? AND author_id = ?',
            [$articleId, $authorId]
        );
        if ($affected === 0) {
            return ['error' => 'Article not found or permission denied.'];
        }

        return ['ok' => true];
    }

    //vvalidate article written by user and insert into db, returns result array with ok or error 
    
    public function publish(int $authorId, array $input): array {
        $title      = trim($input['title']       ?? '');
        $excerpt    = trim($input['excerpt']     ?? '');
        $content    = trim($input['content']     ?? '');
        $categoryId = (int)($input['category_id'] ?? 0);
        $imagePath = $input['image_path'] ?? null;
       
        if (!$title || !$excerpt || !$content || !$categoryId) {
            return ['error' => 'All fields are required.'];
        }
       
        if (!DB::first('SELECT id FROM categories WHERE id = ?', [$categoryId])) {
            return ['error' => 'Invalid category selected.'];
        }

        DB::execute(
        'INSERT INTO articles (title, excerpt, content, author_id, category_id, trust_score, image_path, status, published_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())',
        [$title, $excerpt, $content, $authorId, $categoryId, 80, $imagePath, 'published']
         );

        return ['ok' => true, 'id' => DB::lastId()];
    }

    public function getByCategory($category = null, $sort = 'recent', $search = null): array {

        $sql = 'SELECT a.*, u.full_name AS author_name, c.name AS category_name,
                COUNT(v.id) AS view_count
                FROM articles a
                JOIN users u ON u.id = a.author_id
                JOIN categories c ON c.id = a.category_id
                LEFT JOIN article_views v ON v.article_id = a.id
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
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' GROUP BY a.id';

        if ($sort === 'recent') {
            $sql .= ' ORDER BY a.published_at DESC';
        } else {
            $sql .= ' ORDER BY a.trust_score DESC, a.published_at DESC';
        }

        $rows = DB::query($sql, $params);

        return array_map(fn($r) => new Article($r), $rows);
    }

    public function saveDraft(int $authorId, array $input): array {

    DB::execute(
        'INSERT INTO articles (title, excerpt, content, author_id, category_id, trust_score, image_path, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
        [
            $input['title'] ?? '',
            $input['excerpt'] ?? '',
            $input['content'] ?? '',
            $authorId,
            $input['category_id'] ?? null,
            80,
            $input['image_path'] ?? null,
            'draft'
        ]
    );

    return ['ok' => true];
}

 }