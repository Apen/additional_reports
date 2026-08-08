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
    private const FACTORY_CLASS = SystemResourceFactory::class;

    private const PUBLISHER_INTERFACE = SystemResourcePublisherInterface::class;

    private const OPTIONS_CLASS = UriGenerationOptions::class;

    public function resolve(string $identifier, ?ServerRequestInterface $serverRequest = null): string
    {
        if (
            ! class_exists(self::FACTORY_CLASS)
            || ! interface_exists(self::PUBLISHER_INTERFACE)
            || ! class_exists(self::OPTIONS_CLASS)
        ) {
            return PathUtility::getPublicResourceWebPath($identifier);
        }

        $self = GeneralUtility::makeInstance(self::FACTORY_CLASS);
        if (! method_exists($self, 'createPublicResource')) {
            return PathUtility::getPublicResourceWebPath($identifier);
        }

        $resource = $self->createPublicResource($identifier);
        $publisher = GeneralUtility::makeInstance(self::PUBLISHER_INTERFACE);
        if (! method_exists($publisher, 'generateUri')) {
            return PathUtility::getPublicResourceWebPath($identifier);
        }

        $uriGenerationOptions = (new \ReflectionClass(self::OPTIONS_CLASS))->newInstanceArgs(['absoluteUri' => false]);

        return (string) $publisher->generateUri($resource, $serverRequest, $uriGenerationOptions);
    }
}
