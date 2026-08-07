<?php

declare(strict_types=1);

use Symfony\Component\Filesystem\Filesystem;

require dirname(__DIR__) . '/.Build/vendor/autoload.php';

$root = dirname(__DIR__) . '/.Build/public/typo3temp/var';
$directory = $root . '/tests';
$filesystem = new Filesystem();
if (DIRECTORY_SEPARATOR === '\\' && is_dir($directory)) {
    // Junctions created by the Windows fallback cannot reliably be traversed
    // by Symfony Filesystem. An atomic rename gives the test runner a clean
    // location without following or deleting junction targets.
    rename($directory, $root . '/tests-stale-' . bin2hex(random_bytes(4)));
} else {
    $filesystem->remove($directory);
}
$filesystem->mkdir($directory);
