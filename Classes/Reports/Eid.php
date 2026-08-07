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
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class Eid extends AbstractReport
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
     * Generate the eid report
     *
     * @return string HTML code
     */
    public function display()
    {
        $items = $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include'];
        $eids = [];

        if (count($items) > 0) {
            foreach ($items as $itemKey => $itemValue) {
                preg_match('#EXT:(.*?)\/#', $itemValue, $ext);
                if ($ext[1] ?? false) {
                    continue;
                }
                if (ExtensionManagementUtility::isLoaded($ext[1] ?? '')) {
                    $eids[] = [
                        'icon' => Utility::getExtIcon($ext[1]),
                        'extension' => $ext[1],
                        'name' => $itemKey,
                        'path' => $itemValue,
                    ];
                }
            }
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
        return 'additionalreports_eid';
    }
}
