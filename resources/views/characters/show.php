<?php

use function App\Support\e;

$character = $details->character;
?>
<a class="back-link" href="/characters">&larr; Back to characters</a>

<article class="detail-card">
    <div class="detail-image">
        <img src="<?= e($character->image) ?>" alt="<?= e($character->name) ?>">
    </div>

    <div class="detail-content">
        <span class="status-pill <?= e($character->statusClass()) ?>"><?= e($character->status) ?></span>
        <h1><?= e($character->name) ?></h1>

        <dl class="facts">
            <div>
                <dt>Species</dt>
                <dd><?= e($character->species) ?></dd>
            </div>
            <?php if ($character->type !== ''): ?>
                <div>
                    <dt>Type</dt>
                    <dd><?= e($character->type) ?></dd>
                </div>
            <?php endif; ?>
            <div>
                <dt>Gender</dt>
                <dd><?= e($character->gender) ?></dd>
            </div>
            <div>
                <dt>Origin</dt>
                <dd><?= e($character->originName) ?></dd>
            </div>
            <div>
                <dt>Last known location</dt>
                <dd><?= e($character->locationName) ?></dd>
            </div>
        </dl>
    </div>
</article>

<section class="episodes">
    <div class="section-heading">
        <p class="eyebrow">Episodes</p>
        <h2>Appears in <?= e(count($details->episodes)) ?> episode<?= count($details->episodes) === 1 ? '' : 's' ?></h2>
    </div>

    <?php if ($details->episodes === []): ?>
        <p class="notice">No episode information was available for this character.</p>
    <?php else: ?>
        <ol class="episode-list">
            <?php foreach ($details->episodes as $episode): ?>
                <li>
                    <span><?= e($episode->code) ?></span>
                    <strong><?= e($episode->name) ?></strong>
                    <small><?= e($episode->airDate) ?></small>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</section>
