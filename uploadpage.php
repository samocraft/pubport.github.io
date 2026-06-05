<?php

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.html');
    exit;
}

require_once __DIR__ . '/includes/ProjectCategory.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$flash = $_SESSION['upload_flash'] ?? null;
unset($_SESSION['upload_flash']);

$categoryIcons = [
    'web/mobile app' => 'phone-portrait-outline',
    'infographics' => 'bar-chart-outline',
    'video editing' => 'film-outline',
    'motion graphics' => 'color-wand-outline',
    '3d art' => 'cube-outline',
    '3d animations/vfx' => 'sparkles-outline',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Project — Portfolio Admin</title>
    <link rel="shortcut icon" href="./assets/images/Screenshot 2024-07-15 185357.png" type="image/x-icon">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/upload.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>

<main>
    <div class="main-content">
        <nav class="navbar">
            <ul class="navbar-list">
                <li class="navbar-item">
                    <span class="navbar-link active">Add project</span>
                </li>
            </ul>
        </nav>

        <article class="about active" data-page="about">
            <header>
                <h2 class="articletitlenext article-title">Publish work</h2>
            </header>

            <section class="upload-page">
                <?php if (is_array($flash)): ?>
                <div class="upload-flash upload-flash--<?php echo htmlspecialchars((string) $flash['type'], ENT_QUOTES, 'UTF-8'); ?>" role="alert">
                    <ion-icon name="<?php echo ($flash['type'] ?? '') === 'success' ? 'checkmark-circle' : 'alert-circle'; ?>-outline"></ion-icon>
                    <span><?php echo htmlspecialchars((string) ($flash['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <?php endif; ?>

                <div class="upload-stepper" aria-label="Upload progress">
                    <div class="upload-step is-active" data-upload-step>
                        <span class="upload-step__dot">1</span>
                        <span class="upload-step__label">Details</span>
                    </div>
                    <div class="upload-step" data-upload-step>
                        <span class="upload-step__dot">2</span>
                        <span class="upload-step__label">Thumbnail</span>
                    </div>
                    <div class="upload-step" data-upload-step>
                        <span class="upload-step__dot">3</span>
                        <span class="upload-step__label">Gallery</span>
                    </div>
                    <div class="upload-step" data-upload-step>
                        <span class="upload-step__dot">4</span>
                        <span class="upload-step__label">Review</span>
                    </div>
                </div>

                <form id="upload-project-form" class="upload-form form" action="uploadold.php" method="post" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                    <!-- Step 1: Details -->
                    <div class="upload-panel is-active" data-upload-panel>
                        <h3 class="upload-panel__title">Project details</h3>
                        <p class="upload-panel__hint">Title and category appear on your portfolio grid and post view.</p>

                        <div class="upload-field">
                            <label for="title">Title <span aria-hidden="true">*</span></label>
                            <input type="text" id="title" name="title" class="form-input" placeholder="Project name" required maxlength="200" autocomplete="off">
                        </div>

                        <div class="upload-field">
                            <label for="subtitle">Subtitle</label>
                            <input type="text" id="subtitle" name="subtitle" class="form-input" placeholder="Short tagline (optional)" maxlength="300" autocomplete="off">
                        </div>

                        <div class="upload-field">
                            <p class="upload-field__legend" id="category-label">Category <span aria-hidden="true">*</span></p>
                            <div class="upload-categories" role="radiogroup" aria-labelledby="category-label">
                                <?php foreach (ProjectCategory::all() as $category): ?>
                                <?php $icon = $categoryIcons[$category->value] ?? 'folder-outline'; ?>
                                <label class="upload-category">
                                    <input type="radio" name="category" value="<?php echo htmlspecialchars($category->value, ENT_QUOTES, 'UTF-8'); ?>" required>
                                    <span class="upload-category__card">
                                        <ion-icon name="<?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?>"></ion-icon>
                                        <?php echo htmlspecialchars($category->label(), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Thumbnail -->
                    <div class="upload-panel" data-upload-panel>
                        <h3 class="upload-panel__title">Thumbnail</h3>
                        <p class="upload-panel__hint">Square or landscape cover image for the portfolio grid. Max 5MB — JPG, PNG, GIF, or WebP.</p>

                        <div class="upload-drop upload-drop--thumb">
                            <input type="file" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png,image/gif,image/webp" required>
                            <ion-icon class="upload-drop__icon" name="image-outline"></ion-icon>
                            <p class="upload-drop__text">Drop thumbnail here</p>
                            <p class="upload-drop__sub">or tap to browse</p>
                        </div>
                        <div class="upload-thumb-preview" data-thumb-preview aria-live="polite"></div>
                    </div>

                    <!-- Step 3: Gallery -->
                    <div class="upload-panel" data-upload-panel>
                        <h3 class="upload-panel__title">Gallery media</h3>
                        <p class="upload-panel__hint">Add at least one image or video. You can include both. Images up to 10MB, videos up to 100MB.</p>

                        <div class="upload-field">
                            <label>Images</label>
                            <div class="upload-drop">
                                <input type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple>
                                <ion-icon class="upload-drop__icon" name="images-outline"></ion-icon>
                                <p class="upload-drop__text">Add images</p>
                                <p class="upload-drop__sub">Multiple files allowed</p>
                            </div>
                            <div class="upload-previews" data-images-preview></div>
                        </div>

                        <div class="upload-field">
                            <label>Videos</label>
                            <div class="upload-drop">
                                <input type="file" id="videos" name="videos[]" accept="video/mp4,video/webm,video/quicktime,video/x-msvideo,video/x-matroska" multiple>
                                <ion-icon class="upload-drop__icon" name="videocam-outline"></ion-icon>
                                <p class="upload-drop__text">Add videos</p>
                                <p class="upload-drop__sub">MP4, MOV, WebM, AVI, MKV</p>
                            </div>
                            <div class="upload-previews" data-videos-preview></div>
                        </div>
                    </div>

                    <!-- Step 4: Review -->
                    <div class="upload-panel" data-upload-panel>
                        <h3 class="upload-panel__title">Review &amp; publish</h3>
                        <p class="upload-panel__hint">Confirm everything looks right before publishing to the portfolio.</p>

                        <div class="upload-review">
                            <dl>
                                <div>
                                    <dt>Title</dt>
                                    <dd data-review-title>—</dd>
                                </div>
                                <div>
                                    <dt>Subtitle</dt>
                                    <dd data-review-subtitle>—</dd>
                                </div>
                                <div>
                                    <dt>Category</dt>
                                    <dd data-review-category>—</dd>
                                </div>
                                <div>
                                    <dt>Thumbnail</dt>
                                    <dd data-review-thumb>—</dd>
                                </div>
                                <div>
                                    <dt>Gallery</dt>
                                    <dd data-review-media>—</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="upload-actions">
                        <button type="button" class="form-btn form-btn--ghost" data-upload-prev hidden>Back</button>
                        <div class="upload-actions__group">
                            <button type="button" class="form-btn" data-upload-next>Continue</button>
                            <button type="submit" class="form-btn" data-upload-submit hidden>
                                <ion-icon name="cloud-upload-outline"></ion-icon>
                                <span>Publish project</span>
                            </button>
                        </div>
                    </div>
                </form>

                <div class="upload-toolbar">
                    <a href="index.php?page=portfolio" class="form-btn form-btn--ghost">
                        <ion-icon name="grid-outline"></ion-icon>
                        <span>View portfolio</span>
                    </a>
                    <a href="index.php" class="form-btn form-btn--ghost">
                        <ion-icon name="home-outline"></ion-icon>
                        <span>Home</span>
                    </a>
                    <form action="logout.php" method="post" style="display:inline">
                        <button type="submit" class="form-btn form-btn--ghost">
                            <ion-icon name="exit-outline"></ion-icon>
                            <span>Log out</span>
                        </button>
                    </form>
                </div>
            </section>
        </article>
    </div>
</main>

<script src="./assets/js/upload.js"></script>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>
