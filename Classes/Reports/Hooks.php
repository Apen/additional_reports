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
        $structuredDataNormalizer = GeneralUtility::makeInstance(StructuredDataNormalizer::class);
        $hooks = [
            'core' => $this->collectHooks($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'], $structuredDataNormalizer, true),
            'extensions' => $this->collectHooks($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'], $structuredDataNormalizer),
        ];

        $view = $this->createView('hooks-fluid');
        $view->assign('hooks', $hooks);
        return $view->render();
    }

    /**
     * @param array<string, mixed> $items
     * @return list<array{corefile: string, name: string, file: array<int, array<string, mixed>>}>
     */
    private function collectHooks(array $items, StructuredDataNormalizer $structuredDataNormalizer, bool $coreHooks = false): array
    {
        $hooks = [];
        foreach ($items as $itemKey => $itemValue) {
            if (! is_array($itemValue)) {
                continue;
            }
            if ($coreHooks && preg_match('#.*?/.*?\.php#', $itemKey) !== 1) {
                continue;
            }
            foreach ($itemValue as $hookName => $hookList) {
                $resolvedHook = $this->hookResolver->resolve($hookList);
                if ($resolvedHook === null) {
                    continue;
                }
                if ($resolvedHook === []) {
                    continue;
                }
                if ($resolvedHook === '') {
                    continue;
                }

                $hooks[] = [
                    'corefile' => $itemKey,
                    'name' => (string) $hookName,
                    'file' => $structuredDataNormalizer->normalize($resolvedHook),
                ];
            }
        }

        return $hooks;
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
