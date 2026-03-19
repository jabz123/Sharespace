<?php

// handles all system admin operations
// provides CRUD for all articles (not just own) and full user management
// only accessible to users with role = 'system_admin'
// no html output, only data or result arrays

require_once __DIR__ . '/../entities/Article.php';
require_once __DIR__ . '/../entities/Category.php';
require_once __DIR__ . '/../entities/User.php';
require_once __DIR__ . '/../db.php';

class AdminController {

    // ─────────────────────────────────────────────
    // GUARD: call this at top of every admin page
    // redirects away if user is not system_admin
    // ─────────────────────────────────────────────
    public function requireAdmin(User $user): void {
        if ($user->role !== 'system_admin') {
            header('Location: /dashboard.php');
            exit;
        }
    }

    // ─────────────────────────────────────────────
    // ARTICLE MANAGEMENT
    // ─────────────────────────────────────────────

    // get all articles with author + category info, newest first
    public function getAllArticles(): array {
        $rows = DB::query(
            'SELECT a.*, u.full_name AS author_name, c.name AS category_name,
             COUNT(v.id) AS view_count
             FROM articles a
             JOIN users u ON u.id = a.author_id
             JOIN categories c ON c.id = a.category_id
             LEFT JOIN article_views v ON v.article_id = a.id
             GROUP BY a.id
             ORDER BY a.published_at DESC'
        );
        return array_map(fn($r) => new Article($r), $rows);
    }

    // get single article by id (no author restriction - admin can see all)
    public function getArticleById(int $id): ?Article {
        $row = DB::first(
            'SELECT a.*, u.full_name AS author_name, c.name AS category_name,
             COUNT(v.id) AS view_count
             FROM articles a
             JOIN users u ON u.id = a.author_id
             JOIN categories c ON c.id = a.category_id
             LEFT JOIN article_views v ON v.article_id = a.id
             WHERE a.id = ?
             GROUP BY a.id',
            [$id]
        );
        return $row ? new Article($row) : null;
    }

    // admin update: no author_id restriction
    public function updateArticle(int $articleId, array $input): array {
        $title      = trim($input['title']        ?? '');
        $excerpt    = trim($input['excerpt']      ?? '');
        $content    = trim($input['content']      ?? '');
        $categoryId = (int)($input['category_id'] ?? 0);

        if (!$title || !$excerpt || !$content || !$categoryId) {
            return ['error' => 'All fields are required.'];
        }
        if (!DB::first('SELECT id FROM categories WHERE id = ?', [$categoryId])) {
            return ['error' => 'Invalid category selected.'];
        }
        if (!DB::first('SELECT id FROM articles WHERE id = ?', [$articleId])) {
            return ['error' => 'Article not found.'];
        }

        $imagePath = $input['image_path'] ?? null;

        DB::execute(
            'UPDATE articles
             SET title = ?, excerpt = ?, content = ?, category_id = ?, image_path = ?, updated_at = NOW()
             WHERE id = ?',
            [$title, $excerpt, $content, $categoryId, $imagePath, $articleId]
        );

        return ['ok' => true];
    }

    // admin delete: no author_id restriction
    public function deleteArticle(int $articleId): array {
        $affected = DB::execute('DELETE FROM articles WHERE id = ?', [$articleId]);
        if ($affected === 0) {
            return ['error' => 'Article not found.'];
        }
        return ['ok' => true];
    }

    // get all categories (reused from ArticleController pattern)
    public function getAllCategories(): array {
        $rows = DB::query('SELECT * FROM categories ORDER BY name');
        return array_map(fn($r) => new Category($r), $rows);
    }

    // ─────────────────────────────────────────────
    // USER MANAGEMENT
    // ─────────────────────────────────────────────

    // get all users, newest first
    public function getAllUsers(): array {
        $rows = DB::query(
            'SELECT * FROM users ORDER BY created_at DESC'
        );
        return array_map(fn($r) => new User($r), $rows);
    }

    // get single user by id
    public function getUserById(int $id): ?User {
        $row = DB::first('SELECT * FROM users WHERE id = ?', [$id]);
        return $row ? new User($row) : null;
    }

    // update user role and suspension status
    // allowed roles: free, premium, system_admin
    public function updateUser(int $userId, array $input): array {
        $role        = $input['role']         ?? '';
        $isSuspended = isset($input['is_suspended']) ? 1 : 0;

        $allowed = ['free', 'premium', 'system_admin'];
        if (!in_array($role, $allowed, true)) {
            return ['error' => 'Invalid role selected.'];
        }
        if (!DB::first('SELECT id FROM users WHERE id = ?', [$userId])) {
            return ['error' => 'User not found.'];
        }

        DB::execute(
            'UPDATE users SET role = ?, is_suspended = ? WHERE id = ?',
            [$role, $isSuspended, $userId]
        );

        return ['ok' => true];
    }

    // delete a user and all their articles
    public function deleteUser(int $userId, int $adminId): array {
        if ($userId === $adminId) {
            return ['error' => 'You cannot delete your own account.'];
        }
        if (!DB::first('SELECT id FROM users WHERE id = ?', [$userId])) {
            return ['error' => 'User not found.'];
        }

        // articles deleted via ON DELETE CASCADE in DB, but do it explicitly for safety
        DB::execute('DELETE FROM articles WHERE author_id = ?', [$userId]);
        DB::execute('DELETE FROM users WHERE id = ?', [$userId]);

        return ['ok' => true];
    }

    // summary counts for admin dashboard overview cards
    public function getStats(): array {
        $totalArticles = DB::first('SELECT COUNT(*) AS cnt FROM articles')['cnt'] ?? 0;
        $totalUsers    = DB::first('SELECT COUNT(*) AS cnt FROM users')['cnt'] ?? 0;
        $premiumUsers  = DB::first("SELECT COUNT(*) AS cnt FROM users WHERE role = 'premium'")['cnt'] ?? 0;
        $suspended     = DB::first('SELECT COUNT(*) AS cnt FROM users WHERE is_suspended = 1')['cnt'] ?? 0;

        return compact('totalArticles', 'totalUsers', 'premiumUsers', 'suspended');
    }

    // ─────────────────────────────────────────────
    // CATEGORY MANAGEMENT
    // ─────────────────────────────────────────────

    // get all categories with article count
    public function getAllCategoriesWithCount(): array {
        return DB::query(
            'SELECT c.*, COUNT(a.id) AS article_count
             FROM categories c
             LEFT JOIN articles a ON a.category_id = c.id
             GROUP BY c.id
             ORDER BY c.name'
        );
    }

    // create a new category
    public function createCategory(array $input): array {
        $name        = trim($input['name']        ?? '');
        $description = trim($input['description'] ?? '');

        if (!$name) {
            return ['error' => 'Category name is required.'];
        }
        if (DB::first('SELECT id FROM categories WHERE name = ?', [$name])) {
            return ['error' => 'A category with that name already exists.'];
        }

        DB::execute(
            'INSERT INTO categories (name, description) VALUES (?, ?)',
            [$name, $description]
        );

        return ['ok' => true];
    }

    // update an existing category
    public function updateCategory(int $categoryId, array $input): array {
        $name        = trim($input['name']        ?? '');
        $description = trim($input['description'] ?? '');

        if (!$name) {
            return ['error' => 'Category name is required.'];
        }
        $existing = DB::first('SELECT id FROM categories WHERE name = ? AND id != ?', [$name, $categoryId]);
        if ($existing) {
            return ['error' => 'A category with that name already exists.'];
        }
        if (!DB::first('SELECT id FROM categories WHERE id = ?', [$categoryId])) {
            return ['error' => 'Category not found.'];
        }

        DB::execute(
            'UPDATE categories SET name = ?, description = ? WHERE id = ?',
            [$name, $description, $categoryId]
        );

        return ['ok' => true];
    }

    // delete a category — only if no articles use it
    public function deleteCategory(int $categoryId): array {
        $count = DB::first(
            'SELECT COUNT(*) AS cnt FROM articles WHERE category_id = ?',
            [$categoryId]
        )['cnt'] ?? 0;

        if ($count > 0) {
            return ['error' => "Cannot delete — {$count} article(s) still use this category."];
        }
        if (!DB::first('SELECT id FROM categories WHERE id = ?', [$categoryId])) {
            return ['error' => 'Category not found.'];
        }

        DB::execute('DELETE FROM categories WHERE id = ?', [$categoryId]);

        return ['ok' => true];
    }
}