<?php
//landing page management for admin side 
require_once __DIR__ . '/../db.php';

class LandingPageController
{
    //fetch and update hero section
    public function getHero(): ?array
    {
        return DB::first("SELECT * FROM landing_sections WHERE section_key = 'hero'");
    }

    public function updateHero(array $data): int
    {
        return DB::execute(
            "UPDATE landing_sections
             SET badge = ?, title = ?, title_highlight = ?, subtitle = ?
             WHERE section_key = 'hero'",
            [
                trim($data['badge'] ?? ''),
                trim($data['title'] ?? ''),
                trim($data['title_highlight'] ?? ''),
                trim($data['subtitle'] ?? ''),
            ]
        );
    }

    public function getDemoVideo(): ?array
    {
        return DB::first("SELECT * FROM landing_sections WHERE section_key = 'demo_video'");
    }

    public function updateDemoVideo(array $data): int
    {
        return DB::execute(
            "UPDATE landing_sections
             SET title = ?, subtitle = ?, video_url = ?
             WHERE section_key = 'demo_video'",
            [
                trim($data['title'] ?? ''),
                trim($data['subtitle'] ?? ''),
                trim($data['video_url'] ?? ''),
            ]
        );
    }

    public function getFeatures(): array
    {
        return DB::query('SELECT * FROM landing_features ORDER BY display_order ASC, id ASC');
    }

    public function getFeatureById(int $id): ?array
    {
        return DB::first('SELECT * FROM landing_features WHERE id = ?', [$id]);
    }

    public function addFeature(array $data): int
    {
        DB::execute(
            'INSERT INTO landing_features (icon_path, title, description, display_order)
             VALUES (?, ?, ?, ?)',
            [
                trim($data['icon_path'] ?? ''),
                trim($data['title'] ?? ''),
                trim($data['description'] ?? ''),
                (int) ($data['display_order'] ?? 0),
            ]
        );

        return (int) DB::lastId();
    }

    public function updateFeature(int $id, array $data): int
    {
        return DB::execute(
            'UPDATE landing_features
             SET icon_path = ?, title = ?, description = ?, display_order = ?
             WHERE id = ?',
            [
                trim($data['icon_path'] ?? ''),
                trim($data['title'] ?? ''),
                trim($data['description'] ?? ''),
                (int) ($data['display_order'] ?? 0),
                $id,
            ]
        );
    }

    public function deleteFeature(int $id): int
    {
        return DB::execute('DELETE FROM landing_features WHERE id = ?', [$id]);
    }

    public function getSteps(): array
    {
        return DB::query('SELECT * FROM landing_steps ORDER BY display_order ASC, id ASC');
    }

    public function getStepById(int $id): ?array
    {
        return DB::first('SELECT * FROM landing_steps WHERE id = ?', [$id]);
    }

    public function addStep(array $data): int
    {
        DB::execute(
            'INSERT INTO landing_steps (icon_path, step_number, title, description, display_order)
             VALUES (?, ?, ?, ?, ?)',
            [
                trim($data['icon_path'] ?? ''),
                trim($data['step_number'] ?? ''),
                trim($data['title'] ?? ''),
                trim($data['description'] ?? ''),
                (int) ($data['display_order'] ?? 0),
            ]
        );

        return (int) DB::lastId();
    }

    public function updateStep(int $id, array $data): int
    {
        return DB::execute(
            'UPDATE landing_steps
             SET icon_path = ?, step_number = ?, title = ?, description = ?, display_order = ?
             WHERE id = ?',
            [
                trim($data['icon_path'] ?? ''),
                trim($data['step_number'] ?? ''),
                trim($data['title'] ?? ''),
                trim($data['description'] ?? ''),
                (int) ($data['display_order'] ?? 0),
                $id,
            ]
        );
    }

    public function deleteStep(int $id): int
    {
        return DB::execute('DELETE FROM landing_steps WHERE id = ?', [$id]);
    }

    //pricing plan logic
    public function getPlans(): array
    {
        return DB::query('SELECT * FROM landing_pricing_plans ORDER BY display_order ASC, id ASC');
    }

    public function getPlanById(int $id): ?array
    {
        return DB::first('SELECT * FROM landing_pricing_plans WHERE id = ?', [$id]);
    }

    public function addPlan(array $data): int
    {
        DB::execute(
            'INSERT INTO landing_pricing_plans
             (name, price, price_suffix, description, button_text, button_link, is_popular, display_order)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                trim($data['name'] ?? ''),
                trim($data['price'] ?? ''),
                trim($data['price_suffix'] ?? ''),
                trim($data['description'] ?? ''),
                trim($data['button_text'] ?? ''),
                trim($data['button_link'] ?? ''),
                isset($data['is_popular']) ? 1 : 0,
                (int) ($data['display_order'] ?? 0),
            ]
        );

        return (int) DB::lastId();
    }

    public function updatePlan(int $id, array $data): int
    {
        return DB::execute(
            'UPDATE landing_pricing_plans
             SET name = ?, price = ?, price_suffix = ?, description = ?, button_text = ?, button_link = ?, is_popular = ?, display_order = ?
             WHERE id = ?',
            [
                trim($data['name'] ?? ''),
                trim($data['price'] ?? ''),
                trim($data['price_suffix'] ?? ''),
                trim($data['description'] ?? ''),
                trim($data['button_text'] ?? ''),
                trim($data['button_link'] ?? ''),
                isset($data['is_popular']) ? 1 : 0,
                (int) ($data['display_order'] ?? 0),
                $id,
            ]
        );
    }

    public function deletePlan(int $id): int
    {
        return DB::execute('DELETE FROM landing_pricing_plans WHERE id = ?', [$id]);
    }

    public function getPlanFeaturesByPlanId(int $planId): array
    {
        return DB::query(
            'SELECT * FROM landing_pricing_features WHERE plan_id = ? ORDER BY display_order ASC, id ASC',
            [$planId]
        );
    }

    public function getAllPlanFeatures(): array
    {
        return DB::query('SELECT * FROM landing_pricing_features ORDER BY plan_id ASC, display_order ASC, id ASC');
    }

    public function getPlanFeatureById(int $id): ?array
    {
        return DB::first('SELECT * FROM landing_pricing_features WHERE id = ?', [$id]);
    }

    public function addPlanFeature(array $data): int
    {
        DB::execute(
            'INSERT INTO landing_pricing_features (plan_id, feature_text, is_included, display_order)
             VALUES (?, ?, ?, ?)',
            [
                (int) ($data['plan_id'] ?? 0),
                trim($data['feature_text'] ?? ''),
                isset($data['is_included']) ? 1 : 0,
                (int) ($data['display_order'] ?? 0),
            ]
        );

        return (int) DB::lastId();
    }

    public function updatePlanFeature(int $id, array $data): int
    {
        return DB::execute(
            'UPDATE landing_pricing_features
             SET plan_id = ?, feature_text = ?, is_included = ?, display_order = ?
             WHERE id = ?',
            [
                (int) ($data['plan_id'] ?? 0),
                trim($data['feature_text'] ?? ''),
                isset($data['is_included']) ? 1 : 0,
                (int) ($data['display_order'] ?? 0),
                $id,
            ]
        );
    }

    public function deletePlanFeature(int $id): int
    {
        return DB::execute('DELETE FROM landing_pricing_features WHERE id = ?', [$id]);
    }
}
