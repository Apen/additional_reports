<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

class WebsiteConf extends \Sng\AdditionalReports\Reports\AbstractReport implements \TYPO3\CMS\Reports\ReportInterface
{

    /**
     * This method renders the report
     *
     * @return    string    The status report as HTML
     */
    public function getReport()
    {
        $content = '';
        $content .= $this->display();
        return $content;
    }

    /**
     * Generate the website conf report
     *
     * @return string HTML code
     */
    public function display()
    {
        $items = \Sng\AdditionalReports\Utility::exec_SELECTgetRows(
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
            foreach ($items as $itemKey => $itemValue) {
                $websiteconfItem = [];

                $domainRecords = \Sng\AdditionalReports\Utility::exec_SELECTgetRows(
                    'uid, pid, domainName',
                    'sys_domain',
                    'pid IN(' . $itemValue['uid'] . ') AND hidden=0',
                    '',
                    'sorting'
                );

                $websiteconfItem['pid'] = $itemValue['uid'];
                $websiteconfItem['pagetitle'] = \Sng\AdditionalReports\Utility::getIconPage() . $itemValue['title'];
                $websiteconfItem['domains'] = '';
                $websiteconfItem['template'] = '';

                foreach ($domainRecords as $domain) {
                    $websiteconfItem['domains'] .= \Sng\AdditionalReports\Utility::getIconDomain() . $domain['domainName'] . '<br/>';
                }

                $templates = \Sng\AdditionalReports\Utility::exec_SELECTgetRows(
                    'uid,title,root',
                    'sys_template',
                    'pid IN(' . $itemValue['uid'] . ') AND deleted=0 AND hidden=0',
                    '',
                    'sorting'
                );

                foreach ($templates as $templateObj) {
                    $websiteconfItem['template'] .= \Sng\AdditionalReports\Utility::getIconTemplate() . ' ' . $templateObj['title'] . ' ';
                    $websiteconfItem['template'] .= '[uid=' . $templateObj['uid'] . ',root=' . $templateObj['root'] . ']<br/>';
                }

                // baseurl
                $tmpl = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\TypoScript\\ExtendedTemplateService');
                $tmpl->tt_track = 0;
                $tmpl->init();
                $tmpl->runThroughTemplates(\Sng\AdditionalReports\Utility::getRootLine($itemValue['uid']), 0);
                $tmpl->generateConfig();
                $websiteconfItem['baseurl'] = $tmpl->setup['config.']['baseURL'];

                // count pages
                $list = \Sng\AdditionalReports\Utility::getTreeList($itemValue['uid'], 99, 0, '1=1');
                $listArray = explode(',', $list);
                $websiteconfItem['pages'] = (count($listArray) - 1);
                $websiteconfItem['pageshidden'] = (\Sng\AdditionalReports\Utility::getCountPagesUids($list, 'hidden=1'));
                $websiteconfItem['pagesnosearch'] = (\Sng\AdditionalReports\Utility::getCountPagesUids($list, 'no_search=1'));

                $websiteconf[] = $websiteconfItem;
            }
        }

        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/websiteconf-fluid.html');
        $view->assign('items', $websiteconf);
        return $view->render();
    }
}
