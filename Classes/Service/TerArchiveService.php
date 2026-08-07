<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

final class TerArchiveService
{
    /** @return array<string, mixed>|string */
    public function download(string $extensionKey, string $version, ?string $file = null): array|string
    {
        $url = sprintf(
            'https://typo3.org/fileadmin/ter/%s/%s/%s_%s.t3x',
            strtolower(substr($extensionKey, 0, 1)),
            strtolower(substr($extensionKey, 1, 1)),
            $extensionKey,
            trim($version),
        );
        $content = GeneralUtility::getURL($url);
        if (! is_string($content)) {
            throw new \RuntimeException('The extension archive could not be downloaded.');
        }
        $archive = $this->extract($content);
        if ($file === null || $file === '') {
            return $archive;
        }
        $fileContent = $archive['FILES'][$file]['content'] ?? null;
        if (! is_string($fileContent)) {
            throw new \UnexpectedValueException('The requested file does not exist in the extension archive.');
        }
        return $fileContent;
    }

    /** @return array<string, mixed> */
    public function extract(string $content): array
    {
        $parts = explode(':', $content, 3);
        if (($parts[1] ?? '') === 'gzcompress') {
            $uncompressedContent = gzuncompress($parts[2] ?? '');
            if (! is_string($uncompressedContent)) {
                throw new \RuntimeException('Decoding Error: The compressed extension payload is invalid.');
            }
            $parts[2] = $uncompressedContent;
        }
        $serializedContent = $parts[2] ?? '';
        if (! isset($parts[0]) || ! hash_equals($parts[0], md5($serializedContent))) {
            throw new \UnexpectedValueException('Error: MD5 mismatch. Maybe the extension file was downloaded and saved as a text file by the browser and thereby corrupted!? (Always select "All" filetype when saving extensions)');
        }
        $archive = unserialize($serializedContent, ['allowed_classes' => false]);
        if (! is_array($archive) || $this->containsObject($archive)) {
            throw new \UnexpectedValueException('Error: Content could not be safely unserialized to an array.');
        }
        return $archive;
    }

    /** @param array<mixed> $values */
    private function containsObject(array $values): bool
    {
        foreach ($values as $value) {
            if (is_object($value) || (is_array($value) && $this->containsObject($value))) {
                return true;
            }
        }
        return false;
    }
}
