<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'connection.php';

if (!isset($_GET['id'])) {
    echo '<p class="ig-post__empty">No project ID specified.</p>';
    exit;
}

$id = (int) $_GET['id'];

$stmt = $pdo->prepare('SELECT * FROM projects WHERE id = :id');
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    echo '<p class="ig-post__empty">Project not found.</p>';
    exit;
}

$mediaStmt = $pdo->prepare(
    'SELECT media_file, media_type, position FROM media WHERE project_id = :project_id ORDER BY position ASC'
);
$mediaStmt->bindParam(':project_id', $id, PDO::PARAM_INT);
$mediaStmt->execute();
$mediaItems = $mediaStmt->fetchAll(PDO::FETCH_ASSOC);

$similarStmt = $pdo->prepare(
    'SELECT * FROM projects WHERE category = :category AND id != :id LIMIT 3'
);
$similarStmt->bindParam(':category', $project['category'], PDO::PARAM_STR);
$similarStmt->bindParam(':id', $id, PDO::PARAM_INT);
$similarStmt->execute();
$similarProjects = $similarStmt->fetchAll(PDO::FETCH_ASSOC);

function safe_escape($value) {
    return $value !== null ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : '';
}

function ig_hashtag($category) {
    $slug = preg_replace('/[^a-z0-9]+/i', '', strtolower($category ?? ''));
    return $slug ? '#' . $slug : '';
}

$username = 'oussama.seddiki';
$avatar = './assets/images/my-avatar.png';
$slideCount = count($mediaItems);
$likeBase = max(12, ($id * 47) % 900 + 100);
$categoryTag = ig_hashtag($project['category']);
$instaUrl = 'https://www.instagram.com/oussama.seddiki1/';
?>

<link rel="stylesheet" href="./assets/css/ig-post-overlay.css">

<article class="ig-post" tabindex="0" aria-label="Post by <?php echo safe_escape($username); ?>">

    <section class="ig-post__media" aria-label="Post media" data-ig-swipe>
        <div class="ig-post__media-top">
            <a class="ig-post__media-profile" href="<?php echo safe_escape($instaUrl); ?>" target="_blank" rel="noopener noreferrer">
                <img src="<?php echo safe_escape($avatar); ?>" alt="">
                <span><?php echo safe_escape($username); ?></span>
            </a>
        </div>
        <div class="ig-post__carousel">
            <?php if ($slideCount > 0): ?>
                <?php foreach ($mediaItems as $i => $media): ?>
                <?php
                $ext = strtolower(pathinfo($media['media_file'], PATHINFO_EXTENSION));
                $isVideo = in_array($ext, ['mp4', 'avi', 'mkv', 'mov', 'webm'], true);
                ?>
                <div class="ig-post__slide<?php echo $i === 0 ? ' is-active' : ''; ?>">
                    <?php if ($isVideo): ?>
                    <video controls playsinline preload="metadata">
                        <source src="<?php echo safe_escape($media['media_file']); ?>" type="video/<?php echo safe_escape($ext); ?>">
                    </video>
                    <?php else: ?>
                    <img src="<?php echo safe_escape($media['media_file']); ?>" alt="<?php echo safe_escape($project['title']); ?>">
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <?php if ($slideCount > 1): ?>
                <button type="button" class="ig-post__nav ig-post__nav--prev" aria-label="Previous">
                    <ion-icon name="chevron-back"></ion-icon>
                </button>
                <button type="button" class="ig-post__nav ig-post__nav--next" aria-label="Next">
                    <ion-icon name="chevron-forward"></ion-icon>
                </button>
                <div class="ig-post__dots" role="tablist" aria-label="Carousel pagination">
                    <?php for ($d = 0; $d < $slideCount; $d++): ?>
                    <button type="button" class="ig-post__dot<?php echo $d === 0 ? ' is-active' : ''; ?>" aria-label="Go to slide <?php echo $d + 1; ?>"></button>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="ig-post__slide is-active">
                    <img src="<?php echo safe_escape($project['thumbnail']); ?>" alt="<?php echo safe_escape($project['title']); ?>">
                </div>
            <?php endif; ?>
        </div>
    </section>

    <aside class="ig-post__panel">
        <div class="ig-post__sheet-head">
            <button type="button" class="ig-post__sheet-toggle" aria-expanded="false" aria-controls="ig-post-sheet-content">
                <span class="ig-post__sheet-handle" aria-hidden="true"></span>
                <span class="ig-post__sheet-peek">
                    <strong><?php echo safe_escape($project['title']); ?></strong>
                    <?php if (!empty($project['subtitle'])): ?>
                    <span class="ig-post__sheet-peek-sub"><?php echo safe_escape($project['subtitle']); ?></span>
                    <?php endif; ?>
                </span>
                <ion-icon name="chevron-up-outline" class="ig-post__sheet-chevron" aria-hidden="true"></ion-icon>
            </button>
        </div>

        <div class="ig-post__sheet-content" id="ig-post-sheet-content">
        <header class="ig-post__header">
            <a class="ig-post__profile" href="<?php echo safe_escape($instaUrl); ?>" target="_blank" rel="noopener noreferrer">
                <img class="ig-post__avatar" src="<?php echo safe_escape($avatar); ?>" alt="">
                <span class="ig-post__username"><?php echo safe_escape($username); ?></span>
            </a>
            <button type="button" class="ig-post__menu" aria-label="More options">•••</button>
        </header>

        <div class="ig-post__thread">
            <div class="ig-post__comment">
                <img class="ig-post__comment-avatar" src="<?php echo safe_escape($avatar); ?>" alt="">
                <div class="ig-post__comment-body">
                    <a class="ig-post__comment-user" href="<?php echo safe_escape($instaUrl); ?>" target="_blank" rel="noopener noreferrer"><?php echo safe_escape($username); ?></a>
                    <?php echo safe_escape($project['title']); ?>
                    <?php if (!empty($project['subtitle'])): ?>
                    <br><?php echo safe_escape($project['subtitle']); ?>
                    <?php endif; ?>
                    <?php if ($categoryTag): ?>
                    <span class="ig-post__hashtag"> <?php echo safe_escape($categoryTag); ?></span>
                    <?php endif; ?>
                    <span class="ig-post__comment-meta">
                        <time datetime="PT1H">1h</time>
                        <button type="button">Reply</button>
                    </span>
                </div>
            </div>

            <?php if (!empty($similarProjects)): ?>
            <p class="ig-post__more-label">More posts</p>
            <ul class="ig-post__more-grid">
                <?php foreach ($similarProjects as $similar): ?>
                <li>
                    <a href="#" class="open-overlay" data-project-id="<?php echo (int) $similar['id']; ?>" aria-label="<?php echo safe_escape($similar['title']); ?>">
                        <div class="ig-post__thumb" style="background-image:url('<?php echo safe_escape($similar['thumbnail']); ?>')"></div>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
        </div><!-- .ig-post__sheet-content -->

        <footer class="ig-post__footer">
            <div class="ig-post__actions">
                <div class="ig-post__actions-left">
                    <button type="button" class="ig-post__action" data-ig-like aria-label="Like">
                        <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16.792 3.904A4.989 4.989 0 0 0 12 2.05a4.988 4.988 0 0 0-4.792 3.854A8.978 8.978 0 0 0 2.05 12c0 5.523 4.477 10 10 10s10-4.477 10-10a8.978 8.978 0 0 0-4.158-8.096z"/></svg>
                    </button>
                    <button type="button" class="ig-post__action" aria-label="Comment">
                        <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.656 17.008a9.993 9.993 0 1 0-3.59 3.615L22 22z"/></svg>
                    </button>
                    <button type="button" class="ig-post__action" aria-label="Share">
                        <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
                <button type="button" class="ig-post__action" aria-label="Save">
                    <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="20 21 12 15 4 21 4 3 20 3 20 21"/></svg>
                </button>
            </div>

            <p class="ig-post__likes" data-ig-like-count data-base="<?php echo (int) $likeBase; ?>">
                <?php echo number_format($likeBase); ?> likes
            </p>

            <p class="ig-post__caption-preview">
                <a class="ig-post__comment-user" href="<?php echo safe_escape($instaUrl); ?>" target="_blank" rel="noopener noreferrer"><?php echo safe_escape($username); ?></a>
                <?php echo safe_escape($project['title']); ?>
                <?php if (!empty($project['subtitle'])): ?>
                <span class="ig-post__caption-muted"> — <?php echo safe_escape($project['subtitle']); ?></span>
                <?php endif; ?>
            </p>

            <time class="ig-post__time" datetime="PT1H">1 hour ago</time>

            <form class="ig-post__composer" action="#" onsubmit="return false;">
                <input type="text" placeholder="Add a comment…" aria-label="Add a comment" autocomplete="off">
                <button type="submit" class="ig-post__composer-post" disabled>Post</button>
            </form>
        </footer>
    </aside>
</article>
