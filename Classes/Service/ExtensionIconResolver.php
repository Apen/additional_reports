<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Package\Exception\UnknownPackageException;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class ExtensionIconResolver
{
    public function __construct(private ?PublicResourceUriResolver $publicResourceUriResolver = null) {}

    public function resolve(string $extensionKey, ?ServerRequestInterface $serverRequest = null): string
    {
        if ($extensionKey === '') {
            return '';
        }

        try {
            $package = GeneralUtility::makeInstance(PackageManager::class)->getPackage($extensionKey);
        } catch (UnknownPackageException) {
            return '';
        }

        $icon = $package->getPackageIcon();
        if ($icon === null) {
            return '';
        }

        $identifier = 'EXT:' . $extensionKey . '/' . $icon;
        $resolver = $this->publicResourceUriResolver ?? GeneralUtility::makeInstance(PublicResourceUriResolver::class);
        return $resolver->resolve($identifier, $serverRequest);
    }
}
