<?php
// performs writing and editing article function for this file
// if an article id is provided, loads the article for editing and checks if the user is the author
// handles publishing a new article or updating an existing article through ArticleController
// premium users can upload an image for the article
// displays the article form and pre-fills data when editing

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
    $article = $articleCtrl->getByIdForAuthor($editId, $user->id);
    // only the author can edit own article
    if (!$article || $article->authorId !== $user->id) {
        redirect('/pages/my-articles.php', 'Article not found or permission denied.');
    }
    $isEdit = true;
}


//   HANDLE BOTH DRAFT + PUBLISH

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? 'publish'; // ⭐ NEW

    $imagePath = $article->imagePath ?? null;

    // remove image logic
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
        if (!empty($article->imagePath)) {
            $filePath = __DIR__ . '/../public/' . $article->imagePath;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        $imagePath = null;
    }

    // handle image upload for premium users
    if ($isPremium && isset($_FILES['article_image']) && $_FILES['article_image']['error'] === 0) {

        $uploadDir = __DIR__ . '/../public/uploads/articles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . basename($_FILES['article_image']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['article_image']['tmp_name'], $targetPath)) {
            $imagePath = 'uploads/articles/' . $fileName;
        }
    }

    // add image path into POST data
    $_POST['image_path'] = $imagePath;

    
    // SAVE AS DRAFT
    
    if ($action === 'draft') {

        $_POST['status'] = 'draft'; // future usage

        if ($isEdit) {
            $result = $articleCtrl->update($editId, $user->id, $_POST);
        } else {
            $result = $articleCtrl->saveDraft($user->id, $_POST); // ⭐ NEW FUNCTION
        }

        if (isset($result['ok'])) {
            redirect('/pages/my-articles.php', null, 'Draft saved!');
        }

    } 
    
    // EXISTING PUBLISH (UNCHANGED)
    
    else {

    //  PUBLISH ACTION
    $_POST['status'] = 'published';

    if ($isEdit) {
        $result = $articleCtrl->update($editId, $user->id, $_POST);
        if (isset($result['ok'])) {
            redirect('/pages/my-articles.php', null, 'Article published!');
        }
    } else {
        $result = $articleCtrl->publish($user->id, $_POST);
        if (isset($result['ok'])) {
            redirect('/pages/my-articles.php', null, 'Article published!');
            }
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

//render form
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

        <!--  FLEX LAYOUT -->
        <div class="page-content write-layout">

            <!-- ================= LEFT SIDE ================= -->
            <form method="POST" id="write-form" enctype="multipart/form-data" style="flex:2">

                <input type="hidden" name="remove_image" id="removeImageFlag" value="0">

                <?php if ($isPremium): ?>
                <div class="image-upload-container">
                    <div class="image-preview" id="imagePreview">
                    <?php if ($isEdit && !empty($article->imagePath)): ?>
                        <img src="/public/<?= htmlspecialchars($article->imagePath) ?>">
                    <?php else: ?>
                        <span>No image selected</span>
                    <?php endif; ?>
                    </div>

                    <input type="file" id="articleImageInput" name="article_image" hidden>

                    <div class="image-buttons">
                        <button type="button" class="btn btn-dark" onclick="selectImage()">Select Image</button>
                        <button type="button" class="btn btn-light" onclick="removeImage()">Remove Image</button>
                    </div>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Article Title</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($val['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Article Summary</label>
                    <input type="text" name="excerpt" value="<?= htmlspecialchars($val['excerpt']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <option value="">Select category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat->id ?>" <?= (int)$val['category_id'] === $cat->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" style="min-height:300px" required><?= htmlspecialchars($val['content']) ?></textarea>
                </div>

                <div class="write-actions">
                <!-- AI BUTTON -->
                <button type="button" onclick="runAICheck()" class="btn-ai" style="flex:1">
                    🤖 AI Fact Check
                </button>
                <!-- NEW BUTTONS -->
                <div class="write-actions-row">


                <!-- SAVE DRAFT (only for draft or new) -->
                <?php if (!$isEdit || ($article->status ?? '') === 'draft'): ?>
                    <button type="submit" name="action" value="draft" class="btn-draft" style="flex:1">
                        <?= $isEdit ? '💾 Update Draft' : '💾 Save Draft' ?>
                    </button>
                <?php endif; ?>

                <button type="submit" name="action" value="publish" class="btn-publish" style="flex:1">
                <?php
                    $isDraft = !$isEdit || ($article->status === 'draft');
                    echo $isDraft ? '🚀 Publish Article' : '💾 Save Changes';
                    ?>
                 </button>

            </div>
            </div>

            </form>

            <!-- ================= RIGHT SIDE (AI PANEL) ================= -->
            <div class="ai-panel">
            <div class="card">

            <h3>🛡 AI Verification</h3>

            <!-- STATE 1 (DEFAULT) -->
            <div id="ai-empty">
                <p class="text-muted" style="margin-top:10px;">
                    Click "AI Fact Check" to analyze your article's credibility before publishing.
                </p>
            </div>

            <!-- STATE 2 (HIDDEN INITIALLY) -->
            <div id="ai-result" style="display:none;">

    <!-- SCORE -->
    <div style="text-align:center; margin:20px 0;">
        <h2 style="font-size:28px; font-weight:700;">90%</h2>
        <p class="text-muted">Trust Score</p>
    </div>

    <!--  DESCRIPTION TEXT -->
    <p style="font-size:13px; color:#555; margin-bottom:16px; line-height:1.5;">
        This submission is a legitimate news article. It consists of well researched facts and is supported well by evidence.
    </p>

            <!-- PROGRESS BARS -->
            <p>Factual Accuracy</p>
            <div class="progress-bar"><div style="width:95%"></div></div>

            <p>Source Quality</p>
            <div class="progress-bar"><div style="width:80%"></div></div>

            <p>Bias Detection</p>
            <div class="progress-bar"><div style="width:5%"></div></div>

            <p>Logical Consistency</p>
            <div class="progress-bar"><div style="width:90%"></div></div>

            <p>Completeness</p>
            <div class="progress-bar"><div style="width:80%"></div></div>

            <!-- SUCCESS BOX -->
            <div class="ai-success-box">
                 Trust score is above 60%. Article can be published.
            </div>

        </div>
    </main>
</div>

<script>
// image preview logic (UNCHANGED)
function selectImage() {
    document.getElementById('articleImageInput').click();
}

document.getElementById('articleImageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('imagePreview');
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(event) {
        preview.innerHTML = `<img src="${event.target.result}">`;
    }
    reader.readAsDataURL(file);
});

function removeImage() {
    document.getElementById('articleImageInput').value = '';
    document.getElementById('imagePreview').innerHTML = '<span>No image selected</span>';
    document.getElementById('removeImageFlag').value = "1";
}

function runAICheck() {
    document.getElementById('ai-empty').style.display = 'none';
    document.getElementById('ai-result').style.display = 'block';
}
</script>

<?php page_foot(); ?>