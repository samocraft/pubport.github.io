<?php

declare(strict_types=1);

require_once __DIR__ . '/connection.php';

$all_data = [];

try {
    $stmt = $pdo->prepare('SELECT * FROM projects ORDER BY id DESC');
    $stmt->execute();
    $all_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $portfolio_error = 'Could not load projects. Please try again later.';
}

$project_count = count($all_data);

$categories = [
    'all' => ['label' => 'All work', 'icon' => 'grid-outline'],
    'web/mobile app' => ['label' => 'Apps', 'icon' => 'phone-portrait-outline'],
    'infographics' => ['label' => 'Infographics', 'icon' => 'bar-chart-outline'],
    'video editing' => ['label' => 'Video', 'icon' => 'film-outline'],
    'motion graphics' => ['label' => 'Motion', 'icon' => 'color-wand-outline'],
    '3d art' => ['label' => '3D Art', 'icon' => 'cube-outline'],
    '3d animations/vfx' => ['label' => 'VFX', 'icon' => 'sparkles-outline'],
];

function pf_category_label(string $slug, array $categories): string
{
    return $categories[$slug]['label'] ?? ucwords(str_replace('/', ' ', $slug));
}
?>

<link rel="stylesheet" href="./assets/css/portfolio.css">

<section class="portfolio-wrap projects" aria-label="Portfolio gallery">

    <header class="portfolio-hero">
        <div class="portfolio-hero__copy">
            <p class="portfolio-hero__eyebrow">Creative direction</p>
            <h2 class="portfolio-hero__title article-title">Portfolio</h2>
            <p class="portfolio-hero__desc">
                A curated selection of design, motion, and visual work. Filter by discipline or open any piece for the full story.
            </p>
        </div>
        <div class="portfolio-hero__stat" aria-label="<?php echo (int) $project_count; ?> projects">
            <strong data-portfolio-count><?php echo (int) $project_count; ?></strong>
            <span>Projects</span>
        </div>
    </header>

    <nav class="portfolio-filters" aria-label="Filter projects by category">
        <div class="portfolio-filters__scroll" role="tablist">
            <?php foreach ($categories as $slug => $meta): ?>
            <button
                type="button"
                class="portfolio-filter filter-item<?php echo $slug === 'all' ? ' is-active active' : ''; ?>"
                data-filter="<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>"
                role="tab"
                aria-selected="<?php echo $slug === 'all' ? 'true' : 'false'; ?>"
            >
                <ion-icon name="<?php echo htmlspecialchars($meta['icon'], ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></ion-icon>
                <?php echo htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8'); ?>
            </button>
            <?php endforeach; ?>
        </div>

        <div class="portfolio-filters__mobile filter-select-box">
            <div class="filter-select" data-select>
                <span class="select-value">All work</span>
                <ion-icon name="chevron-down" aria-hidden="true"></ion-icon>
            </div>
            <ul class="select-list">
                <?php foreach ($categories as $slug => $meta): ?>
                <li class="select-item">
                    <button type="button" data-filter="<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($meta['label'], ENT_QUOTES, 'UTF-8'); ?>
                    </button>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>

    <?php if (!empty($portfolio_error)): ?>
    <p class="portfolio-error" role="alert">
        <ion-icon name="alert-circle-outline" aria-hidden="true"></ion-icon><br>
        <?php echo htmlspecialchars($portfolio_error, ENT_QUOTES, 'UTF-8'); ?>
    </p>
    <?php else: ?>

    <ul class="project-list portfolio-grid">
        <?php if ($all_data): ?>
            <?php foreach ($all_data as $row): ?>
            <?php
            $catSlug = (string) ($row['category'] ?? '');
            $catLabel = pf_category_label($catSlug, $categories);
            ?>
            <li
                class="project-item active is-visible"
                data-category="<?php echo htmlspecialchars($catSlug, ENT_QUOTES, 'UTF-8'); ?>"
            >
                <?php if (isset($row['id'])): ?>
                <a
                    href="#"
                    class="open-overlay pf-card"
                    data-project-id="<?php echo (int) $row['id']; ?>"
                    aria-label="View <?php echo htmlspecialchars((string) $row['title'], ENT_QUOTES, 'UTF-8'); ?>"
                >
                    <figure class="project-img pf-card__media">
                        <div
                            class="img-wrapper"
                            style="background-image: url('<?php echo htmlspecialchars((string) $row['thumbnail'], ENT_QUOTES, 'UTF-8'); ?>')"
                        ></div>
                        <figcaption class="pf-card__overlay">
                            <span class="pf-card__tag"><?php echo htmlspecialchars($catLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                            <h3 class="project-title pf-card__title"><?php echo htmlspecialchars((string) $row['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <?php if (!empty($row['subtitle'])): ?>
                            <p class="project-category pf-card__sub"><?php echo htmlspecialchars((string) $row['subtitle'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php endif; ?>
                            <span class="pf-card__cta">
                                View project
                                <ion-icon name="arrow-forward-outline" aria-hidden="true"></ion-icon>
                            </span>
                        </figcaption>
                    </figure>
                </a>
                <?php else: ?>
                <p class="portfolio-empty">Incomplete project data.</p>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>

    <p class="portfolio-empty" data-portfolio-empty hidden>
        <ion-icon name="images-outline" aria-hidden="true"></ion-icon><br>
        No projects in this category yet.
    </p>

    <?php if (!$all_data): ?>
    <p class="portfolio-empty is-shown">
        <ion-icon name="folder-open-outline" aria-hidden="true"></ion-icon><br>
        No projects published yet. Check back soon.
    </p>
    <?php endif; ?>

    <?php endif; ?>
</section>

<div class="modalover-container" id="modaloverContainer" role="dialog" aria-modal="true" aria-label="Project details">
    <div class="testimonials-modalover">
        <button type="button" class="modalover-close-btn" id="closemodalover" aria-label="Close">&times;</button>
        <div class="modalover-content"></div>
    </div>
    <div class="overlaypage" id="overlaypage"></div>
</div>
