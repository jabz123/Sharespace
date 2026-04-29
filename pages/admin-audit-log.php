<?php
// Standalone Audit Log page — accessible from the sidebar
// Only accessible to users with role = 'system_admin'

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
$filterRole = trim($_GET['role'] ?? '');

// fetch
$entries = [];
$totalCount = 0;
$totalPages = 0;
$auditError = null;

try {
    $entries = $adminCtrl->getAuditLog($perPage, $offset, $filterAction ?: null, $filterRole ?: null);
    $totalCount = $adminCtrl->getAuditLogCount($filterAction ?: null, $filterRole ?: null);
    $totalPages = (int) ceil($totalCount / $perPage);
} catch (Throwable $e) {
    $auditError = 'The audit log is not available yet on this environment. Please ensure the audit_log table exists and try again.';
}

// action → [display label, badge bg colour]
$actionMeta = [
    'login' => ['login',             '#2563eb'],
    'logout' => ['logout',            '#64748b'],
    'register' => ['registered',        '#16a34a'],
    'update_profile' => ['update profile',    '#0f766e'],
    'change_password' => ['change password',   '#7c3aed'],
    'failed_login' => ['failed login',      '#f97316'],
    'failed_login_5_times' => ['5 failed logins', '#dc2626'],
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
    'submit_content' => ['submit content',    '#0891b2'],
    'update_content' => ['edit content',      '#0ea5e9'],
    'delete_content' => ['delete content',    '#ef4444'],
    'save_draft' => ['save draft',        '#64748b'],
    'review_category_content' => ['review content', '#9333ea'],
    'post_comment' => ['post comment',      '#14b8a6'],
    'delete_comment' => ['delete comment',    '#f43f5e'],
    'save_bookmark' => ['save bookmark',     '#f59e0b'],
    'remove_bookmark' => ['remove bookmark',   '#78716c'],
    'flag_article' => ['flag article',      '#f97316'],
    'upload_dataset' => ['upload dataset',    '#4f46e5'],
    'train_model' => ['train model',       '#7c3aed'],
    'update_model_settings' => ['model settings', '#0f172a'],
];

$roleMeta = [
    'system_admin' => ['System Admin', '#1e3a8a'],
    'category_admin' => ['Category Expert', '#6d28d9'],
    'premium' => ['Registered User', '#047857'],
    'free' => ['Free User', '#475569'],
    'unknown' => ['Unknown', '#6b7280'],
];

function audit_page_href(int $page, string $filterAction, string $filterRole): string
{
    $params = ['page' => $page];
    if ($filterAction !== '') {
        $params['filter'] = $filterAction;
    }
    if ($filterRole !== '') {
        $params['role'] = $filterRole;
    }
    return '?' . http_build_query($params);
}

function audit_format_sgt(string $timestamp): string
{
    try {
        return (new DateTimeImmutable($timestamp, new DateTimeZone('UTC')))
            ->setTimezone(new DateTimeZone('Asia/Singapore'))
            ->format('M j, Y g:i A');
    } catch (Throwable $e) {
        return date('M j, Y g:i A', strtotime($timestamp));
    }
}

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
            <strong style="color:var(--fg)"><?= number_format($totalCount) ?></strong>
            total entr<?= $totalCount === 1 ? 'y' : 'ies' ?>
            <?php if ($filterAction): ?>
                filtered by <strong style="color:var(--fg)"><?= htmlspecialchars(str_replace('_', ' ', $filterAction)) ?></strong>
            <?php endif; ?>
            <?php if ($filterRole): ?>
                <?= $filterAction ? 'and' : 'filtered by' ?>
                <strong style="color:var(--fg)"><?= htmlspecialchars($roleMeta[$filterRole][0] ?? str_replace('_', ' ', $filterRole)) ?></strong>
            <?php endif; ?>
            <?php if ($filterAction || $filterRole): ?>
                &mdash; <a href="/pages/admin-audit-log.php" style="color:var(--primary)">clear filters</a>
            <?php endif; ?>
        </div>

        <!-- FILTERS -->
        <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <select name="filter" class="form-input" style="font-size:13px;padding:6px 10px;min-width:180px"
                    onchange="this.form.submit()">
                <option value="">All actions</option>
                <?php foreach (array_keys($actionMeta) as $key): ?>
                    <option value="<?= $key ?>" <?= $filterAction === $key ? 'selected' : '' ?>>
                        <?= htmlspecialchars(str_replace('_', ' ', $key)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="role" class="form-input" style="font-size:13px;padding:6px 10px;min-width:170px"
                    onchange="this.form.submit()">
                <option value="">All roles</option>
                <?php foreach ($roleMeta as $key => $meta): ?>
                    <option value="<?= $key ?>" <?= $filterRole === $key ? 'selected' : '' ?>>
                        <?= htmlspecialchars($meta[0]) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <noscript><button type="submit" class="btn btn-ghost btn-sm">Filter</button></noscript>
        </form>
    </div>

    <!-- LOG TABLE -->
    <div class="card" style="padding:0;overflow:hidden">
        <?php if ($auditError): ?>
            <div style="padding:28px 24px;border-bottom:1px solid var(--border);background:rgba(245,166,35,0.06);color:var(--fg)">
                <div style="font-weight:700;margin-bottom:6px">Audit log unavailable</div>
                <div style="font-size:14px;color:var(--muted)"><?= htmlspecialchars($auditError) ?></div>
            </div>
        <?php elseif (empty($entries)): ?>
            <div style="text-align:center;padding:60px 24px;color:var(--muted)">
                <?php if ($filterAction): ?>
                    No entries found for this action type.
                <?php elseif ($filterRole): ?>
                    No entries found for this role.
                <?php else: ?>
                    No audit log entries yet. Platform actions will appear here.
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
                        <th style="text-align:left;padding:12px 16px;color:var(--muted);font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap">Actor</th>
                        <th style="text-align:left;padding:12px 16px;color:var(--muted);font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap">Role</th>
                        <th style="text-align:left;padding:12px 16px;color:var(--muted);font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap">When (SGT)</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($entries as $entry): ?>
                    <?php
                    $meta = $actionMeta[$entry['action']] ?? [str_replace('_', ' ', $entry['action']), '#6b7280'];
                    [$label, $colour] = $meta;
                    $actorRole = $entry['resolved_actor_role'] ?? $entry['actor_role'] ?? 'unknown';
                    $role = $roleMeta[$actorRole] ?? [str_replace('_', ' ', $actorRole), '#6b7280'];
                    [$roleLabel, $roleColour] = $role;
                    $actorName = $entry['actor_name_display'] ?? $entry['admin_name'] ?? 'Unknown user';
                    $actorEmail = $entry['actor_email_display'] ?? '';
                    $ts = audit_format_sgt($entry['created_at']);
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
                            <?php if ($entry['target_id'] !== null && $entry['target_id'] !== ''): ?>
                                <span style="font-size:12px;color:var(--muted);margin-left:3px">#<?= $entry['target_id'] ?></span>
                            <?php endif; ?>
                        </td>
                        <!-- details -->
                        <td style="padding:14px 16px;max-width:340px;font-size:13px">
                            <?= htmlspecialchars($entry['details']) ?>
                        </td>
                        <!-- actor name -->
                        <td style="padding:14px 16px;white-space:nowrap;font-size:13px;color:var(--muted)">
                            <span style="display:block;color:var(--fg);font-weight:600"><?= htmlspecialchars($actorName) ?></span>
                            <?php if ($actorEmail): ?>
                                <span style="display:block;font-size:12px;color:var(--muted)"><?= htmlspecialchars($actorEmail) ?></span>
                            <?php endif; ?>
                        </td>
                        <!-- actor role -->
                        <td style="padding:14px 16px;white-space:nowrap;font-size:13px;color:var(--muted)">
                            <span style="display:inline-flex;align-items:center;padding:4px 9px;border-radius:20px;font-size:12px;font-weight:600;background:<?= $roleColour ?>;color:#fff">
                                <?= htmlspecialchars($roleLabel) ?>
                            </span>
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
                    <a href="<?= audit_page_href($page - 1, $filterAction, $filterRole) ?>"
                       class="btn btn-ghost btn-sm">&laquo; Prev</a>
                <?php endif; ?>

                <?php
                // show a sliding window of page numbers
                $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            for ($p = $start; $p <= $end; $p++): ?>
                    <a href="<?= audit_page_href($p, $filterAction, $filterRole) ?>"
                       class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-ghost' ?>"
                       style="min-width:34px;text-align:center"><?= $p ?></a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="<?= audit_page_href($page + 1, $filterAction, $filterRole) ?>"
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
