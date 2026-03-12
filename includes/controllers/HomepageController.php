<?php
// function for homepage. 
// reco users articles based on their interests
// reco users articles that their age grp ppl viewed mostly
// reco users articles based on their gender
// get latest 6 articles

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../entities/Article.php';

class HomepageController {  
// recommended articles based on user's chosen interests
// shows 1 top article per category
    public function getRecommendedByInterest($userId) {

        $rows = DB::query(
        "SELECT a.*, u.full_name author_name, c.name category_name,
        COUNT(v.id) AS view_count
        FROM articles a
        JOIN users u ON u.id = a.author_id
        JOIN categories c ON c.id = a.category_id
        JOIN user_interests ui ON ui.category_id = a.category_id
        LEFT JOIN article_views v ON v.article_id = a.id
        WHERE ui.user_id = ?
        GROUP BY a.id
        ORDER BY view_count DESC, a.published_at DESC
        ", [$userId]);

        $unique = [];
        $usedCategories = [];

        foreach ($rows as $r) {
            if (!isset($usedCategories[$r['category_id']])) {
                $unique[] = $r;
                $usedCategories[$r['category_id']] = true;
            }
            if (count($unique) == 3) break;
        }
        return array_map(fn($r) => new Article($r), $unique);
    }



    // articles people in same age group are reading
    public function getPopularByAgeGroup($userId) {
        $rows = DB::query(
        "SELECT a.*, u.full_name author_name, c.name category_name,
        COUNT(v.id) AS view_count
        FROM articles a
        LEFT JOIN article_views v ON v.article_id = a.id
        JOIN users reader ON reader.id = v.user_id
        JOIN users u ON u.id = a.author_id
        JOIN categories c ON c.id = a.category_id
        WHERE reader.age_group =
            (SELECT age_group FROM users WHERE id = ?)

        GROUP BY a.id
        ORDER BY view_count DESC
        LIMIT 3
        ", [$userId]);

        return array_map(fn($r) => new Article($r), $rows);
    }



    // articles popular with same gender readers
    public function getPopularByGender($userId) {
        $rows = DB::query(
        "SELECT a.*, u.full_name author_name, c.name category_name,
        COUNT(v.id) AS view_count
        FROM articles a
        LEFT JOIN article_views v ON v.article_id = a.id
        JOIN users reader ON reader.id = v.user_id
        JOIN users u ON u.id = a.author_id
        JOIN categories c ON c.id = a.category_id
        WHERE reader.gender =
            (SELECT gender FROM users WHERE id = ?)
        GROUP BY a.id
        ORDER BY view_count DESC
        LIMIT 3
        ", [$userId]);
        return array_map(fn($r) => new Article($r), $rows);
    }



    // newest articles on the platform
    public function getLatest(int $limit = 6) {
        $rows = DB::query(
        "SELECT a.*, u.full_name AS author_name, c.name AS category_name,
        COUNT(v.id) AS view_count
        FROM articles a
        JOIN users u ON u.id = a.author_id
        JOIN categories c ON c.id = a.category_id
        LEFT JOIN article_views v ON v.article_id = a.id
        GROUP BY a.id
        ORDER BY a.published_at DESC
        LIMIT $limit"
        );

        return array_map(fn($r) => new Article($r), $rows);
    }

}