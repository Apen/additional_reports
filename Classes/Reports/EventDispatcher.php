<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Utility;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use TYPO3\CMS\Core\DependencyInjection\ServiceProviderCompilationPass;
use TYPO3\CMS\Core\DependencyInjection\ServiceProviderRegistry;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Service\DependencyOrderingService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Reports\ReportInterface;

class EventDispatcher extends AbstractReport
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
        $events = $this->getAllEvents();
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/events-fluid.html');
        $view->assign('events', $events);
        return $view->render();
    }

    public function getAllEvents()
    {
        $packageManager = GeneralUtility::makeInstance(PackageManager::class);
        $packages = $packageManager->getActivePackages();
        $containerBuilder = new SymfonyContainerBuilder();
        $registry = new ServiceProviderRegistry($packageManager);
        $containerBuilder->addCompilerPass(new ServiceProviderCompilationPass($registry, 'service_provider_registry'));

        foreach ($packages as $package) {
            $diConfigDir = $package->getPackagePath() . 'Configuration/';
            if (file_exists($diConfigDir . 'Services.php')) {
                $phpFileLoader = new PhpFileLoader($containerBuilder, new FileLocator($diConfigDir));
                $phpFileLoader->load('Services.php');
            }
            if (file_exists($diConfigDir . 'Services.yaml')) {
                $yamlFileLoader = new YamlFileLoader($containerBuilder, new FileLocator($diConfigDir));
                $yamlFileLoader->load('Services.yaml');
            }
        }

        $events = [];
        foreach ($containerBuilder->getDefinitions() as $className => $definition) {
            $tags = $definition->getTags();
            if (!empty($tags)) {
                foreach ($tags as $tagType => $tag) {
                    if ($tagType === 'event.listener') {
                        $events[] = [
                            'className' => $className,
                            'list' => Utility::viewArray($tag)
                        ];
                    }
                }
            }
        }

        return $events;
    }

}
