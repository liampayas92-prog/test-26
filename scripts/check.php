<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$targets = [
    $root . DIRECTORY_SEPARATOR . 'public',
    $root . DIRECTORY_SEPARATOR . 'resources',
    $root . DIRECTORY_SEPARATOR . 'src',
];

$files = [];

foreach ($targets as $target) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($target));

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
}

sort($files);

foreach ($files as $file) {
    $command = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file);
    $output = [];
    exec($command, $output, $exitCode);

    if ($exitCode !== 0) {
        echo implode(PHP_EOL, $output) . PHP_EOL;
        exit($exitCode);
    }
}

echo 'PHP syntax check passed for ' . count($files) . ' files.' . PHP_EOL;
