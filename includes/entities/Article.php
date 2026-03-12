<?php

// stores article information like title, content, author and category
// when the controller retrieves articles from the database, each row is converted into this article object
// easier for pages to access article data like $article->title or $article->content
// also includes helper functions used when displaying articles
// such as getting the author's initial, trust level and formatting article content

//all the article data from db for rendering and display logic

class Article {
    public int    $id;
    public string $title;
    public string $excerpt;
    public string $content;
    public int    $authorId;
    public string $authorName;
    public int    $categoryId;
    public string $categoryName;
    public int    $trustScore;
    public bool   $hasMedia;
    public bool   $isPremiumOnly;
    public string $publishedAt;
    public int $viewCount;

    public function __construct(array $row) {
        $this->id            = (int)$row['id'];
        $this->title         = $row['title'];
        $this->excerpt       = $row['excerpt'];
        $this->content       = $row['content'];
        $this->authorId      = (int)$row['author_id'];
        $this->authorName    = $row['author_name']   ?? '';
        $this->categoryId    = (int)$row['category_id'];
        $this->categoryName  = $row['category_name'] ?? '';
        $this->trustScore    = (int)$row['trust_score'];
        $this->hasMedia      = (bool)($row['has_media']       ?? false);
        $this->isPremiumOnly = (bool)($row['is_premium_only'] ?? false);
        $this->publishedAt   = $row['published_at'] ?? '';
        $this->imagePath = $row['image_path'] ?? null;
        $this->viewCount = $row['view_count'] ?? 0;
    }

    //put user initial for default avatar cos no picture yyet
    public function authorInitial(): string {
        return strtoupper(mb_substr($this->authorName, 0, 1)) ?: '?';
    }

    //trust score shit
    public function trustTier(): string {
        if ($this->trustScore >= 80) return 'high';
        if ($this->trustScore >= 60) return 'mid';
        return 'low';
    }


    //converts ## to <h2> 
    //converts ### to <h3>
    //converts - to <ul><li> 
    //converts numbered list to <ol><li>
    public function renderContent(): string {
        $out   = '';
        $paras = explode("\n\n", $this->content);
        foreach ($paras as $p) {
            $p = trim($p);
            if ($p === '') continue;
            if (str_starts_with($p, '## ')) {
                $out .= '<h2>' . htmlspecialchars(substr($p, 3)) . '</h2>';
            } elseif (str_starts_with($p, '### ')) {
                $out .= '<h3>' . htmlspecialchars(substr($p, 4)) . '</h3>';
            } elseif (str_starts_with($p, '- ') || str_contains($p, "\n- ")) {
                $lines = explode("\n", $p);
                $out  .= '<ul>';
                foreach ($lines as $line) {
                    $out .= str_starts_with($line, '- ')
                        ? '<li>' . htmlspecialchars(substr($line, 2)) . '</li>'
                        : '<p>'  . htmlspecialchars($line) . '</p>';
                }
                $out .= '</ul>';
            } elseif (preg_match('/^\d+\./', $p)) {
                $lines = explode("\n", $p);
                $out  .= '<ol>';
                foreach ($lines as $line) {
                    $out .= '<li>' . htmlspecialchars(preg_replace('/^\d+\.\s*/', '', $line)) . '</li>';
                }
                $out .= '</ol>';
            } else {
                $out .= '<p>' . htmlspecialchars($p) . '</p>';
            }
        }
        return $out;
    }
}
