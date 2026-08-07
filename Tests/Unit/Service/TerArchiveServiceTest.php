<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\Service\TerArchiveService;

final class TerArchiveServiceTest extends TestCase
{
    public function testRawAndCompressedArchivesAreExtracted(): void
    {
        $data = ['FILES' => ['ext_localconf.php' => ['content' => '<?php']]];
        $serializedData = serialize($data);
        $service = new TerArchiveService();

        self::assertSame($data, $service->extract(md5($serializedData) . ':raw:' . $serializedData));
        self::assertSame($data, $service->extract(md5($serializedData) . ':gzcompress:' . gzcompress($serializedData)));
    }

    public function testInvalidChecksumIsRejected(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('MD5 mismatch');

        (new TerArchiveService())->extract('invalid:raw:content');
    }

    public function testNonArrayPayloadIsRejected(): void
    {
        $serializedData = serialize('not an array');
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('could not be safely unserialized to an array');

        (new TerArchiveService())->extract(md5($serializedData) . ':raw:' . $serializedData);
    }

    public function testObjectPayloadIsRejected(): void
    {
        $serializedData = serialize(['object' => new \stdClass()]);
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('could not be safely unserialized to an array');

        (new TerArchiveService())->extract(md5($serializedData) . ':raw:' . $serializedData);
    }
}
