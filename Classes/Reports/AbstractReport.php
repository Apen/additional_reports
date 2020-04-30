<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class provides a base for all the reports
 */
class AbstractReport
{

    /**
     * Back-reference to the calling reports module
     *
     * @var object $reportObject
     */
    protected $reportObject;

    /**
     * Constructor for class \Sng\AdditionalReports\Reports\AbstractReport
     *
     * @param object    Back-reference to the calling reports module
     */
    public function __construct($reportObject)
    {
        $this->reportObject = $reportObject;
        $this->setCss('EXT:additional_reports/Resources/Public/Css/tx_additionalreports.css');
        $GLOBALS['LANG']->includeLLFile('EXT:additional_reports/Resources/Private/Language/locallang.xlf');
    }

    /**
     * Set a Css
     *
     * @param $path
     */
    public function setCss($path)
    {
        if (isset($this->reportObject->doc)) {
            $this->reportObject->doc->getPageRenderer()->addCssFile($path);
        }
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addCssFile($path);
    }

    /**
     * Set a Js
     *
     * @param $path
     */
    public function setJs($path)
    {
        if (isset($this->reportObject->doc)) {
            $this->reportObject->doc->getPageRenderer()->addJsFile($path);
        }
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addJsFile($path);
    }
}
