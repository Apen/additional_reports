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
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility class
 */
class Utility
{
    /**
     * Define all the reports
     *
     * @return array
     */
    public static function getReportsList()
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
     *
     * @param int $id
     * @param int $depth
     * @param int $begin
     * @param string $permsClause
     * @return string
     */
    public static function getTreeList($id, $depth, $begin = 0, $permsClause = '1=1')
    {
        if ((int) $begin !== 0 || $permsClause !== '1=1') {
            throw new \InvalidArgumentException('Custom tree offsets and SQL permission clauses are no longer supported.');
        }
        return implode(',', GeneralUtility::makeInstance(PageStatisticsRepository::class)->findPageIdsRecursive((int) $id, (int) $depth));
    }

    /**
     * Count page uids in a list given (validating the condition)
     *
     * @param string $listOfUids
     * @param string $field
     * @return int
     */
    public static function getCountPagesUids(string $listOfUids, string $field = ''): int
    {
        $pageUids = array_values(array_filter(array_map('intval', explode(',', $listOfUids))));
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
     * Get last version information for an extkey
     *
     * @param array $extInfo
     * @return array
     */
    public static function checkExtensionUpdate($extInfo)
    {
        return GeneralUtility::makeInstance(ExtensionRepository::class)->findLatestVersion($extInfo);
    }

    /**
     * Get the HTTP icon path of an extension
     *
     * @param string $extKey
     * @return string
     */
    public static function getExtIcon($extKey)
    {
        return GeneralUtility::makeInstance(ExtensionIconResolver::class)->resolve(is_string($extKey) ? $extKey : '');
    }

    /**
     * Get principal domain by page uid
     */
    public static function getDomain(int $pageUid): string
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        try {
            $siteConf = $siteFinder->getSiteByPageId($pageUid);
            if (! empty($siteConf)) {
                return $siteConf->getBase()
                    ->getHost();
            }
        } catch (SiteNotFoundException $siteNotFoundException) {
            return '';
        }
        return '';
    }

    /**
     * Get the version of a given extension
     *
     * @param string $key
     */
    public static function getExtensionVersion($key): ?string
    {
        if (! is_string($key) || empty($key)) {
            throw new \InvalidArgumentException('Extension key must be a non-empty string.');
        }
        return GeneralUtility::makeInstance(ExtensionRepository::class)->findVersion($key);
    }

    public static function getAllDifferentPlugins(bool $displayHidden = false): array
    {
        return GeneralUtility::makeInstance(ContentUsageRepository::class)->findDistinctPlugins($displayHidden);
    }

    public static function getAllDifferentCtypes(bool $displayHidden = false): array
    {
        return GeneralUtility::makeInstance(ContentUsageRepository::class)->findDistinctContentTypes($displayHidden);
    }

    /**
     * Get all the usage of a all the plugins
     *
     * @return array
     */
    public static function getAllPlugins(bool $displayHidden = false, ?string $filter = null): array
    {
        return GeneralUtility::makeInstance(ContentUsageRepository::class)->findPlugins($displayHidden, $filter);
    }

    /**
     * Get all the usage of a all the ctypes
     *
     */
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
     *
     * @return int
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

    public static function buildPagination(array $items, int $currentPage, &$view): void
    {
        if (count($items) > 0) {
            try {
                $itemsPerPage = (int) GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('additional_reports', 'itemsPerPage');
                if ($itemsPerPage < 1) {
                    $itemsPerPage = 10;
                }
            } catch (\Exception $e) {
                $itemsPerPage = 10;
            }
            $paginator = new ArrayPaginator($items, $currentPage, $itemsPerPage);
            $pagination = new SlidingWindowPagination($paginator, 5);
            $view->assign('paginator', $paginator);
            $view->assign('pagination', $pagination);
        }
    }

}
