<?php
// Standalone Audit Log page — accessible from the sidebar
// Only accessible to users with role = 'system_admin'
//will call AdminController to fetch paginated log entries, 
//with optional filtering by action type like suspend article

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/AdminController.php';

$auth = new AuthController();
$adminCtrl = new AdminController();

$auth->requireAuth();
$user = $auth->currentUser();
$adminCtrl->requireAdmin($user);

// pagination
$perPage = 25;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// optional action-type filter
$filterAction = trim($_GET['filter'] ?? '');

// fetch
$entries = $adminCtrl->getAuditLog($perPage, $offset, $filterAction ?: null);
$totalCount = $adminCtrl->getAuditLogCount($filterAction ?: null);
$totalPages = (int) ceil($totalCount / $perPage);

// action → [display label, badge bg colour]
$actionMeta = [
    'suspend_article' => ['suspend article',   '#e07b39'],
    'unsuspend_article' => ['unsuspend article', '#3b82f6'],
    'delete_article' => ['delete article',    '#ef4444'],
    'suspend_user' => ['suspend user',      '#e07b39'],
    'reinstate_user' => ['reinstate user',    '#22c55e'],
    'create_category' => ['create category',   '#111827'],
    'update_category' => ['update category',   '#6b7280'],
    'delete_category' => ['delete category',   '#ef4444'],
    'assign_role' => ['assign role',       '#6b7280'],
    'unassign_role' => ['unassign role',     '#9ca3af'],
];

page_head('Audit Log', true);
?>
<div class="dashboard-layout">
<?php sidebar($user); ?>
<main>
<?php dash_header('Audit Log', 'Track all platform activity'); ?>
<?php flash_messages(); ?>

<div class="page-content">

    <!-- SUMMARY BAR -->
    <div style="display:flex;align-items:center;justify-content:space-between;
                flex-wrap:wrap;gap:12px;margin-bottom:24px">
        <div style="font-size:13px;color:var(--muted)">
            Showing
            <strong style="color:var(--text)"><?= number_format($totalCount) ?></strong>
            total entr<?= $totalCount === 1 ? 'y' : 'ies' ?>
            <?php if ($filterAction): ?>
                filtered by <strong style="color:var(--text)"><?= htmlspecialchars(str_replace('_', ' ', $filterAction)) ?></strong>
                &mdash; <a href="/pages/admin-audit-log.php" style="color:var(--primary)">clear filter</a>
            <?php endif; ?>
        </div>

        <!-- ACTION TYPE FILTER -->
        <form method="GET" style="display:flex;gap:8px;align-items:center">
            <select name="filter" class="form-input" style="font-size:13px;padding:6px 10px;min-width:180px"
                    onchange="this.form.submit()">
                <option value="">All actions</option>
                <?php foreach (array_keys($actionMeta) as $key): ?>
                    <option value="<?= $key ?>" <?= $filterAction === $key ? 'selected' : '' ?>>
                        <?= htmlspecialchars(str_replace('_', ' ', $key)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <noscript><button type="submit" class="btn btn-ghost btn-sm">Filter</button></noscript>
        </form>
    </div>

    <!-- LOG TABLE -->
    <div class="card" style="padding:0;overflow:hidden">
        <?php if (empty($entries)): ?>
            <div style="text-align:center;padding:60px 24px;color:var(--muted)">
                <?php if ($filterAction): ?>
                    No entries found for this action type.
                <?php else: ?>
                    No audit log entries yet. Actions performed on the Admin Dashboard will appear here.
                <?php endif; ?>
            </div>
        <?php else: ?>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:14px">
                <thead>
                    <tr style="border-bottom:2px solid var(--border);background:var(--surface-alt,var(--surface))">
                        <th style="text-align:left;padding:12px 16px;color:var(--muted);font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap">Action</th>
                        <th style="text-align:left;padding:12px 16px;color:var(--muted);font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.06em">Target</th>
                        <th style="text-align:left;padding:12px 16px;color:var(--muted);font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.06em">Details</th>
                        <th style="text-align:left;padding:12px 16px;color:var(--muted);font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap">Admin</th>
                        <th style="text-align:left;padding:12px 16px;color:var(--muted);font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap">When</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($entries as $entry): ?>
                    <?php
                    $meta = $actionMeta[$entry['action']] ?? [str_replace('_', ' ', $entry['action']), '#6b7280'];
                    [$label, $colour] = $meta;
                    $ts = date('M j, Y g:i A', strtotime($entry['created_at']));
                    ?>
                    <tr style="border-bottom:1px solid var(--border)">
                        <!-- action badge -->
                        <td style="padding:14px 16px;white-space:nowrap">
                            <span style="display:inline-flex;align-items:center;gap:5px;
                                         padding:4px 11px;border-radius:20px;font-size:12px;
                                         font-weight:600;background:<?= $colour ?>;color:#fff">
                                <?= htmlspecialchars($label) ?>
                            </span>
                        </td>
                        <!-- target type + id -->
                        <td style="padding:14px 16px;white-space:nowrap">
                            <span style="font-weight:600;font-size:13px"><?= htmlspecialchars($entry['target_type']) ?></span>
                            <?php if ($entry['target_id']): ?>
                                <span style="font-size:12px;color:var(--muted);margin-left:3px">#<?= $entry['target_id'] ?></span>
                            <?php endif; ?>
                        </td>
                        <!-- details -->
                        <td style="padding:14px 16px;max-width:340px;font-size:13px">
                            <?= htmlspecialchars($entry['details']) ?>
                        </td>
                        <!-- admin name -->
                        <td style="padding:14px 16px;white-space:nowrap;font-size:13px;color:var(--muted)">
                            <?= htmlspecialchars($entry['admin_name']) ?>
                        </td>
                        <!-- timestamp -->
                        <td style="padding:14px 16px;white-space:nowrap;font-size:13px;color:var(--muted)">
                            <?= $ts ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <?php if ($totalPages > 1): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:16px 20px;border-top:1px solid var(--border);flex-wrap:wrap;gap:10px">
            <span style="font-size:13px;color:var(--muted)">
                Page <?= $page ?> of <?= $totalPages ?>
            </span>
            <div style="display:flex;gap:6px;flex-wrap:wrap">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?><?= $filterAction ? '&filter=' . urlencode($filterAction) : '' ?>"
                       class="btn btn-ghost btn-sm">&laquo; Prev</a>
                <?php endif; ?>

                <?php
                // show a sliding window of page numbers
                $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            for ($p = $start; $p <= $end; $p++): ?>
                    <a href="?page=<?= $p ?><?= $filterAction ? '&filter=' . urlencode($filterAction) : '' ?>"
                       class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-ghost' ?>"
                       style="min-width:34px;text-align:center"><?= $p ?></a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?><?= $filterAction ? '&filter=' . urlencode($filterAction) : '' ?>"
                       class="btn btn-ghost btn-sm">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php endif; ?>
    </div>

</div>
</main>
</div>
<?php page_foot(); ?>
