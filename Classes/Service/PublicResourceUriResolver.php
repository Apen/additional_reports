<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\SystemResource\Publishing\SystemResourcePublisherInterface;
use TYPO3\CMS\Core\SystemResource\Publishing\UriGenerationOptions;
use TYPO3\CMS\Core\SystemResource\SystemResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

final class PublicResourceUriResolver
{
    public function resolve(string $identifier, ?ServerRequestInterface $serverRequest = null): string
    {
        if (
            ! class_exists(SystemResourceFactory::class)
            || ! interface_exists(SystemResourcePublisherInterface::class)
            || ! class_exists(UriGenerationOptions::class)
        ) {
            return PathUtility::getPublicResourceWebPath($identifier);
        }

        $systemResourceFactory = GeneralUtility::makeInstance(SystemResourceFactory::class);
        if (! method_exists($systemResourceFactory, 'createPublicResource')) {
            return PathUtility::getPublicResourceWebPath($identifier);
        }

        $publicResource = $systemResourceFactory->createPublicResource($identifier);
        $systemResourcePublisher = GeneralUtility::makeInstance(SystemResourcePublisherInterface::class);
        if (! method_exists($systemResourcePublisher, 'generateUri')) {
            return PathUtility::getPublicResourceWebPath($identifier);
        }

        $uriGenerationOptions = (new \ReflectionClass(UriGenerationOptions::class))->newInstanceArgs(['absoluteUri' => false]);

        return (string) $systemResourcePublisher->generateUri($publicResource, $serverRequest, $uriGenerationOptions);
    }
}
