<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\TypoScript\ExtendedTemplateService;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Reports\ReportInterface;
use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class WebsiteConf extends AbstractReport implements ReportInterface
{

    /**
     * This method renders the report
     *
     * @return string the status report as HTML
     */
    public function getReport()
    {
        $content = '';
        return $content . $this->display();
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

                $domainRecords = Utility::exec_SELECTgetRows(
                    'uid, pid, domainName',
                    'sys_domain',
                    'pid IN(' . $itemValue['uid'] . ') AND hidden=0',
                    '',
                    'sorting'
                );

                $websiteconfItem['pid'] = $itemValue['uid'];
                $websiteconfItem['pagetitle'] = Utility::getIconPage() . $itemValue['title'];
                $websiteconfItem['domains'] = '';
                $websiteconfItem['template'] = '';

                foreach ($domainRecords as $domain) {
                    $websiteconfItem['domains'] .= Utility::getIconDomain() . $domain['domainName'] . '<br/>';
                }

                $templates = Utility::exec_SELECTgetRows(
                    'uid,title,root',
                    'sys_template',
                    'pid IN(' . $itemValue['uid'] . ') AND deleted=0 AND hidden=0',
                    '',
                    'sorting'
                );

                foreach ($templates as $templateObj) {
                    $websiteconfItem['template'] .= Utility::getIconTemplate() . ' ' . $templateObj['title'] . ' ';
                    $websiteconfItem['template'] .= '[uid=' . $templateObj['uid'] . ',root=' . $templateObj['root'] . ']<br/>';
                }

                // baseurl
                $tmpl = GeneralUtility::makeInstance(ExtendedTemplateService::class);
                $tmpl->tt_track = 0;
                $tmpl->init();
                $tmpl->runThroughTemplates(Utility::getRootLine($itemValue['uid']), 0);
                $tmpl->generateConfig();
                $websiteconfItem['baseurl'] = $tmpl->setup['config.']['baseURL'];

                // count pages
                $list = Utility::getTreeList($itemValue['uid'], 99, 0, '1=1');
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
}
