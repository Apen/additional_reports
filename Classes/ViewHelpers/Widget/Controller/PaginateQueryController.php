<?php

namespace Sng\AdditionalReports\ViewHelpers\Widget\Controller;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * PaginateQuery controller to create the pagination.
 * Extended version from fluid core
 */
class PaginateQueryController extends \TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetController
{

    /**
     * @var array
     */
    protected $configuration = array(
        'itemsPerPage'           => 10,
        'insertAbove'            => false,
        'insertBelow'            => true,
        'pagesAfter'             => 3,
        'pagesBefore'            => 3,
        'lessPages'              => true,
        'forcedNumberOfLinks'    => 5,
        'forceFirstPrevNextlast' => false,
        'showFirstLast'          => true
    );

    /**
     * @var array
     */
    protected $query;

    /**
     * @var integer
     */
    protected $numberOfItems;

    /**
     * @var integer
     */
    protected $currentPage = 1;

    /**
     * @var integer
     */
    protected $pagesBefore = 1;

    /**
     * @var integer
     */
    protected $pagesAfter = 1;

    /**
     * @var boolean
     */
    protected $lessPages = false;

    /**
     * @var integer
     */
    protected $forcedNumberOfLinks = 10;

    /**
     * @var integer
     */
    protected $numberOfPages = 1;

    /**
     * Initialize the action and get correct configuration
     *
     * @return void
     */
    public function initializeAction()
    {
        $this->query = $this->widgetConfiguration['query'];
        $this->configuration = \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule(
            $this->configuration,
            (array)$this->widgetConfiguration['configuration'],
            true
        );

        if (empty($this->configuration['itemsPerPage'])) {
            $this->configuration['itemsPerPage'] = 50;
        }

        $res = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($this->query);
        $this->numberOfItems = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
        $this->numberOfPages = ceil($this->numberOfItems / (integer)$this->configuration['itemsPerPage']);
        $GLOBALS['TYPO3_DB']->sql_free_result($res);

        $this->pagesBefore = (integer)$this->configuration['pagesBefore'];
        $this->pagesAfter = (integer)$this->configuration['pagesAfter'];
        $this->lessPages = (boolean)$this->configuration['lessPages'];
        $this->forcedNumberOfLinks = (integer)$this->configuration['forcedNumberOfLinks'];
    }

    /**
     * If a certain number of links should be displayed, adjust before and after
     * amounts accordingly.
     *
     * @return void
     */
    protected function adjustForForcedNumberOfLinks()
    {
        $forcedNumberOfLinks = $this->forcedNumberOfLinks;
        if ($forcedNumberOfLinks > $this->numberOfPages) {
            $forcedNumberOfLinks = $this->numberOfPages;
        }
        $totalNumberOfLinks = min($this->currentPage, $this->pagesBefore) +
            min($this->pagesAfter, $this->numberOfPages - $this->currentPage) + 1;
        if ($totalNumberOfLinks <= $forcedNumberOfLinks) {
            $delta = intval(ceil(($forcedNumberOfLinks - $totalNumberOfLinks) / 2));
            $incr = ($forcedNumberOfLinks & 1) == 0 ? 1 : 0;
            if ($this->currentPage - ($this->pagesBefore + $delta) < 1) {
                // Too little from the right to adjust
                $this->pagesAfter = $forcedNumberOfLinks - $this->currentPage - 1;
                $this->pagesBefore = $forcedNumberOfLinks - $this->pagesAfter - 1;
            } elseif ($this->currentPage + ($this->pagesAfter + $delta) >= $this->numberOfPages) {
                $this->pagesBefore = $forcedNumberOfLinks - ($this->numberOfPages - $this->currentPage);
                $this->pagesAfter = $forcedNumberOfLinks - $this->pagesBefore - 1;
            } else {
                $this->pagesBefore += $delta;
                $this->pagesAfter += $delta - $incr;
            }
        }
    }

    /**
     * Main action which does all the fun
     *
     * @param integer $currentPage
     * @return void
     */
    public function indexAction($currentPage = 1)
    {
        // ugly patch to work without extbase (sry for that)

        $widgetIdentifier = '__widget_0';
        if (\Sng\AdditionalReports\Utility::intFromVer(TYPO3_version) >= 6002000) {
            $widgetIdentifier = '@widget_0';
        }

        if (($currentPage == 1) && (!empty($_GET['tx__'][$widgetIdentifier]['currentPage']))) {
            $currentPage = (int)$_GET['tx__'][$widgetIdentifier]['currentPage'];
        }

        // set current page
        $this->currentPage = (integer)$currentPage;
        if ($this->currentPage < 1) {
            $this->currentPage = 1;
        } elseif (!empty($this->numberOfPages) && ($this->currentPage > $this->numberOfPages)) {
            $this->currentPage = $this->numberOfPages;
        }

        // modify query
        $itemsPerPage = (integer)$this->configuration['itemsPerPage'];

        $this->query['LIMIT'] = (integer)($itemsPerPage * ($this->currentPage - 1)) . ',' . $itemsPerPage;
        $res = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($this->query);

        $modifiedObjects = array();

        while ($tempRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $modifiedObjects[] = $tempRow;
        }

        $GLOBALS['TYPO3_DB']->sql_free_result($res);

        $this->view->assign('contentArguments', array($this->widgetConfiguration['as'] => $modifiedObjects));
        $this->view->assign('configuration', $this->configuration);
        $this->view->assign('pagination', $this->buildPagination());
    }

    /**
     * Returns an array with the keys
     * "pages", "current", "numberOfPages", "nextPage" & "previousPage"
     *
     * @return array
     */
    public function buildPagination()
    {
        $this->adjustForForcedNumberOfLinks();

        $pages = array();
        $start = max($this->currentPage - $this->pagesBefore, 0);
        $end = min($this->numberOfPages, $this->currentPage + $this->pagesAfter + 1);
        for ($i = $start; $i < $end; $i++) {
            $j = $i + 1;
            $pages[] = array('number' => $j, 'isCurrent' => (intval($j) === intval($this->currentPage)));
        }

        $pagination = array(
            'pages'         => $pages,
            'current'       => $this->currentPage,
            'numberOfPages' => $this->numberOfPages,
            'numberOfItems' => $this->numberOfItems,
            'pagesBefore'   => $this->pagesBefore,
            'pagesAfter'    => $this->pagesAfter,
            'firstPageItem' => ($this->currentPage - 1) * (int)$this->configuration['itemsPerPage'] + 1
        );
        if ($this->currentPage < $this->numberOfPages) {
            $pagination['nextPage'] = $this->currentPage + 1;
            $pagination['lastPageItem'] = $this->currentPage * (integer)$this->configuration['itemsPerPage'];
        } else {
            $pagination['lastPageItem'] = $pagination['numberOfItems'];
        }

        // previous pages
        if ($this->currentPage > 1) {
            $pagination['previousPage'] = $this->currentPage - 1;
        }

        // less pages (before current)
        if ($start > 0 && $this->lessPages) {
            $pagination['lessPages'] = true;
        }

        // next pages (after current)
        if ($end != $this->numberOfPages && $this->lessPages) {
            $pagination['morePages'] = true;
        }

        return $pagination;
    }
}
