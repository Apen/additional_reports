<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Service;

use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class EidTargetResolver
{
    public function __construct(private ?PackageManager $packageManager = null) {}

    /** @return array{extension: string, target: string} */
    public function resolve(mixed $target): array
    {
        $displayTarget = is_string($target) ? $target : get_debug_type($target);
        if (preg_match('#^EXT:([^/]+)/#', $displayTarget, $matches) === 1) {
            return ['extension' => $matches[1], 'target' => $displayTarget];
        }

        $className = $this->resolveClassName($target);
        return [
            'extension' => $className === null ? '' : $this->resolvePackageKey($className),
            'target' => $displayTarget,
        ];
    }

    private function resolveClassName(mixed $target): ?string
    {
        if (is_string($target)) {
            $className = explode('::', $target, 2)[0];
            return class_exists($className) ? $className : null;
        }

        if (is_array($target) && isset($target[0])) {
            $className = is_object($target[0]) ? $target[0]::class : $target[0];
            return is_string($className) && class_exists($className) ? $className : null;
        }

        return is_object($target) ? $target::class : null;
    }

    private function resolvePackageKey(string $className): string
    {
        $packageManager = $this->packageManager ?? GeneralUtility::makeInstance(PackageManager::class);
        foreach ($packageManager->getAvailablePackages() as $package) {
            $autoload = (array) $package->getValueFromComposerManifest('autoload');
            $namespaces = (array) ($autoload['psr-4'] ?? []);
            foreach (array_keys($namespaces) as $namespace) {
                if (is_string($namespace) && str_starts_with($className, $namespace)) {
                    return $package->getPackageKey();
                }
            }
        }

        try {
            $fileName = (new \ReflectionClass($className))->getFileName();
        } catch (\ReflectionException) {
            return '';
        }

        if (! is_string($fileName)) {
            return '';
        }

        $normalizedFileName = str_replace('\\', '/', $fileName);
        foreach ($packageManager->getAvailablePackages() as $package) {
            $packagePath = rtrim(str_replace('\\', '/', $package->getPackagePath()), '/') . '/';
            if (str_starts_with($normalizedFileName, $packagePath)) {
                return $package->getPackageKey();
            }
        }

        return '';
    }
}
