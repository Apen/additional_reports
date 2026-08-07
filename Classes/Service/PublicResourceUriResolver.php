<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

final class PublicResourceUriResolver
{
    private const FACTORY_CLASS = 'TYPO3\\CMS\\Core\\SystemResource\\SystemResourceFactory';
    private const PUBLISHER_INTERFACE = 'TYPO3\\CMS\\Core\\SystemResource\\Publishing\\SystemResourcePublisherInterface';
    private const OPTIONS_CLASS = 'TYPO3\\CMS\\Core\\SystemResource\\Publishing\\UriGenerationOptions';

    public function resolve(string $identifier, ?ServerRequestInterface $request = null): string
    {
        if (
            ! class_exists(self::FACTORY_CLASS)
            || ! interface_exists(self::PUBLISHER_INTERFACE)
            || ! class_exists(self::OPTIONS_CLASS)
        ) {
            return PathUtility::getPublicResourceWebPath($identifier);
        }

        $factory = GeneralUtility::makeInstance(self::FACTORY_CLASS);
        if (! method_exists($factory, 'createPublicResource')) {
            return PathUtility::getPublicResourceWebPath($identifier);
        }
        $resource = call_user_func([$factory, 'createPublicResource'], $identifier);
        $publisher = GeneralUtility::makeInstance(self::PUBLISHER_INTERFACE);
        if (! method_exists($publisher, 'generateUri')) {
            return PathUtility::getPublicResourceWebPath($identifier);
        }
        $options = (new \ReflectionClass(self::OPTIONS_CLASS))->newInstanceArgs(['absoluteUri' => false]);

        return (string) call_user_func([$publisher, 'generateUri'], $resource, $request, $options);
    }
}
