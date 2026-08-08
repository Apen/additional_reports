<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Service\HookResolver;
use Sng\AdditionalReports\Service\StructuredDataNormalizer;
use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Hooks extends AbstractReport
{
    private readonly HookResolver $hookResolver;

    public function __construct(?object $reportObject = null, ?HookResolver $hookResolver = null)
    {
        parent::__construct($reportObject);
        $this->hookResolver = $hookResolver ?? GeneralUtility::makeInstance(HookResolver::class);
    }

    /**
     * This method renders the report
     *
     * @return string the status report as HTML
     */
    public function getReport(): string
    {
        $content = '<p class="help">' . Utility::getLL('hooks_description') . '</p>';
        return $content . $this->display();
    }

    /**
     * Generate the hooks report
     *
     * @return string HTML code
     */
    public function display(): string
    {
        $hooks = [];
        $structuredDataNormalizer = GeneralUtility::makeInstance(StructuredDataNormalizer::class);

        // core hooks
        $items = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'];
        if (count($items) > 0) {
            foreach ($items as $itemKey => $itemValue) {
                if (preg_match('#.*?\/.*?\.php#', $itemKey, $matches)) {
                    foreach ($itemValue as $hookName => $hookList) {
                        $hookList = $this->hookResolver->resolve($hookList);
                        if (!empty($hookList)) {
                            $hooks['core'][] = [
                                'corefile' => $itemKey,
                                'name'     => $hookName,
                                'file'     => $structuredDataNormalizer->normalize($hookList),
                            ];
                        }
                    }
                }
            }
        }

        $items = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
        if (count($items) > 0) {
            foreach ($items as $itemKey => $itemValue) {
                foreach ($itemValue as $hookName => $hookList) {
                    $hookList = $this->hookResolver->resolve($hookList);
                    if (!empty($hookList)) {
                        $hooks['extensions'][] = [
                            'corefile' => $itemKey,
                            'name'     => $hookName,
                            'file'     => $structuredDataNormalizer->normalize($hookList),
                        ];
                    }
                }
            }
        }

        $view = $this->createView('hooks-fluid');
        $view->assign('hooks', $hooks);
        return $view->render();
    }

    public function getIdentifier(): string
    {
        return 'additionalreports_hooks';
    }

    public function getTitle(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:hooks_title';
    }

    public function getDescription(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:hooks_description';
    }

    public function getIconIdentifier(): string
    {
        return 'module-reports';
    }
}
