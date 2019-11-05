<?php

namespace Sng\AdditionalReports\ViewHelpers;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * ViewHelper to check if a variable is in a list
 *
 * Example
 * <AdditionalReports:inList list="{AdditionalReports:session(index:'agenda', identifier:'dates')}" item="{eventDate.filtre}">...</AdditionalReports:inList>
 */
class InListViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper
{

    /**
     * @return void
     */
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
        if (\TYPO3\CMS\Core\Utility\GeneralUtility::inList($this->arguments['list'], $this->arguments['item']) === true) {
            return $this->renderThenChild();
        }
        return $this->renderElseChild();
    }

}

?>