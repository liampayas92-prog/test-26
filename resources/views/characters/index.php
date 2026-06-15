<?php

use function App\Support\e;

$urlForPage = static fn (int $pageNumber): string => '/characters?' . http_build_query(
    $criteria->toUrlQuery($pageNumber),
    '',
    '&',
    PHP_QUERY_RFC3986,
);
?>
<section class="hero">
    <div>
        <p class="eyebrow">Character Listing</p>
        <h1>Explore the multiverse</h1>
        <p class="lede">Search by name and narrow the list by status, species, or gender. Results are loaded from the public Rick and Morty API.</p>
    </div>
</section>

<form class="filters" method="get" action="/characters">
    <label>
        <span>Search</span>
        <input type="search" name="q" value="<?= e($criteria->name) ?>" placeholder="Rick, Morty, Summer...">
    </label>

    <label>
        <span>Status</span>
        <select name="status">
            <?php foreach ($statuses as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= $criteria->status === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <label>
        <span>Species</span>
        <input type="text" name="species" value="<?= e($criteria->species) ?>" placeholder="Human, Alien...">
    </label>

    <label>
        <span>Gender</span>
        <select name="gender">
            <?php foreach ($genders as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= $criteria->gender === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <div class="filter-actions">
        <button type="submit">Apply filters</button>
        <a href="/characters">Clear</a>
    </div>
</form>

<?php if ($error !== null): ?>
    <div class="notice notice-error"><?= e($error) ?></div>
<?php elseif ($page !== null && $page->characters === []): ?>
    <div class="notice">
        <strong>No characters found.</strong>
        <span>Try removing a filter or searching for a different name.</span>
    </div>
<?php elseif ($page !== null): ?>
    <div class="results-summary">
        <p><?= e($page->totalCount) ?> character<?= $page->totalCount === 1 ? '' : 's' ?> found</p>
        <p>Page <?= e($page->currentPage) ?> of <?= e($page->totalPages) ?></p>
    </div>

    <section class="character-grid" aria-label="Character results">
        <?php foreach ($page->characters as $character): ?>
            <article class="character-card">
                <a href="/characters/<?= e($character->id) ?>">
                    <img src="<?= e($character->image) ?>" alt="<?= e($character->name) ?>" loading="lazy">
                    <div class="card-body">
                        <span class="status-pill <?= e($character->statusClass()) ?>"><?= e($character->status) ?></span>
                        <h2><?= e($character->name) ?></h2>
                        <p><?= e($character->species) ?><?= $character->type !== '' ? ' - ' . e($character->type) : '' ?></p>
                        <p class="muted">Origin: <?= e($character->originName) ?></p>
                    </div>
                </a>
            </article>
        <?php endforeach; ?>
    </section>

    <?php if ($page->totalPages > 1): ?>
        <nav class="pagination" aria-label="Pagination">
            <?php if ($page->hasPreviousPage()): ?>
                <a href="<?= e($urlForPage($page->currentPage - 1)) ?>">Previous</a>
            <?php else: ?>
                <span>Previous</span>
            <?php endif; ?>

            <div class="pagination-pages">
                <?php foreach ($page->visiblePageNumbers() as $pageNumber): ?>
                    <?php if (is_int($pageNumber)): ?>
                        <?php if ($pageNumber === $page->currentPage): ?>
                            <span class="pagination-current" aria-current="page"><?= e($pageNumber) ?></span>
                        <?php else: ?>
                            <a href="<?= e($urlForPage($pageNumber)) ?>"><?= e($pageNumber) ?></a>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="pagination-ellipsis" aria-hidden="true">&hellip;</span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <?php if ($page->hasNextPage()): ?>
                <a href="<?= e($urlForPage($page->currentPage + 1)) ?>">Next</a>
            <?php else: ?>
                <span>Next</span>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>
