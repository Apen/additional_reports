<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Package\Exception\UnknownPackageException;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\SystemResource\Publishing\SystemResourcePublisherInterface;
use TYPO3\CMS\Core\SystemResource\Publishing\UriGenerationOptions;
use TYPO3\CMS\Core\SystemResource\SystemResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

final class ExtensionIconResolver
{
    public function resolve(string $extensionKey, ?ServerRequestInterface $request = null): string
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
        if (class_exists(SystemResourceFactory::class)) {
            $resource = GeneralUtility::makeInstance(SystemResourceFactory::class)->createPublicResource($identifier);
            return (string) GeneralUtility::makeInstance(SystemResourcePublisherInterface::class)->generateUri(
                $resource,
                $request,
                new UriGenerationOptions(absoluteUri: false),
            );
        }
        return PathUtility::getPublicResourceWebPath($identifier);
    }
}
