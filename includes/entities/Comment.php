<?php

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
