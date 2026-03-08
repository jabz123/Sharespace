<?php

// boundary page for writing and editing article
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';

$auth        = new AuthController();
$articleCtrl = new ArticleController();

$auth->requireAuth();
$user = $auth->currentUser();
$isPremium = ($user->role === 'premium');

//load categories for dropdown
$categories = $articleCtrl->getAllCategories();

//check if editing existing article
$editId  = (int)($_GET['id'] ?? 0);
$article = null;
$isEdit  = false;

if ($editId) {
    $article = $articleCtrl->getById($editId);
    // only the author can edit own article
    if (!$article || $article->authorId !== $user->id) {
        redirect('/pages/my-articles.php', 'Article not found or permission denied.');
    }
    $isEdit = true;
}

//update and publish article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publish'])) {
     $imagePath = null;
     //handle image upload for premium users
        if ($isPremium && isset($_FILES['article_image']) && $_FILES['article_image']['error'] === 0) {

            // $uploadDir = __DIR__ . '/../uploads/articles/';
            $uploadDir = __DIR__ . '/../public/uploads/articles/';
            if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
            }

            //create unique filename
            $fileName = time() . '_' . basename($_FILES['article_image']['name']);

            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['article_image']['tmp_name'], $targetPath)) {
                $imagePath = 'uploads/articles/' . $fileName;
            }
        }

         //add image path into POST data
        $_POST['image_path'] = $imagePath;

    if ($isEdit) {
        $result = $articleCtrl->update($editId, $user->id, $_POST);
        if (isset($result['ok'])) {
            redirect('/pages/my-articles.php', null, 'Article updated!');
        }
    } else {
        $result = $articleCtrl->publish($user->id, $_POST);
        if (isset($result['ok'])) {
            redirect('/pages/my-articles.php', null, 'Article published!');
        }
    }
    flash_set('flash_error', $result['error']);
}

//shows what user submitted if validation fail
$val = [
    'title'       => $_POST['title']       ?? ($article?->title      ?? ''),
    'excerpt'     => $_POST['excerpt']     ?? ($article?->excerpt     ?? ''),
    'content'     => $_POST['content']     ?? ($article?->content     ?? ''),
    'category_id' => $_POST['category_id'] ?? ($article?->categoryId  ?? 0),
];

//render form with existing article data if editing
//render empty form if writing new articel
page_head($isEdit ? 'Edit Article' : 'Write Article');
?>
<div class="dashboard-layout">
    <?php sidebar($user); ?>
    <main>
        <?php dash_header(
            $isEdit ? 'Edit Article' : 'Write Article',
            $isEdit ? 'Update your article' : 'Share your story with the world'
        ); ?>
        <?php flash_messages(); ?>
        <div class="page-content">

            <form method="POST" id="write-form" enctype="multipart/form-data">
                <?php if ($isPremium): ?>

                <div class="image-upload-container">

                    <div class="image-preview" id="imagePreview">
                        <span>No image selected</span>
                    </div>

                    <input type="file" id="articleImageInput" name="article_image" accept="image/*" hidden>

                    <div class="image-buttons">
                        <button type="button" class="btn btn-dark" onclick="selectImage()">Select Image</button>
                        <button type="button" class="btn btn-light" onclick="removeImage()">Remove Image</button>
                    </div>

                </div>

                <?php endif; ?>

                <div class="form-group">
                    <label for="title">Article Title</label>
                    <input type="text" id="title" name="title"
                        placeholder="Article title"
                        value="<?= htmlspecialchars($val['title']) ?>"
                        required />
                </div>

                <div class="form-group">
                    <label for="excerpt">Article Summary</label>
                    <input type="text" id="excerpt" name="excerpt"
                        placeholder="Brief summary of your article"
                        value="<?= htmlspecialchars($val['excerpt']) ?>"
                        required />
                </div>

                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat->id ?>"
                                <?= (int)$val['category_id'] === $cat->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea id="content" name="content"
                        placeholder="Write your article here… (## Heading, - bullets)"
                        style="min-height:360px"
                        required><?= htmlspecialchars($val['content']) ?></textarea>
                </div>

                <div class="flex gap-2">
                    <button type="submit" name="publish" class="btn btn-primary">
                        <?= $isEdit ? '💾 Save Changes' : 'Publish Article' ?>
                    </button>
                    <a href="/pages/my-articles.php" class="btn btn-ghost">Cancel</a>
                </div>

            </form>

        </div>
    </main>
</div>
<script>

        function selectImage() {
            document.getElementById('articleImageInput').click();
        }

        document.getElementById('articleImageInput').addEventListener('change', function(e) {

            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');

            if (!file) return;

            const reader = new FileReader();

            reader.onload = function(event) {
                preview.innerHTML = `<img src="${event.target.result}" alt="Preview">`;
            }

            reader.readAsDataURL(file);
        });

        function removeImage() {
            document.getElementById('articleImageInput').value = '';
            document.getElementById('imagePreview').innerHTML = '<span>No image selected</span>';
        }

        </script>

<?php page_foot(); ?>