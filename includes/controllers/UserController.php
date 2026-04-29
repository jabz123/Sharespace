<?php
//for user related logic like top contributor shit
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../entities/User.php';

class UserController
{
    // top contributors by published article count
    // for the top contributor shit
    public function getTopContributors(int $limit = 3): array
    {
        $limit = max(1, min(20, $limit)); //maek 20 max

        return DB::query(
            "SELECT
                u.id,
                u.full_name,
                u.avatar_url,
                COUNT(a.id) AS article_count,
                MAX(a.published_at) AS latest_published_at
             FROM users u
             INNER JOIN articles a ON a.author_id = u.id
             WHERE a.status = 'published'
             GROUP BY u.id
             ORDER BY article_count DESC, latest_published_at DESC, u.full_name ASC
             LIMIT $limit"
        );
    }

    // search users
    public function searchUsers(?string $keyword, int $limit, int $offset, int $currentUserId): array
    {

        $params = [];
        $sql = "
            SELECT 
                u.id, 
                u.full_name, 
                u.avatar_url, 
                u.role, 
                u.bio,  
                SUM(a.status = 'published') AS article_count
            FROM users u
            LEFT JOIN articles a ON a.author_id = u.id
            WHERE u.onboarding_completed = 1
            AND u.role IN ('free', 'premium', 'category_admin')
            AND u.id != ?
        ";

        $params[] = $currentUserId;

        if (!empty($keyword)) {
            $sql .= ' AND u.full_name LIKE ?';
            $params[] = '%' . $keyword . '%';
        }

        $sql .= '
            GROUP BY u.id
            ORDER BY article_count DESC, u.full_name ASC
            LIMIT ? OFFSET ?
        ';

        $params[] = $limit;
        $params[] = $offset;

        return DB::query($sql, $params);
    }

    // count total users for pagination use
    public function countUsers(?string $keyword, int $currentUserId): int
    {

        $params = [];
        $sql = "
            SELECT COUNT(*) as total
            FROM users
            WHERE onboarding_completed = 1
            AND role IN ('free', 'premium', 'category_admin')
            AND id != ?
        ";

        $params[] = $currentUserId;

        if (!empty($keyword)) {
            $sql .= ' AND full_name LIKE ?';
            $params[] = '%' . $keyword . '%';
        }

        $result = DB::first($sql, $params);

        return (int) ($result['total'] ?? 0);
    }

    // get selected user details
    public function getUserById(int $id): ?array
    {

        $sql = "
            SELECT 
                u.id,
                u.full_name,
                u.avatar_url,
                u.role,
                u.bio,
                SUM(a.status = 'published') AS article_count
            FROM users u
            LEFT JOIN articles a ON a.author_id = u.id
            WHERE u.id = ?
            GROUP BY u.id
            LIMIT 1
        ";

        return DB::first($sql, [$id]);
    }

}
