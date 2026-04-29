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
require_once __DIR__ . '/../AuditLogger.php';
//admincontroller, for all admin related db operations.
class AdminController
{
    private function ensureCategoryExpertTable(): void
    {
        DB::ensureCategoryExpertsTable();
    }

    public function getAssignedCategoryForExpert(int $userId): ?array
    {
        $this->ensureCategoryExpertTable();

        return DB::first(
            'SELECT c.id, c.name
             FROM category_experts ce
             JOIN categories c ON c.id = ce.category_id
             WHERE ce.user_id = ?
             ORDER BY c.name
             LIMIT 1',
            [$userId]
        );
    }

    public function getAssignedCategoriesForExpert(int $userId): array
    {
        $this->ensureCategoryExpertTable();

        return DB::query(
            'SELECT c.id, c.name
             FROM category_experts ce
             JOIN categories c ON c.id = ce.category_id
             WHERE ce.user_id = ?
             ORDER BY c.name',
            [$userId]
        );
    }

    // ─────────────────────────────────────────────
    // GUARD
    // ─────────────────────────────────────────────

    public function requireAdmin(User $user): void
    {
        if ($user->role !== 'system_admin') {
            header('Location: /dashboard.php');
            exit;
        }
    }

    // ─────────────────────────────────────────────
    // ARTICLE MANAGEMENT
    // ─────────────────────────────────────────────

    // get all articles with author + category info, newest first
    public function getAllArticles(): array
    {
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
        return array_map(fn ($r) => new Article($r), $rows);
    }

    // get single article by id (no author restriction)
    public function getArticleById(int $id): ?Article
    {
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
    public function suspendArticle(int $articleId): array
    {
        if (!DB::first('SELECT id FROM articles WHERE id = ?', [$articleId])) {
            return ['error' => 'Article not found.'];
        }
        DB::execute("UPDATE articles SET status = 'suspended' WHERE id = ?", [$articleId]);
        return ['ok' => true];
    }

    // restores article status back to 'published'
    public function unsuspendArticle(int $articleId): array
    {
        if (!DB::first('SELECT id FROM articles WHERE id = ?', [$articleId])) {
            return ['error' => 'Article not found.'];
        }
        DB::execute("UPDATE articles SET status = 'published' WHERE id = ?", [$articleId]);
        return ['ok' => true];
    }

    // delete article: no author_id restriction
    public function deleteArticle(int $articleId): array
    {
        $affected = DB::execute('DELETE FROM articles WHERE id = ?', [$articleId]);
        if ($affected === 0) {
            return ['error' => 'Article not found.'];
        }
        return ['ok' => true];
    }

    // get all categories (used in dropdowns)
    public function getAllCategories(): array
    {
        $rows = DB::query('SELECT * FROM categories ORDER BY name');
        return array_map(fn ($r) => new Category($r), $rows);
    }

    // ─────────────────────────────────────────────
    // USER MANAGEMENT
    // ─────────────────────────────────────────────

    // get all users, newest first
    public function getAllUsers(): array
    {
        $rows = DB::query('SELECT * FROM users ORDER BY created_at DESC');
        return array_map(fn ($r) => new User($r), $rows);
    }

    // get single user by id
    public function getUserById(int $id): ?User
    {
        $row = DB::first('SELECT * FROM users WHERE id = ?', [$id]);
        return $row ? new User($row) : null;
    }

    // update user suspension status only
    // is_suspended: 1 = suspend, omitted = 0 = unsuspend
    public function updateUser(int $userId, array $input): array
    {
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
    public function getStats(): array
    {
        $totalArticles = DB::first(
            "SELECT COUNT(*) AS cnt FROM articles WHERE status IN ('published', 'suspended')"
        )['cnt'] ?? 0;
        $totalUsers = DB::first('SELECT COUNT(*) AS cnt FROM users')['cnt'] ?? 0;
        $premiumUsers = DB::first("SELECT COUNT(*) AS cnt FROM users WHERE role = 'premium'")['cnt'] ?? 0;
        $suspended = DB::first('SELECT COUNT(*) AS cnt FROM users WHERE is_suspended = 1')['cnt'] ?? 0;

        return compact('totalArticles', 'totalUsers', 'premiumUsers', 'suspended');
    }

    // ─────────────────────────────────────────────
    // CATEGORY MANAGEMENT
    // ─────────────────────────────────────────────

    // get all categories with article count
    public function getAllCategoriesWithCount(): array
    {
        return DB::query(
            'SELECT c.*, COUNT(a.id) AS article_count
             FROM categories c
             LEFT JOIN articles a ON a.category_id = c.id
             GROUP BY c.id
             ORDER BY c.name'
        );
    }

    // create a new category
    public function createCategory(array $input): array
    {
        $name = trim($input['name'] ?? '');
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
    public function updateCategory(int $categoryId, array $input): array
    {
        $name = trim($input['name'] ?? '');
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
    public function deleteCategory(int $categoryId): array
    {
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
    public function getCategoriesWithExperts(): array
    {
        $this->ensureCategoryExpertTable();

        $categories = DB::query(
            'SELECT c.id, c.name, c.description
             FROM categories c
             ORDER BY c.name'
        );

        $assignments = DB::query(
            'SELECT ce.category_id, ce.user_id, u.full_name, u.email
             FROM category_experts ce
             JOIN users u ON u.id = ce.user_id
             ORDER BY u.full_name'
        );

        $expertsByCategory = [];
        foreach ($assignments as $assignment) {
            $categoryId = (int) $assignment['category_id'];
            $expertsByCategory[$categoryId][] = [
                'user_id' => (int) $assignment['user_id'],
                'full_name' => $assignment['full_name'],
                'email' => $assignment['email'],
            ];
        }

        foreach ($categories as &$category) {
            $category['experts'] = $expertsByCategory[(int) $category['id']] ?? [];
            $primaryExpert = $category['experts'][0] ?? null;
            $category['admin_user_id'] = $primaryExpert['user_id'] ?? null;
            $category['expert_name'] = $primaryExpert['full_name'] ?? null;
            $category['expert_email'] = count($category['experts']) . ' assigned';
        }
        unset($category);

        return $categories;
    }

    // get all users eligible to be category experts
    // returns free users + existing category_admin users (so they show in the dropdown)
    public function getEligibleExperts(): array
    {
        $this->ensureCategoryExpertTable();

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
    public function assignExpert(int $categoryId, int $userId): array
    {
        $this->ensureCategoryExpertTable();

        if (!DB::first('SELECT id FROM categories WHERE id = ?', [$categoryId])) {
            return ['error' => 'Category not found.'];
        }
        $user = DB::first('SELECT id, is_suspended FROM users WHERE id = ?', [$userId]);
        if (!$user) {
            return ['error' => 'User not found.'];
        }
        if ((int) ($user['is_suspended'] ?? 0) === 1) {
            return ['error' => 'Suspended users cannot be assigned as category experts.'];
        }

        DB::execute(
            'INSERT IGNORE INTO category_experts (category_id, user_id) VALUES (?, ?)',
            [$categoryId, $userId]
        );

        DB::execute(
            "UPDATE users SET role = 'category_admin' WHERE id = ?",
            [$userId]
        );

        $primaryExpert = DB::first(
            'SELECT user_id
             FROM category_experts
             WHERE category_id = ?
             ORDER BY created_at, id
             LIMIT 1',
            [$categoryId]
        );
        DB::execute(
            'UPDATE categories SET admin_user_id = ? WHERE id = ?',
            [(int) ($primaryExpert['user_id'] ?? $userId), $categoryId]
        );

        return ['ok' => true];
    }

    // unassign the expert from a category
    // - clears categories.admin_user_id
    // - demotes user back to 'free' only if they have no other category assigned
    public function unassignExpert(int $categoryId, int $userId = 0): array
    {
        $this->ensureCategoryExpertTable();

        if (!DB::first('SELECT id FROM categories WHERE id = ?', [$categoryId])) {
            return ['error' => 'Category not found.'];
        }
        if ($userId <= 0) {
            $primaryExpert = DB::first(
                'SELECT user_id
                 FROM category_experts
                 WHERE category_id = ?
                 ORDER BY created_at, id
                 LIMIT 1',
                [$categoryId]
            );
            $userId = (int) ($primaryExpert['user_id'] ?? 0);
        }

        if ($userId <= 0 || !DB::first('SELECT id FROM users WHERE id = ?', [$userId])) {
            return ['error' => 'User not found.'];
        }

        $removed = DB::execute(
            'DELETE FROM category_experts WHERE category_id = ? AND user_id = ?',
            [$categoryId, $userId]
        );
        if ($removed === 0) {
            return ['error' => 'That expert is not assigned to this category.'];
        }

        $primaryExpert = DB::first(
            'SELECT user_id
             FROM category_experts
             WHERE category_id = ?
             ORDER BY created_at, id
             LIMIT 1',
            [$categoryId]
        );
        DB::execute(
            'UPDATE categories SET admin_user_id = ? WHERE id = ?',
            [$primaryExpert ? (int) $primaryExpert['user_id'] : null, $categoryId]
        );

        $this->demoteExpertIfUnused($userId);

        return ['ok' => true];
    }

    // helper: demote a user back to 'free' if they are not assigned to any other category
    private function demoteExpertIfUnused(int $userId): void
    {
        $this->ensureCategoryExpertTable();

        $stillAssigned = DB::first(
            'SELECT id FROM category_experts WHERE user_id = ?',
            [$userId]
        );
        if (!$stillAssigned) {
            DB::execute("UPDATE users SET role = 'free' WHERE id = ?", [$userId]);
        }
    }

    public function getUnverifiedArticlesForExpert(int $userId): array
    {
        DB::ensureArticleReviewWorkflow();

        return DB::query(
            'SELECT a.*, u.full_name AS author_name, c.name AS category_name,
                    aer.status AS reviewer_status,
                    counts.total_reviews,
                    counts.verified_reviews,
                    counts.pending_reviews
             FROM article_expert_reviews aer
             JOIN articles a ON a.id = aer.article_id
             JOIN users u ON u.id = a.author_id
             JOIN categories c ON c.id = a.category_id
             JOIN (
                 SELECT article_id,
                        COUNT(*) AS total_reviews,
                        SUM(CASE WHEN status = "verified" THEN 1 ELSE 0 END) AS verified_reviews,
                        SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) AS pending_reviews
                 FROM article_expert_reviews
                 GROUP BY article_id
             ) counts ON counts.article_id = a.id
             WHERE aer.user_id = ?
               AND aer.status = "pending"
               AND a.status = "pending"
             ORDER BY a.updated_at DESC, a.id DESC',
            [$userId]
        );
    }

    public function getUnverifiedArticleForExpert(int $userId, int $articleId): ?array
    {
        DB::ensureArticleReviewWorkflow();

        return DB::first(
            'SELECT a.*, u.full_name AS author_name, c.name AS category_name,
                    aer.status AS reviewer_status
             FROM article_expert_reviews aer
             JOIN articles a ON a.id = aer.article_id
             JOIN users u ON u.id = a.author_id
             JOIN categories c ON c.id = a.category_id
             WHERE aer.user_id = ?
               AND aer.article_id = ?
               AND a.status = "pending"',
            [$userId, $articleId]
        );
    }

    public function getExpertReviewProgress(int $articleId): array
    {
        DB::ensureArticleReviewWorkflow();

        return DB::query(
            'SELECT aer.user_id, aer.status, aer.reviewed_at, u.full_name, u.email
             FROM article_expert_reviews aer
             JOIN users u ON u.id = aer.user_id
             WHERE aer.article_id = ?
             ORDER BY u.full_name',
            [$articleId]
        );
    }

    public function reviewPendingArticle(int $articleId, int $expertId, string $decision): array
    {
        DB::ensureArticleReviewWorkflow();

        $decision = $decision === 'unverified' ? 'unverified' : 'verified';

        $review = DB::first(
            'SELECT aer.id, aer.status, a.id AS article_id, a.status AS article_status, a.title
             FROM article_expert_reviews aer
             JOIN articles a ON a.id = aer.article_id
             WHERE aer.article_id = ? AND aer.user_id = ?',
            [$articleId, $expertId]
        );
        if (!$review) {
            return ['error' => 'Review assignment not found for this article.'];
        }
        if (($review['article_status'] ?? '') !== 'pending') {
            return ['error' => 'This article is no longer pending review.'];
        }

        $pdo = DB::get();
        $pdo->beginTransaction();

        try {
            DB::execute(
                'UPDATE article_expert_reviews
                 SET status = ?, reviewed_at = NOW()
                 WHERE article_id = ? AND user_id = ?',
                [$decision, $articleId, $expertId]
            );

            if ($decision === 'unverified') {
                DB::execute(
                    'UPDATE articles
                     SET status = ?, review_notice = ?, review_notice_pending = 1, updated_at = NOW()
                     WHERE id = ?',
                    ['draft', 'A category expert rejected this article during final verification. Please revise it and submit it again.', $articleId]
                );
                AuditLogger::log($expertId, 'reject_content', 'Article', $articleId, 'Rejected article during expert verification: ' . ($review['title'] ?? ('ID ' . $articleId)));
            } else {
                $summary = DB::first(
                    'SELECT
                        COUNT(*) AS total_reviews,
                        SUM(CASE WHEN status = "verified" THEN 1 ELSE 0 END) AS verified_reviews,
                        SUM(CASE WHEN status = "unverified" THEN 1 ELSE 0 END) AS unverified_reviews
                     FROM article_expert_reviews
                     WHERE article_id = ?',
                    [$articleId]
                );

                $hasVerifiedExpert = (int) ($summary['total_reviews'] ?? 0) > 0
                    && (int) ($summary['verified_reviews'] ?? 0) >= 1
                    && (int) ($summary['unverified_reviews'] ?? 0) === 0;

                if ($hasVerifiedExpert) {
                    DB::execute(
                        'UPDATE articles
                         SET status = ?, published_at = NOW(), review_notice = NULL, review_notice_pending = 0, updated_at = NOW()
                         WHERE id = ?',
                        ['published', $articleId]
                    );
                    AuditLogger::log($expertId, 'approve_content', 'Article', $articleId, 'Completed expert verification and published article: ' . ($review['title'] ?? ('ID ' . $articleId)));
                } else {
                    AuditLogger::log($expertId, 'approve_content', 'Article', $articleId, 'Verified article during expert review: ' . ($review['title'] ?? ('ID ' . $articleId)));
                }
            }

            $pdo->commit();
            return ['ok' => true, 'title' => $review['title'] ?? 'Article'];
        } catch (\Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $error;
        }
    }

    // ─────────────────────────────────────────────
    // FLAGGED ARTICLE MANAGEMENT (category admin)
    // ─────────────────────────────────────────────

    // get all articles with at least one flag in a given category
    // returns Article[] sorted by flag count descending
    public function getFlaggedArticlesByCategory(int $categoryId): array
    {
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
             GROUP BY a.id
             ORDER BY flag_count DESC, a.published_at DESC",
            [$categoryId]
        );
        return array_map(fn ($r) => new Article($r), $rows);
    }

    // get all individual flag reports for a specific article
    public function getFlagsByArticle(int $articleId): array
    {
        return DB::query(
            'SELECT f.*, u.full_name AS reporter_name
             FROM article_flags f
             JOIN users u ON u.id = f.user_id
             WHERE f.article_id = ?
             ORDER BY f.created_at DESC',
            [$articleId]
        );
    }

    // dismiss all flags for an article — article stays published
    public function dismissFlags(int $articleId): array
    {
        if (!DB::first('SELECT id FROM articles WHERE id = ?', [$articleId])) {
            return ['error' => 'Article not found.'];
        }
        DB::execute('DELETE FROM article_flags WHERE article_id = ?', [$articleId]);
        return ['ok' => true];
    }

    // confirm flags: suspend the article and clear its flags
    public function confirmFlag(int $articleId): array
    {
        if (!DB::first('SELECT id FROM articles WHERE id = ?', [$articleId])) {
            return ['error' => 'Article not found.'];
        }
        DB::execute("UPDATE articles SET status = 'suspended' WHERE id = ?", [$articleId]);
        DB::execute('DELETE FROM article_flags WHERE article_id = ?', [$articleId]);
        return ['ok' => true];
    }

    // dismiss flags and restore a suspended article back to published
    public function restoreAndDismissFlags(int $articleId): array
    {
        if (!DB::first('SELECT id FROM articles WHERE id = ?', [$articleId])) {
            return ['error' => 'Article not found.'];
        }
        DB::execute("UPDATE articles SET status = 'published' WHERE id = ?", [$articleId]);
        DB::execute('DELETE FROM article_flags WHERE article_id = ?', [$articleId]);
        return ['ok' => true];
    }

    // ─────────────────────────────────────────────
    // EXTENDED STATS (for analytics quick-stats bar)
    // ─────────────────────────────────────────────

    // Returns all stats needed for the dashboard analytics bar:
    // totalArticles, totalUsers, totalCategories,
    // flaggedArticles, suspendedArticles, premiumUsers
    public function getExtendedStats(): array
    {
        $totalArticles = DB::first(
            "SELECT COUNT(*) AS cnt FROM articles WHERE status IN ('published','suspended')"
        )['cnt'] ?? 0;

        $totalUsers = DB::first('SELECT COUNT(*) AS cnt FROM users')['cnt'] ?? 0;

        $totalCategories = DB::first('SELECT COUNT(*) AS cnt FROM categories')['cnt'] ?? 0;

        $flaggedArticles = DB::first(
            'SELECT COUNT(DISTINCT article_id) AS cnt FROM article_flags'
        )['cnt'] ?? 0;

        $suspendedArticles = DB::first(
            "SELECT COUNT(*) AS cnt FROM articles WHERE status = 'suspended'"
        )['cnt'] ?? 0;

        $premiumUsers = DB::first(
            "SELECT COUNT(*) AS cnt FROM users WHERE role = 'premium'"
        )['cnt'] ?? 0;

        $suspended = DB::first(
            'SELECT COUNT(*) AS cnt FROM users WHERE is_suspended = 1'
        )['cnt'] ?? 0;

        return compact(
            'totalArticles',
            'totalUsers',
            'totalCategories',
            'flaggedArticles',
            'suspendedArticles',
            'premiumUsers',
            'suspended'
        );
    }

    // ─────────────────────────────────────────────
    // AUDIT LOG
    // ─────────────────────────────────────────────

    // Record an actor action into audit_log. Older callers still pass the admin id here.
    public function logAction(
        ?int   $actorId,
        string $action,
        string $targetType,
        ?int   $targetId,
        string $details,
        ?string $actorRole = null,
        ?string $actorName = null,
        ?string $actorEmail = null
    ): void {
        AuditLogger::log($actorId, $action, $targetType, $targetId, $details, $actorRole, $actorName, $actorEmail);
    }

    // Fetch audit log entries, newest first, with the actor's name joined.
    // Optional $filterAction limits results to a specific action type (e.g. 'suspend_user').
    public function getAuditLog(
        int $limit = 50,
        int $offset = 0,
        ?string $filterAction = null,
        ?string $filterRole = null
    ): array {
        return AuditLogger::entries($limit, $offset, $filterAction, $filterRole);
    }

    // Total count of audit log rows. Optional $filterAction scopes the count.
    public function getAuditLogCount(?string $filterAction = null, ?string $filterRole = null): int
    {
        return AuditLogger::count($filterAction, $filterRole);
    }
}
