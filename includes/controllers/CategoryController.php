<?php

// handles category-related logic for the admin system
// allows system_admin to create, read, update and delete categories
// validates all inputs and enforces data integrity
// returns only data arrays, no html output

require_once __DIR__ . '/../entities/Category.php';
require_once __DIR__ . '/../db.php';

class CategoryController {

    //returns all categories with article count
    //returns array of associative arrays
    public function getAllWithStats(): array {
        return DB::query(
            'SELECT c.*, COUNT(a.id) AS article_count
             FROM categories c
             LEFT JOIN articles a ON a.category_id = c.id
             GROUP BY c.id
             ORDER BY c.name'
        );
    }

    //returns single category by id, or null if not found
    public function getById(int $id): ?array {
        return DB::first(
            'SELECT * FROM categories WHERE id = ?',
            [$id]
        );
    }

    //create new category
    //return ['ok' => true] or ['error' => '...']
    public function create(string $name, string $description): array {
        $name        = trim($name);
        $description = trim($description);

        if (empty($name)) {
            return ['error' => 'Category name is required.'];
        }
        if (strlen($name) > 100) {
            return ['error' => 'Category name cannot exceed 100 characters.'];
        }
        if (strlen($description) > 500) {
            return ['error' => 'Description cannot exceed 500 characters.'];
        }

        if (DB::first('SELECT id FROM categories WHERE name = ?', [$name])) {
            return ['error' => 'Category with this name already exists.'];
        }

        DB::execute(
            'INSERT INTO categories (name, description) VALUES (?, ?)',
            [$name, $description]
        );

        return ['ok' => true];
    }

    //update existing category
    //return ['ok' => true] or ['error' => '...']
    public function update(int $id, string $name, string $description): array {
        $name        = trim($name);
        $description = trim($description);

        if (empty($name)) {
            return ['error' => 'Category name is required.'];
        }
        if (strlen($name) > 100) {
            return ['error' => 'Category name cannot exceed 100 characters.'];
        }
        if (strlen($description) > 500) {
            return ['error' => 'Description cannot exceed 500 characters.'];
        }

        if (DB::first('SELECT id FROM categories WHERE name = ? AND id != ?', [$name, $id])) {
            return ['error' => 'Another category with this name already exists.'];
        }

        DB::execute(
            'UPDATE categories SET name = ?, description = ? WHERE id = ?',
            [$name, $description, $id]
        );

        return ['ok' => true];
    }

    //delete category, can only delete if no articles exist
    //return ['ok' => true] or ['error' => '...']
    public function delete(int $id): array {
        $articleCount = DB::first(
            'SELECT COUNT(*) AS count FROM articles WHERE category_id = ?',
            [$id]
        )['count'] ?? 0;

        if ($articleCount > 0) {
            return ['error' => 'Cannot delete category with existing articles.'];
        }

        DB::execute('DELETE FROM categories WHERE id = ?', [$id]);

        return ['ok' => true];
    }

}
