<?php

//handles all comment related db queries and shit


require_once __DIR__ . '/../entities/Comment.php';

class CommentController {

    //return all comments for article. show oldest first
    public function getForArticle(int $articleId): array {
        $rows = DB::query(
            'SELECT cm.*, u.full_name AS commenter_name
             FROM comments cm
             JOIN users u ON u.id = cm.user_id
             WHERE cm.article_id = ?
             ORDER BY cm.created_at ASC',
            [$articleId]
        );
        return array_map(fn($r) => new Comment($r), $rows);
    }

    //post comment on article, validate and insert into db
    public function post(int $articleId, int $userId, string $body): array {
        $body = trim($body);
        if ($body === '') {
            return ['error' => 'Comment cannot be empty.'];
        }
        if (strlen($body) > 2000) {
            return ['error' => 'Comment is too long (max 2000 characters).'];
        }

        DB::execute(
            'INSERT INTO comments (article_id, user_id, content) VALUES (?, ?, ?)',
            [$articleId, $userId, $body]
        );

        return ['ok' => true];
    }

    //delete comment from article. only can delete own comment
    public function delete(int $commentId, int $requestingUserId): array {
        $affected = DB::execute(
            'DELETE FROM comments WHERE id = ? AND user_id = ?',
            [$commentId, $requestingUserId]
        );

        if ($affected === 0) {
            return ['error' => 'Comment not found or permission denied.'];
        }

        return ['ok' => true];
    }
}
