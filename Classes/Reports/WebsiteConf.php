<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Utility;

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
        $queryBuilder = Utility::getQueryBuilder('pages');
        $items = $queryBuilder
            ->select('uid', 'title')
            ->from('pages')
            ->where($queryBuilder->expr()->eq('is_siteroot', 1))
            ->andWhere($queryBuilder->expr()->eq('hidden', 0))
            ->andWhere($queryBuilder->expr()->neq('pid', -1))
            ->executeQuery()
            ->fetchAllAssociative();

        $websiteconf = [];

        if (! empty($items)) {
            foreach ($items as $itemValue) {
                $websiteconfItem = [];

                $websiteconfItem['pid'] = $itemValue['uid'];
                $websiteconfItem['pagetitle'] = $itemValue['title'];
                $websiteconfItem['domains'] = '';
                $websiteconfItem['template'] = '';
                $websiteconfItem['domains'] = Utility::getDomain($itemValue['uid']) . '<br/>';

                $queryBuilder = Utility::getQueryBuilder('sys_template');
                $templates = $queryBuilder
                    ->select('uid', 'title', 'root')
                    ->from('sys_template')
                    ->where($queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter((int) $itemValue['uid'])))
                    ->andWhere($queryBuilder->expr()->eq('hidden', 0))
                    ->orderBy('sorting')
                    ->executeQuery()
                    ->fetchAllAssociative();

                foreach ($templates as $templateObj) {
                    $websiteconfItem['template'] .= $templateObj['title'] . ' ';
                    $websiteconfItem['template'] .= '[uid=' . $templateObj['uid'] . ',root=' . $templateObj['root'] . ']<br/>';
                }

                // count pages
                $list = Utility::getTreeList($itemValue['uid'], 99);
                $listArray = explode(',', $list);
                $websiteconfItem['pages'] = (count($listArray) - 1);
                $websiteconfItem['pageshidden'] = Utility::getCountPagesUids($list, 'hidden');
                $websiteconfItem['pagesnosearch'] = Utility::getCountPagesUids($list, 'no_search');

                $websiteconf[] = $websiteconfItem;
            }
        }

        $view = $this->createView();
        $view->assign('items', $websiteconf);
        return $view->render('websiteconf-fluid');
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
