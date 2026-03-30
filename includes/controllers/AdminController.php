<?php

// handles all system admin operations
// articles: view, suspend, unsuspend (no edit)
// users: suspend, unsuspend (no delete, no role change)
// categories: full CRUD
// only accessible to users with role = 'system_admin'
// no html output, only data or result arrays

require_once __DIR__ . '/../entities/Article.php';
require_once __DIR__ . '/../entities/Category.php';
require_once __DIR__ . '/../entities/User.php';
require_once __DIR__ . '/../db.php';

class AdminController {


   
    // redirects away if user is not system_admin

    public function requireAdmin(User $user): void {
        if ($user->role !== 'system_admin') {
            header('Location: /dashboard.php');
            exit;
        }
    }

 
    // ARTICLE MANAGEMENT

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

    // admin suspend: sets article status to 'suspended' — hides from all users
    public function suspendArticle(int $articleId): array {
        if (!DB::first('SELECT id FROM articles WHERE id = ?', [$articleId])) {
            return ['error' => 'Article not found.'];
        }
        DB::execute(
            "UPDATE articles SET status = 'suspended' WHERE id = ?",
            [$articleId]
        );
        return ['ok' => true];
    }

    // admin unsuspend: restores article status back to 'published'
    public function unsuspendArticle(int $articleId): array {
        if (!DB::first('SELECT id FROM articles WHERE id = ?', [$articleId])) {
            return ['error' => 'Article not found.'];
        }
        DB::execute(
            "UPDATE articles SET status = 'published' WHERE id = ?",
            [$articleId]
        );
        return ['ok' => true];
    }

    // admin delete article: no author_id restriction
    public function deleteArticle(int $articleId): array {
        $affected = DB::execute('DELETE FROM articles WHERE id = ?', [$articleId]);
        if ($affected === 0) {
            return ['error' => 'Article not found.'];
        }
        return ['ok' => true];
    }

    // get all categories (used in dropdowns)
    public function getAllCategories(): array {
        $rows = DB::query('SELECT * FROM categories ORDER BY name');
        return array_map(fn($r) => new Category($r), $rows);
    }

    // USER MANAGEMENT

    // get all users, newest first
    public function getAllUsers(): array {
        $rows = DB::query('SELECT * FROM users ORDER BY created_at DESC');
        return array_map(fn($r) => new User($r), $rows);
    }

    // get single user by id
    public function getUserById(int $id): ?User {
        $row = DB::first('SELECT * FROM users WHERE id = ?', [$id]);
        return $row ? new User($row) : null;
    }

    // update user suspension status only
    // role is passed through unchanged so the SQL stays consistent
    // is_suspended: 1 = suspend, omitted = 0 = unsuspend
    public function updateUser(int $userId, array $input): array {
        $isSuspended = isset($input['is_suspended']) ? 1 : 0;

        if (!DB::first('SELECT id FROM users WHERE id = ?', [$userId])) {
            return ['error' => 'User not found.'];
        }

        DB::execute(
            'UPDATE users SET is_suspended = ? WHERE id = ?',
            [$isSuspended, $userId]
        );

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

    // CATEGORY MANAGEMENT

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
        $existing = DB::first(
            'SELECT id FROM categories WHERE name = ? AND id != ?',
            [$name, $categoryId]
        );
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


    // CATEGORY EXPERT MANAGEMENT

    // get all free/premium users that can be promoted to category_admin
    public function getPromotableUsers(): array {
        $rows = DB::query(
            "SELECT * FROM users WHERE role IN ('free', 'premium') ORDER BY full_name"
        );
        return array_map(fn($r) => new User($r), $rows);
    }

    // get all category_admin users with their assigned category info
    public function getCategoryExperts(): array {
        $rows = DB::query(
            "SELECT u.*, c.name AS category_name, c.id AS assigned_category_id
             FROM users u
             LEFT JOIN categories c ON c.admin_user_id = u.id
             WHERE u.role = 'category_admin'
             ORDER BY u.full_name"
        );
        return array_map(fn($r) => new User($r), $rows);
    }

    // get all categories with their assigned expert info
    public function getCategoriesWithExperts(): array {
        return DB::query(
            "SELECT c.*, u.id AS expert_user_id, u.full_name AS expert_name, u.email AS expert_email
             FROM categories c
             LEFT JOIN users u ON u.id = c.admin_user_id
             ORDER BY c.name"
        );
    }

    // promote a free/premium user to category_admin
    public function promoteUserToCategoryAdmin(int $userId): array {
        $user = DB::first('SELECT id, role FROM users WHERE id = ?', [$userId]);

        if (!$user) {
            return ['error' => 'User not found.'];
        }
        if ($user['role'] === 'category_admin') {
            return ['error' => 'User is already a category expert.'];
        }
        if (!in_array($user['role'], ['free', 'premium'])) {
            return ['error' => 'Only free or premium users can be promoted.'];
        }

        DB::execute(
            "UPDATE users SET role = 'category_admin' WHERE id = ?",
            [$userId]
        );

        return ['ok' => true];
    }

    // assign a category to a category_admin user
    // one category_admin can only manage one category at a time
    public function assignCategoryToExpert(int $categoryId, int $userId): array {
        if (!DB::first('SELECT id FROM categories WHERE id = ?', [$categoryId])) {
            return ['error' => 'Category not found.'];
        }

        $user = DB::first('SELECT id, role FROM users WHERE id = ?', [$userId]);
        if (!$user) {
            return ['error' => 'User not found.'];
        }
        if ($user['role'] !== 'category_admin') {
            return ['error' => 'User is not a category expert.'];
        }

        // check if this expert already manages a different category
        if (DB::first(
            'SELECT id FROM categories WHERE admin_user_id = ? AND id != ?',
            [$userId, $categoryId]
        )) {
            return ['error' => 'This expert already manages another category. Remove that assignment first.'];
        }

        DB::execute(
            'UPDATE categories SET admin_user_id = ? WHERE id = ?',
            [$userId, $categoryId]
        );

        return ['ok' => true];
    }

    // unassign a category from its expert
    public function unassignCategoryExpert(int $categoryId): array {
        if (!DB::first('SELECT id FROM categories WHERE id = ?', [$categoryId])) {
            return ['error' => 'Category not found.'];
        }

        DB::execute(
            'UPDATE categories SET admin_user_id = NULL WHERE id = ?',
            [$categoryId]
        );

        return ['ok' => true];
    }

    // demote a category_admin back to free and remove their category assignment
    public function demoteCategoryAdmin(int $userId): array {
        $user = DB::first('SELECT id, role FROM users WHERE id = ?', [$userId]);

        if (!$user) {
            return ['error' => 'User not found.'];
        }
        if ($user['role'] !== 'category_admin') {
            return ['error' => 'User is not a category expert.'];
        }

        // remove any category assignment for this expert
        DB::execute(
            'UPDATE categories SET admin_user_id = NULL WHERE admin_user_id = ?',
            [$userId]
        );

        // revert role to free
        DB::execute(
            "UPDATE users SET role = 'free' WHERE id = ?",
            [$userId]
        );

        return ['ok' => true];
    }
}