<?php

declare(strict_types=1);

namespace TYPO3\TestingFramework\Core {
    if (!function_exists(__NAMESPACE__ . '\\symlink')) {
        function symlink(string $target, string $link): bool
        {
            if (DIRECTORY_SEPARATOR !== '\\') {
                return \symlink($target, $link);
            }
            if (@\symlink($target, $link)) {
                return true;
            }

            $quote = static fn (string $path): string => '"' . str_replace('"', '""', str_replace('/', '\\', $path)) . '"';
            $command = is_dir($target)
                ? 'cmd /c mklink /J ' . $quote($link) . ' ' . $quote($target)
                : 'cmd /c mklink ' . $quote($link) . ' ' . $quote($target);
            exec($command, $output, $returnCode);

            return $returnCode === 0;
        }
    }
}

namespace {
    require dirname(__DIR__) . '/.Build/vendor/typo3/testing-framework/Resources/Core/Build/FunctionalTestsBootstrap.php';
}
