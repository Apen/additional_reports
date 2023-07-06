<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Reports\ReportInterface;
use TYPO3\CMS\Reports\RequestAwareReportInterface;

/**
 * This class provides a base for all the reports
 */
abstract class AbstractReport implements ReportInterface
{
    /**
     * Back-reference to the calling reports module
     *
     * @var object $reportObject
     */
    protected $reportObject;

    /**
     * @param object Back-reference to the calling reports module
     */
    public function __construct($reportObject = null)
    {
        $this->reportObject = $reportObject;
        $this->setCss('EXT:additional_reports/Resources/Public/Css/tx_additionalreports.css');
        Utility::getLanguageService()->includeLLFile('EXT:additional_reports/Resources/Private/Language/locallang.xlf');
    }

    public function setCss(string $path): void
    {
        if (isset($this->reportObject->doc)) {
            $this->reportObject->doc->getPageRenderer()->addCssFile($path);
        }
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addCssFile($path);
    }

    public function setJs(string $path): void
    {
        if (isset($this->reportObject->doc)) {
            $this->reportObject->doc->getPageRenderer()->addJsFile($path);
        }
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addJsFile($path);
    }
}
