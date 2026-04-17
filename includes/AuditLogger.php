<?php

require_once __DIR__ . '/db.php';

class AuditLogger {
    private static bool $schemaReady = false;

    public static function ensureSchema(): void {
        if (self::$schemaReady) {
            return;
        }

        $table = DB::first("SHOW TABLES LIKE 'audit_log'");
        if (!$table) {
            DB::execute(
                "CREATE TABLE audit_log (
                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    admin_id INT NULL,
                    actor_id INT NULL,
                    actor_role VARCHAR(50) NULL,
                    actor_name VARCHAR(255) NULL,
                    actor_email VARCHAR(255) NULL,
                    action VARCHAR(50) NOT NULL,
                    target_type VARCHAR(50) NOT NULL,
                    target_id INT NULL,
                    details VARCHAR(255) NOT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    KEY idx_admin_id (admin_id),
                    KEY idx_actor_id (actor_id),
                    KEY idx_action (action),
                    KEY idx_actor_role (actor_role),
                    KEY idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
            self::$schemaReady = true;
            return;
        }

        $columns = array_column(DB::query('SHOW COLUMNS FROM audit_log'), 'Field');
        $has = fn(string $column): bool => in_array($column, $columns, true);

        try {
            DB::execute('ALTER TABLE audit_log MODIFY admin_id INT NULL');
        } catch (Throwable $e) {
            error_log('Audit schema admin_id migration skipped: ' . $e->getMessage());
        }

        $adds = [
            'actor_id' => 'ALTER TABLE audit_log ADD actor_id INT NULL AFTER admin_id',
            'actor_role' => 'ALTER TABLE audit_log ADD actor_role VARCHAR(50) NULL AFTER actor_id',
            'actor_name' => 'ALTER TABLE audit_log ADD actor_name VARCHAR(255) NULL AFTER actor_role',
            'actor_email' => 'ALTER TABLE audit_log ADD actor_email VARCHAR(255) NULL AFTER actor_name',
        ];

        foreach ($adds as $column => $sql) {
            if (!$has($column)) {
                try {
                    DB::execute($sql);
                } catch (Throwable $e) {
                    if (!str_contains($e->getMessage(), 'Duplicate column')) {
                        throw $e;
                    }
                }
            }
        }

        $indexes = array_column(DB::query('SHOW INDEX FROM audit_log'), 'Key_name');
        $indexAdds = [
            'idx_actor_id' => 'ALTER TABLE audit_log ADD KEY idx_actor_id (actor_id)',
            'idx_action' => 'ALTER TABLE audit_log ADD KEY idx_action (action)',
            'idx_actor_role' => 'ALTER TABLE audit_log ADD KEY idx_actor_role (actor_role)',
            'idx_created_at' => 'ALTER TABLE audit_log ADD KEY idx_created_at (created_at)',
        ];

        foreach ($indexAdds as $name => $sql) {
            if (!in_array($name, $indexes, true)) {
                try {
                    DB::execute($sql);
                } catch (Throwable $e) {
                    if (!str_contains($e->getMessage(), 'Duplicate key name')) {
                        throw $e;
                    }
                }
            }
        }

        self::$schemaReady = true;
    }

    public static function log(
        ?int $actorId,
        string $action,
        string $targetType,
        ?int $targetId,
        string $details,
        ?string $actorRole = null,
        ?string $actorName = null,
        ?string $actorEmail = null
    ): void {
        try {
            self::ensureSchema();

            if ($actorId && (!$actorRole || !$actorName || !$actorEmail)) {
                $actor = DB::first(
                    'SELECT full_name, email, role FROM users WHERE id = ?',
                    [$actorId]
                );

                if ($actor) {
                    $actorRole = $actorRole ?: $actor['role'];
                    $actorName = $actorName ?: $actor['full_name'];
                    $actorEmail = $actorEmail ?: $actor['email'];
                }
            }

            DB::execute(
                'INSERT INTO audit_log
                    (admin_id, actor_id, actor_role, actor_name, actor_email, action, target_type, target_id, details)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $actorId,
                    $actorId,
                    $actorRole,
                    $actorName,
                    $actorEmail,
                    substr($action, 0, 50),
                    substr($targetType, 0, 50),
                    $targetId,
                    substr($details, 0, 255),
                ]
            );
        } catch (Throwable $e) {
            error_log('Audit log write failed: ' . $e->getMessage());
        }
    }

    public static function entries(
        int $limit = 50,
        int $offset = 0,
        ?string $filterAction = null,
        ?string $filterRole = null
    ): array {
        self::ensureSchema();

        [$whereSql, $params] = self::filters($filterAction, $filterRole);

        return DB::query(
            "SELECT
                al.*,
                COALESCE(al.actor_id, al.admin_id) AS resolved_actor_id,
                COALESCE(al.actor_role, u.role, 'unknown') AS resolved_actor_role,
                COALESCE(al.actor_name, u.full_name, 'Unknown user') AS actor_name_display,
                COALESCE(al.actor_email, u.email, '') AS actor_email_display,
                COALESCE(al.actor_name, u.full_name, 'Unknown user') AS admin_name
             FROM audit_log al
             LEFT JOIN users u ON u.id = COALESCE(al.actor_id, al.admin_id)
             $whereSql
             ORDER BY al.created_at DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$limit, $offset])
        );
    }

    public static function count(?string $filterAction = null, ?string $filterRole = null): int {
        self::ensureSchema();

        [$whereSql, $params] = self::filters($filterAction, $filterRole);

        return (int)(DB::first(
            "SELECT COUNT(*) AS cnt
             FROM audit_log al
             LEFT JOIN users u ON u.id = COALESCE(al.actor_id, al.admin_id)
             $whereSql",
            $params
        )['cnt'] ?? 0);
    }

    public static function countRecentForTarget(
        string $action,
        string $targetType,
        ?int $targetId,
        int $minutes = 15
    ): int {
        self::ensureSchema();

        if ($targetId === null) {
            return 0;
        }

        return (int)(DB::first(
            "SELECT COUNT(*) AS cnt
             FROM audit_log
             WHERE action = ?
               AND target_type = ?
               AND target_id = ?
               AND created_at >= DATE_SUB(NOW(), INTERVAL " . max(1, $minutes) . " MINUTE)",
            [$action, $targetType, $targetId]
        )['cnt'] ?? 0);
    }

    private static function filters(?string $filterAction, ?string $filterRole): array {
        $conditions = [];
        $params = [];

        if ($filterAction) {
            $conditions[] = 'al.action = ?';
            $params[] = $filterAction;
        }

        if ($filterRole) {
            $conditions[] = "COALESCE(al.actor_role, u.role, 'unknown') = ?";
            $params[] = $filterRole;
        }

        $whereSql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        return [$whereSql, $params];
    }
}
