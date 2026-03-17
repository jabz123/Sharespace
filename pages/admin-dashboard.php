<?php

// admin dashboard for system administrators
// displays category management interface
// no form submission handling - display only

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/CategoryController.php';

$auth = new AuthController();
$catCtrl = new CategoryController();

// check if user is logged in
$user = $auth->currentUser();
if (!$user) {
    header('Location: /');
    exit;
}

// check if user is system_admin
if ($user->role !== 'system_admin') {
    header('Location: /dashboard.php');
    exit;
}

// get all categories
$categories = $catCtrl->getAll();

page_head('Admin Dashboard');
?>
<div class="dashboard-layout">
    <?php sidebar($user); ?>
    <main>
        <?php dash_header('Admin Dashboard', 'System Administration Panel'); ?>
        <div class="page-content">
            
            <!-- Create Category Button -->
            <div style="margin-bottom:32px">
                <button type="button" class="btn btn-primary" onclick="openCreateModal()">➕ Add Category</button>
            </div>

            <!-- Categories Table -->
            <div class="card">
                <h2 style="font-size:20px;font-weight:700;margin-bottom:16px;padding:24px;padding-bottom:0">📋 Categories</h2>
                
                <?php if (empty($categories)): ?>
                    <div style="padding:32px;text-align:center;color:#64748b">
                        <p>No categories yet.</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x:auto">
                        <table style="width:100%;border-collapse:collapse">
                            <thead>
                                <tr style="background:#f1f5f9;border-bottom:1px solid #e2e8f0">
                                    <th style="padding:12px 24px;text-align:left;font-weight:600">Name</th>
                                    <th style="padding:12px 24px;text-align:left;font-weight:600">Description</th>
                                    <th style="padding:12px 24px;text-align:center;font-weight:600">Articles</th>
                                    <th style="padding:12px 24px;text-align:center;font-weight:600">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                    <tr style="border-bottom:1px solid #e2e8f0">
                                        <td style="padding:12px 24px;font-weight:600"><?= htmlspecialchars($cat['name']) ?></td>
                                        <td style="padding:12px 24px;color:#64748b;font-size:14px"><?= htmlspecialchars(mb_substr($cat['description'], 0, 50)) ?></td>
                                        <td style="padding:12px 24px;text-align:center"><?= $cat['article_count'] ?></td>
                                        <td style="padding:12px 24px;text-align:center;display:flex;gap:8px;justify-content:center">
                                            <!-- Edit -->
                                            <button type="button" class="btn btn-ghost btn-sm" 
                                                onclick="openEditModal(<?= $cat['id'] ?>, '<?= htmlspecialchars(addslashes($cat['name'])) ?>', '<?= htmlspecialchars(addslashes($cat['description'])) ?>')">
                                                ✏️ Edit
                                            </button>

                                            <!-- Delete -->
                                            <button type="button" class="btn btn-ghost btn-sm" 
                                                style="color:#e53e3e"
                                                onclick="deleteCategory(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['name']) ?>')">
                                                🗑 Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div id="categoryModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
            <div class="card" style="max-width:500px;width:90%;padding:24px">
                <h3 style="font-size:18px;font-weight:700;margin-bottom:16px" id="modalTitle">Add Category</h3>
                <form method="POST" action="/admin/categories-handler.php" id="categoryForm">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="category_id" id="categoryId" value="">
                    
                    <div class="form-group">
                        <label for="catName">Category Name</label>
                        <input type="text" id="catName" name="name" maxlength="100" required>
                    </div>

                    <div class="form-group">
                        <label for="catDesc">Description</label>
                        <textarea id="catDesc" name="description" maxlength="500" rows="3"></textarea>
                    </div>

                    <div style="display:flex;gap:8px;justify-content:flex-end">
                        <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Add Category';
    document.getElementById('formAction').value = 'create';
    document.getElementById('submitBtn').textContent = 'Add';
    document.getElementById('categoryId').value = '';
    document.getElementById('catName').value = '';
    document.getElementById('catDesc').value = '';
    document.getElementById('categoryModal').style.display = 'flex';
}

function openEditModal(id, name, description) {
    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('formAction').value = 'update';
    document.getElementById('submitBtn').textContent = 'Save';
    document.getElementById('categoryId').value = id;
    document.getElementById('catName').value = name;
    document.getElementById('catDesc').value = description;
    document.getElementById('categoryModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('categoryModal').style.display = 'none';
}

function deleteCategory(id, name) {
    if (confirm(`Delete "${name}"?\n(Cannot delete if articles exist)`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/categories-handler.php';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="category_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

window.onclick = function(event) {
    const modal = document.getElementById('categoryModal');
    if (event.target === modal) closeModal();
}
</script>

<?php page_foot(); ?>
