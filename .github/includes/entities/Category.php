<?php

// stores category information like id, name and description
// used when retrieving categories so pages can access data like $category->name

//categiry data from db
class Category {
    public int    $id;
    public string $name;
    public string $description;

    public function __construct(array $row) {
        $this->id          = (int)$row['id'];
        $this->name        = $row['name'];
        $this->description = $row['description'] ?? '';
    }
}
