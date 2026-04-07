<?php

// handles all system admin operations
// articles: view, suspend, unsuspend (no edit)
// users: suspend, unsuspend (no delete, no role change)
// categories: full CRUD
// category experts: assign/unassign users as category_admin for a category
// only accessible to users with role = 'system_admin'
// no html output, only data or result arrays

require_once __DIR__ . '/../entities/Article.php';
require_once __DIR__ . '/../entities/Category.php';
require_once __DIR__ . '/../entities/User.php';
require_once __DIR__ . '/../db.php';

class AdminController {

    // ─────────────────────────────────────────────
    // GUARD
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
             COUNT(DISTINCT v.id) AS view_count
             FROM articles a
             JOIN users u ON u.id = a.author_id
             JOIN categories c ON c.id = a.category_id
             LEFT JOIN article_views v ON v.article_id = a.id
             WHERE a.status IN ("published", "suspended")
             GROUP BY a.id
             ORDER BY a.published_at DESC'
        );
        return array_map(fn($r) => new Article($r), $rows);
    }

    // get single article by id (no author restriction)
    public function getArticleById(int $id): ?Article {
        $row = DB::first(
            'SELECT a.*, u.full_name AS author_name, c.name AS category_name,
             COUNT(DISTINCT v.id) AS view_count
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

    // sets article status to 'suspended' — hides from all users
    public function suspendArticle(int $articleId): array {
        if (!DB::first('SELECT id FROM articles WHERE id = ?', [$articleId])) {
            return ['error' => 'Article not found.'];
        }
        DB::execute("UPDATE articles SET status = 'suspended' WHERE id = ?", [$articleId]);
        return ['ok' => true];
    }

    // restores article status back to 'published'
    public function unsuspendArticle(int $articleId): array {
        if (!DB::first('SELECT id FROM articles WHERE id = ?', [$articleId])) {
            return ['error' => 'Article not found.'];
        }
        DB::execute("UPDATE articles SET status = 'published' WHERE id = ?", [$articleId]);
        return ['ok' => true];
    }

    // delete article: no author_id restriction
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

    // ─────────────────────────────────────────────
    // USER MANAGEMENT
    // ─────────────────────────────────────────────

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
        $totalArticles = DB::first(
            "SELECT COUNT(*) AS cnt FROM articles WHERE status IN ('published', 'suspended')"
        )['cnt'] ?? 0;
        $totalUsers   = DB::first('SELECT COUNT(*) AS cnt FROM users')['cnt'] ?? 0;
        $premiumUsers = DB::first("SELECT COUNT(*) AS cnt FROM users WHERE role = 'premium'")['cnt'] ?? 0;
        $suspended    = DB::first('SELECT COUNT(*) AS cnt FROM users WHERE is_suspended = 1')['cnt'] ?? 0;

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
        if (DB::first('SELECT id FROM categories WHERE name = ? AND id != ?', [$name, $categoryId])) {
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

    // ─────────────────────────────────────────────
    // CATEGORY EXPERT MANAGEMENT
    //
    // How it works:
    //   - categories.admin_user_id  → which user is assigned as expert for that category
    //   - users.role = 'category_admin' → marks the user as a category expert
    //
    // Assigning:
    //   1. Set categories.admin_user_id = user id
    //   2. Set users.role = 'category_admin'
    //
    // Unassigning:
    //   1. Set categories.admin_user_id = NULL
    //   2. Set users.role back to 'free'
    //      (only if they are not assigned to another category)
    // ─────────────────────────────────────────────

    // get all categories with their assigned expert (if any)
    public function getCategoriesWithExperts(): array {
        return DB::query(
            'SELECT c.id, c.name, c.description, c.admin_user_id,
                    u.full_name AS expert_name, u.email AS expert_email
             FROM categories c
             LEFT JOIN users u ON u.id = c.admin_user_id
             ORDER BY c.name'
        );
    }

    // get all users eligible to be category experts
    // returns free users + existing category_admin users (so they show in the dropdown)
    public function getEligibleExperts(): array {
        return DB::query(
            "SELECT id, full_name, email, role
             FROM users
             WHERE role IN ('free', 'category_admin')
             AND is_suspended = 0
             ORDER BY full_name"
        );
    }

    // assign a user as the expert for a category
    // - sets categories.admin_user_id
    // - promotes user role to 'category_admin'
    // - if category already had a different expert, demotes the old one first
    public function assignExpert(int $categoryId, int $userId): array {
        if (!DB::first('SELECT id FROM categories WHERE id = ?', [$categoryId])) {
            return ['error' => 'Category not found.'];
        }
        if (!DB::first('SELECT id FROM users WHERE id = ?', [$userId])) {
            return ['error' => 'User not found.'];
        }

        // if this category already has a different expert, unassign them first
        $current = DB::first('SELECT admin_user_id FROM categories WHERE id = ?', [$categoryId]);
        if ($current && $current['admin_user_id'] && $current['admin_user_id'] !== $userId) {
            $this->demoteExpertIfUnused((int)$current['admin_user_id']);
        }

        // assign the new expert to this category
        DB::execute(
            'UPDATE categories SET admin_user_id = ? WHERE id = ?',
            [$userId, $categoryId]
        );

        // promote the user to category_admin role
        DB::execute(
            "UPDATE users SET role = 'category_admin' WHERE id = ?",
            [$userId]
        );

        return ['ok' => true];
    }

    // unassign the expert from a category
    // - clears categories.admin_user_id
    // - demotes user back to 'free' only if they have no other category assigned
    public function unassignExpert(int $categoryId): array {
        $current = DB::first('SELECT admin_user_id FROM categories WHERE id = ?', [$categoryId]);
        if (!$current) {
            return ['error' => 'Category not found.'];
        }

        $expertId = $current['admin_user_id'];

        // clear the category's expert
        DB::execute('UPDATE categories SET admin_user_id = NULL WHERE id = ?', [$categoryId]);

        // demote the user if they are no longer assigned to any category
        if ($expertId) {
            $this->demoteExpertIfUnused((int)$expertId);
        }

        return ['ok' => true];
    }

    // helper: demote a user back to 'free' if they are not assigned to any other category
    private function demoteExpertIfUnused(int $userId): void {
        $stillAssigned = DB::first(
            'SELECT id FROM categories WHERE admin_user_id = ?',
            [$userId]
        );
        if (!$stillAssigned) {
            DB::execute("UPDATE users SET role = 'free' WHERE id = ?", [$userId]);
        }
    }

    // ─────────────────────────────────────────────
    // FLAGGED ARTICLE MANAGEMENT (category admin)
    // ─────────────────────────────────────────────

    // get all articles with at least one flag in a given category
    // returns Article[] sorted by flag count descending
    // excludes flags that were AI-rejected so only actionable flags appear
    public function getFlaggedArticlesByCategory(int $categoryId): array {
        $rows = DB::query(
            "SELECT a.*, u.full_name AS author_name, c.name AS category_name,
             COUNT(DISTINCT v.id) AS view_count,
             COUNT(DISTINCT f.id) AS flag_count
             FROM articles a
             JOIN users u ON u.id = a.author_id
             JOIN categories c ON c.id = a.category_id
             LEFT JOIN article_views v ON v.article_id = a.id
             INNER JOIN article_flags f ON f.article_id = a.id
             WHERE a.category_id = ? AND a.status IN ('published', 'suspended')
             AND (f.status IS NULL OR f.status != 'REJECTED')
             GROUP BY a.id
             ORDER BY flag_count DESC, a.published_at DESC",
            [$categoryId]
        );
        return array_map(fn($r) => new Article($r), $rows);
    }

    // get all individual flag reports for a specific article (excluding AI-rejected flags)
    public function getFlagsByArticle(int $articleId): array {
        return DB::query(
            "SELECT f.*, u.full_name AS reporter_name
             FROM article_flags f
             JOIN users u ON u.id = f.user_id
             WHERE f.article_id = ?
             AND (f.status IS NULL OR f.status != 'REJECTED')
             ORDER BY f.created_at DESC",
            [$articleId]
        );
    }

    // dismiss all flags for an article — article stays published
    public function dismissFlags(int $articleId): array {
        if (!DB::first('SELECT id FROM articles WHERE id = ?', [$articleId])) {
            return ['error' => 'Article not found.'];
        }
        DB::execute('DELETE FROM article_flags WHERE article_id = ?', [$articleId]);
        return ['ok' => true];
    }

    // confirm flags: suspend the article and clear its flags
    public function confirmFlag(int $articleId): array {
        if (!DB::first('SELECT id FROM articles WHERE id = ?', [$articleId])) {
            return ['error' => 'Article not found.'];
        }
        DB::execute("UPDATE articles SET status = 'suspended' WHERE id = ?", [$articleId]);
        DB::execute('DELETE FROM article_flags WHERE article_id = ?', [$articleId]);
        return ['ok' => true];
    }

    // dismiss flags and restore a suspended article back to published
    public function restoreAndDismissFlags(int $articleId): array {
        if (!DB::first('SELECT id FROM articles WHERE id = ?', [$articleId])) {
            return ['error' => 'Article not found.'];
        }
        DB::execute("UPDATE articles SET status = 'published' WHERE id = ?", [$articleId]);
        DB::execute('DELETE FROM article_flags WHERE article_id = ?', [$articleId]);
        return ['ok' => true];
    }
}