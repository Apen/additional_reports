<?php

declare(strict_types=1);

use Symfony\Component\Filesystem\Filesystem;

require dirname(__DIR__) . '/.Build/vendor/autoload.php';

$directory = dirname(__DIR__) . '/.Build/coverage/additional_reports';
$filesystem = new Filesystem();
$filesystem->remove($directory);
$filesystem->mkdir($directory);
