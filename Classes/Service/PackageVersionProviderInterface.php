<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Service;

interface PackageVersionProviderInterface
{
    /** @return array<string, mixed>|null */
    public function findLatestVersion(string $packageName): ?array;
}
