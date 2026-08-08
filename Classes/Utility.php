<?php

declare(strict_types=1);

namespace Sng\AdditionalReports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Repository\ContentUsageRepository;
use Sng\AdditionalReports\Repository\ExtensionRepository;
use Sng\AdditionalReports\Repository\PageStatisticsRepository;
use Sng\AdditionalReports\Service\ContentTypeResolver;
use Sng\AdditionalReports\Service\ExtensionIconResolver;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility class
 */
class Utility
{
    /**
     * Define all the reports
     */
    /** @return list<array{string, string}> */
    public static function getReportsList(): array
    {
        return [
            ['Eid', 'eid'],
            ['CommandControllers', 'commandcontrollers'],
            ['Plugins', 'plugins'],
            ['Xclass', 'xclass'],
            ['Hooks', 'hooks'],
            ['Status', 'status'],
            ['LogErrors', 'logerrors'],
            ['WebsiteConf', 'websitesconf'],
            ['Extensions', 'extensions'],
            ['EventDispatcher', 'eventdispatcher'],
            ['Middlewares', 'middlewares'],
        ];
    }

    /**
     * Generates a list of Page-uid's from $id
     */
    public static function getTreeList(int $id, int $depth, int $begin = 0, string $permsClause = '1=1'): string
    {
        if ($begin !== 0 || $permsClause !== '1=1') {
            throw new \InvalidArgumentException('Custom tree offsets and SQL permission clauses are no longer supported.', 3882860459);
        }

        return implode(',', GeneralUtility::makeInstance(PageStatisticsRepository::class)->findPageIdsRecursive($id, $depth));
    }

    /**
     * Count page uids in a list given (validating the condition)
     */
    public static function getCountPagesUids(string $listOfUids, string $field = ''): int
    {
        $pageUids = array_values(array_filter(array_map(intval(...), explode(',', $listOfUids))));
        if ($pageUids === []) {
            return 0;
        }

        if ($field === '') {
            return count($pageUids);
        }

        return GeneralUtility::makeInstance(PageStatisticsRepository::class)->countByFlag($pageUids, $field);
    }

    /**
     * @return array{
     *     ter: array<string, array<string, mixed>>,
     *     dev: array<string, array<string, mixed>>,
     *     unloaded: array<string, array<string, mixed>>
     * }
     */
    public static function getExtensionList(): array
    {
        return GeneralUtility::makeInstance(ExtensionRepository::class)->findGrouped();
    }

    /**
     * Get the HTTP icon path of an extension
     */
    public static function getExtIcon(string $extKey): string
    {
        return GeneralUtility::makeInstance(ExtensionIconResolver::class)->resolve($extKey);
    }

    /**
     * Get the version of a given extension
     */
    public static function getExtensionVersion(mixed $key): ?string
    {
        if (! is_string($key) || $key === '' || $key === '0') {
            throw new \InvalidArgumentException('Extension key must be a non-empty string.', 2138750667);
        }

        return GeneralUtility::makeInstance(ExtensionRepository::class)->findVersion($key);
    }

    /** @return list<array<string, mixed>> */
    public static function getAllDifferentPlugins(bool $displayHidden = false): array
    {
        return GeneralUtility::makeInstance(ContentUsageRepository::class)->findDistinctPlugins($displayHidden);
    }

    /** @return list<array<string, mixed>> */
    public static function getAllDifferentCtypes(bool $displayHidden = false): array
    {
        return GeneralUtility::makeInstance(ContentUsageRepository::class)->findDistinctContentTypes($displayHidden);
    }

    /**
     * Get all the usage of a all the plugins
     */
    /** @return list<array<string, mixed>> */
    public static function getAllPlugins(bool $displayHidden = false, ?string $filter = null): array
    {
        return GeneralUtility::makeInstance(ContentUsageRepository::class)->findPlugins($displayHidden, $filter);
    }

    /**
     * Get all the usage of a all the ctypes
     *
     */
    /** @return list<array<string, mixed>> */
    public static function getAllCtypes(bool $displayHidden = false, ?string $filter = null): array
    {
        return GeneralUtility::makeInstance(ContentUsageRepository::class)->findContentTypes($displayHidden, $filter);
    }

    public static function hasLegacyListType(): bool
    {
        return GeneralUtility::makeInstance(ContentTypeResolver::class)->hasLegacyListType();
    }

    /**
     * @return list<string>
     */
    public static function getPluginContentTypes(): array
    {
        return GeneralUtility::makeInstance(ContentTypeResolver::class)->getPluginContentTypes();
    }

    /**
     * Return the display mode
     */
    public static function getPluginsDisplayMode(mixed $requestedDisplay = null): int
    {
        $displayMode = 0;

        if (! empty($GLOBALS['BE_USER'])) {
            // Check the display mode
            if ($requestedDisplay !== null) {
                $GLOBALS['BE_USER']->setAndSaveSessionData('additional_reports_menu', $requestedDisplay);
                $displayMode = $requestedDisplay;
            }

            // Check the session
            $sessionDisplay = $GLOBALS['BE_USER']->getSessionData('additional_reports_menu');
            if ($sessionDisplay !== null) {
                $displayMode = $sessionDisplay;
            }

            // force default reports to history value
            if ($displayMode == 1) {
                $displayMode = 5;
            }
        }

        return (int) $displayMode;
    }

    /**
     * Get a label
     */
    public static function getLl(string $key): string
    {
        return self::getLanguageService()->sL('LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:' . $key);
    }

    public static function getLanguageService(): LanguageService
    {
        // be
        if (! empty($GLOBALS['LANG'])) {
            return $GLOBALS['LANG'];
        }

        // fe
        if (! empty($GLOBALS['TSFE'])) {
            return $GLOBALS['TSFE'];
        }

        $languageService = GeneralUtility::makeInstance(LanguageServiceFactory::class)->create($GLOBALS['BE_USER']->uc['lang'] ?? 'default');
        $GLOBALS['LANG'] = $languageService;
        return $languageService;
    }

}
