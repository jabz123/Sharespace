<?php

// stores comment information like article id, user id, content and date
// when comments are retrieved from the database, each row is converted into this comment object
// this makes it easier for pages to access comment data like $comment->content
// also includes a helper function to get the first letter of the commenter name for avatar display


//comment data from db
class Comment {
    public int    $id;
    public int    $articleId;
    public int    $userId;
    public string $commenterName;
    public string $content;
    public string $createdAt;

    public function __construct(array $row) {
        $this->id            = (int)$row['id'];
        $this->articleId     = (int)$row['article_id'];
        $this->userId        = (int)$row['user_id'];
        $this->commenterName = $row['commenter_name'] ?? '';
        $this->content       = $row['content'];
        $this->createdAt     = $row['created_at'] ?? '';
    }

    /** First letter of commenter name for avatar. */
    public function initial(): string {
        return strtoupper(mb_substr($this->commenterName, 0, 1)) ?: '?';
    }
}
