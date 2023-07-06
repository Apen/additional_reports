<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Psr\Http\Message\ServerRequestInterface;
use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\TypoScript\ExtendedTemplateService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Reports\ReportInterface;

class WebsiteConf extends AbstractReport
{
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
        $items = Utility::exec_SELECTgetRows(
            'uid, title',
            'pages',
            'is_siteroot = 1 AND deleted = 0 AND hidden = 0 AND pid != -1',
            '',
            '',
            '',
            'uid'
        );

        $websiteconf = [];

        if (!empty($items)) {
            foreach ($items as $itemValue) {
                $websiteconfItem = [];

                $websiteconfItem['pid'] = $itemValue['uid'];
                $websiteconfItem['pagetitle'] = $itemValue['title'];
                $websiteconfItem['domains'] = '';
                $websiteconfItem['template'] = '';
                $websiteconfItem['domains'] = Utility::getDomain($itemValue['uid']) . '<br/>';

                $templates = Utility::exec_SELECTgetRows(
                    'uid,title,root',
                    'sys_template',
                    'pid IN(' . $itemValue['uid'] . ') AND deleted=0 AND hidden=0',
                    '',
                    'sorting'
                );

                foreach ($templates as $templateObj) {
                    $websiteconfItem['template'] .= $templateObj['title'] . ' ';
                    $websiteconfItem['template'] .= '[uid=' . $templateObj['uid'] . ',root=' . $templateObj['root'] . ']<br/>';
                }

                // baseurl
                if (class_exists(ExtendedTemplateService::class)) {
                    $tmpl = GeneralUtility::makeInstance(ExtendedTemplateService::class);
                } else {
                    $tmpl = GeneralUtility::makeInstance(\TYPO3\CMS\Core\TypoScript\TemplateService::class);
                }
                //$tmpl = GeneralUtility::makeInstance(ExtendedTemplateService::class);
                $tmpl->tt_track = 0;
                $tmpl->runThroughTemplates(Utility::getRootLine($itemValue['uid']), 0);
                $tmpl->generateConfig();
                $websiteconfItem['baseurl'] = $tmpl->setup['config.']['baseURL'] ?? '';

                // count pages
                $list = Utility::getTreeList($itemValue['uid'], 99);
                $listArray = explode(',', $list);
                $websiteconfItem['pages'] = (count($listArray) - 1);
                $websiteconfItem['pageshidden'] = (Utility::getCountPagesUids($list, 'hidden=1'));
                $websiteconfItem['pagesnosearch'] = (Utility::getCountPagesUids($list, 'no_search=1'));

                $websiteconf[] = $websiteconfItem;
            }
        }

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/websiteconf-fluid.html');
        $view->assign('items', $websiteconf);
        return $view->render();
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
        return 'additionalreports_websitesconf';
    }
}
