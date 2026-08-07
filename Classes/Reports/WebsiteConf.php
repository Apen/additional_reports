<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Repository\PageStatisticsRepository;
use Sng\AdditionalReports\Repository\WebsiteConfigurationRepository;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class WebsiteConf extends AbstractReport
{
    private WebsiteConfigurationRepository $websiteConfigurationRepository;

    private PageStatisticsRepository $pageStatisticsRepository;

    private SiteFinder $siteFinder;

    public function __construct(
        ?object $reportObject = null,
        ?WebsiteConfigurationRepository $websiteConfigurationRepository = null,
        ?PageStatisticsRepository $pageStatisticsRepository = null,
        ?SiteFinder $siteFinder = null,
    ) {
        parent::__construct($reportObject);
        $this->websiteConfigurationRepository = $websiteConfigurationRepository ?? GeneralUtility::makeInstance(WebsiteConfigurationRepository::class);
        $this->pageStatisticsRepository = $pageStatisticsRepository ?? GeneralUtility::makeInstance(PageStatisticsRepository::class);
        $this->siteFinder = $siteFinder ?? GeneralUtility::makeInstance(SiteFinder::class);
    }

    /**
     * This method renders the report
     *
     * @return string the status report as HTML
     */
    public function getReport(): string
    {
        return $this->display();
    }

    /**
     * Generate the website conf report
     *
     * @return string HTML code
     */
    public function display()
    {
        $websiteconf = [];
        foreach ($this->websiteConfigurationRepository->findVisibleRootPages() as $rootPage) {
            $pageIds = $this->pageStatisticsRepository->findPageIdsRecursive($rootPage['uid'], 99);
            $domain = $this->findDomain($rootPage['uid']);
            $websiteconf[] = [
                'pid' => $rootPage['uid'],
                'pagetitle' => $rootPage['title'],
                'domains' => $domain === '' ? [] : [$domain],
                'templates' => $this->websiteConfigurationRepository->findVisibleTemplates($rootPage['uid']),
                'pages' => max(0, count($pageIds) - 1),
                'pageshidden' => $this->pageStatisticsRepository->countByFlag($pageIds, 'hidden'),
                'pagesnosearch' => $this->pageStatisticsRepository->countByFlag($pageIds, 'no_search'),
            ];
        }

        $view = $this->createView();
        $view->assign('items', $websiteconf);
        return $view->render('websiteconf-fluid');
    }

    private function findDomain(int $pageId): string
    {
        try {
            return $this->siteFinder->getSiteByPageId($pageId)->getBase()->getHost();
        } catch (SiteNotFoundException) {
            return '';
        }
    }

    public function getIdentifier(): string
    {
        return 'additionalreports_websitesconf';
    }

    public function getTitle(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:websitesconf_title';
    }

    public function getDescription(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:websitesconf_description';
    }

    public function getIconIdentifier(): string
    {
        return 'module-reports';
    }
}
