<?php

namespace Sng\AdditionalReports\ViewHelpers;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * ViewHelper to check if a variable is in a list
 *
 * Example
 * <AdditionalReports:inList list="{AdditionalReports:session(index:'agenda', identifier:'dates')}" item="{eventDate.filtre}">...</AdditionalReports:inList>
 */
class InListViewHelper extends AbstractConditionViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('list', 'string', 'List');
        $this->registerArgument('item', 'string', 'Item');
    }

    /**
     * Renders else-child or else-argument if variable $item is in $list
     *
     * @return string
     */
    public function render()
    {
        if (GeneralUtility::inList($this->arguments['list'], $this->arguments['item'])) {
            return $this->renderThenChild();
        }
        return $this->renderElseChild();
    }
}
