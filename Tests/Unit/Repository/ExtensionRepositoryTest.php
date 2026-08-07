<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Repository;

use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\Repository\ExtensionRepository;

final class ExtensionRepositoryTest extends TestCase
{
    public function testEmptyExtensionKeyIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new ExtensionRepository())->findVersion('');
    }

}
