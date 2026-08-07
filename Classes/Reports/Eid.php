<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Service\ExtensionIconResolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Eid extends AbstractReport
{
    private ExtensionIconResolver $extensionIconResolver;

    public function __construct(?object $reportObject = null, ?ExtensionIconResolver $extensionIconResolver = null)
    {
        parent::__construct($reportObject);
        $this->extensionIconResolver = $extensionIconResolver ?? GeneralUtility::makeInstance(ExtensionIconResolver::class);
    }

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
     * Generate the eid report
     *
     * @return string HTML code
     */
    public function display()
    {
        $items = $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include'] ?? [];
        $eids = [];

        foreach ($items as $itemKey => $itemValue) {
            $path = is_string($itemValue) ? $itemValue : get_debug_type($itemValue);
            preg_match('#^EXT:([^/]+)/#', $path, $matches);
            $extensionKey = $matches[1] ?? '';
            $eids[] = [
                'icon' => $this->extensionIconResolver->resolve($extensionKey),
                'extension' => $extensionKey,
                'name' => (string) $itemKey,
                'path' => $path,
            ];
        }

        $view = $this->createView();
        $view->assign('eids', $eids);
        return $view->render('eid-fluid');
    }

    public function getIdentifier(): string
    {
        return 'additionalreports_eid';
    }

    public function getTitle(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:eid_title';
    }

    public function getDescription(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:eid_description';
    }

    public function getIconIdentifier(): string
    {
        return 'module-reports';
    }
}
