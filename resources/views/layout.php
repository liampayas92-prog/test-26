<?php

use function App\Support\e;

$pageTitle = isset($title) ? $title . ' | Rick and Morty Encyclopedia' : 'Rick and Morty Encyclopedia';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="/">Rick and Morty Encyclopedia</a>
        <p>Browse characters, filter the archive, and inspect episode appearances.</p>
    </header>

    <main class="page-shell">
        <?= $content ?>
    </main>
</body>
</html>
