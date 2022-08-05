<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Service\DependencyOrderingService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Reports\ReportInterface;

class Middlewares extends AbstractReport
{
    /**
     * This method renders the report
     *
     * @return string the status report as HTML
     */
    public function getReport()
    {
        return $this->display();
    }

    /**
     * Generate the eid report
     *
     * @return string HTML code
     */
    public function display()
    {
        $allMiddlewares = $this->getAllMiddlewares();
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/middlewares-fluid.html');
        $view->assign('middlewares', $this->filterAllMiddlewares($allMiddlewares));
        return $view->render();
    }

    public function getAllMiddlewares(): array
    {
        $packageManager = GeneralUtility::makeInstance(PackageManager::class);
        $packages = $packageManager->getActivePackages();
        $allMiddlewares = [[]];
        foreach ($packages as $package) {
            $packageConfiguration = $package->getPackagePath() . 'Configuration/RequestMiddlewares.php';
            if (file_exists($packageConfiguration)) {
                $middlewaresInPackage = require $packageConfiguration;
                if (is_array($middlewaresInPackage)) {
                    $allMiddlewares[] = $middlewaresInPackage;
                }
            }
        }
        return array_replace_recursive(...$allMiddlewares);
    }

    public function filterAllMiddlewares(array $allMiddlewares): array
    {
        $dependencyOrderingService = GeneralUtility::makeInstance(DependencyOrderingService::class);
        $middlewares = [];
        foreach ($allMiddlewares as $stack => $middlewaresOfStack) {
            $middlewaresOfStack = $dependencyOrderingService->orderByDependencies($middlewaresOfStack);
            $sanitizedMiddlewares = [];
            foreach ($middlewaresOfStack as $name => $middleware) {
                if (isset($middleware['disabled']) && $middleware['disabled'] === true) {
                    continue;
                }
                $sanitizedMiddlewares[$name] = $middleware['target'];
            }
            $middlewares[$stack] = $sanitizedMiddlewares;
        }
        return $middlewares;
    }
}
