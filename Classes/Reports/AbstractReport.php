<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Route as SymfonyRoute;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Reports\ReportInterface;

/**
 * This class provides a base for all the reports
 */
trait AbstractReportImplementation
{
    /**
     * Back-reference to the calling reports module
     *
     * @var object
     */
    protected ?object $reportObject;

    /**
     * @param object $reportObject Back-reference to the calling reports module
     */
    public function __construct(?object $reportObject = null)
    {
        $this->reportObject = $reportObject;
        $this->setCss('EXT:additional_reports/Resources/Public/Css/tx_additionalreports.css');
    }

    public function setCss(string $path): void
    {
        if (isset($this->reportObject->doc)) {
            $this->reportObject->doc->getPageRenderer()
                ->addCssFile($path);
        }
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addCssFile($path);
    }

    protected function createView(): ViewInterface
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        $viewFactoryData = new ViewFactoryData(
            templateRootPaths: ['EXT:additional_reports/Resources/Private/Templates'],
            partialRootPaths: ['EXT:additional_reports/Resources/Private/Partials'],
            layoutRootPaths: ['EXT:additional_reports/Resources/Private/Layouts'],
            request: $request instanceof ServerRequestInterface ? $request : null,
        );

        return GeneralUtility::makeInstance(ViewFactoryInterface::class)->create($viewFactoryData);
    }

    protected function getCurrentRouteIdentifier(): string
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        $route = $request instanceof ServerRequestInterface ? $request->getAttribute('route') : null;
        if ($route instanceof Route || $route instanceof SymfonyRoute) {
            $identifier = $route->getOption('_identifier');
            if (is_string($identifier) && $identifier !== '') {
                return $identifier;
            }
        }
        throw new \RuntimeException('The report requires a backend request with an identified route.', 1786128600);
    }
}

// ReportInterface was removed in TYPO3 v14. Keeping the interface on TYPO3 v13
// allows the Reports service autoconfiguration to continue working there.
if (interface_exists(ReportInterface::class)) {
    abstract class AbstractReport implements ReportInterface
    {
        use AbstractReportImplementation;
    }
} else {
    abstract class AbstractReport
    {
        use AbstractReportImplementation;
    }
}
