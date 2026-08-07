<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\ViewHelpers;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Utility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ViewHelper to get all infos on a plugin or content
 *
 * Example
 * <ar:contentInfos item="{item}" as="item" ctype="TRUE"/>
 */
class ContentInfosViewHelper extends AbstractViewHelper
{
    /**
     * Disable escaping of tag based ViewHelpers so that the rendered tag is not htmlspecialchar'd
     *
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('item', 'array', 'Current item array', false, null);
        $this->registerArgument('as', 'string', 'Name of the items array', false, null);
        $this->registerArgument('plugin', 'boolean', 'Is it a plugin?', false, null);
        $this->registerArgument('ctype', 'boolean', 'Is it a CType?', false, null);
    }

    /**
     * Renders else-child or else-argument if variable $item is in $list
     */
    public function render(): mixed
    {
        $item = $this->arguments['item'];
        $as = $this->arguments['as'];
        $plugin = $this->arguments['plugin'];
        $ctype = $this->arguments['ctype'];

        // plugin
        if ($plugin === true) {
            if (Utility::hasLegacyListType()) {
                $item = array_merge($item, Utility::getContentInfosFromTca('plugin', $item['list_type']));
            } else {
                $item = array_merge($item, Utility::getContentInfosFromTca('ctype', $item['CType']));
            }
        }

        // CType
        if ($ctype === true) {
            $item = array_merge($item, \Sng\AdditionalReports\Utility::getContentInfosFromTca('ctype', $item['CType']));
        }

        $item = array_merge($item, $this->getContentInfos($item));

        if ($this->templateVariableContainer->exists($as)) {
            $this->templateVariableContainer->remove($as);
        }
        $this->templateVariableContainer->add($as, $item);

        return null;
    }

    /**
     * Return informations about a ctype or plugin
     */
    public function getContentInfos(array $itemValue): array
    {
        $markersExt = [];

        $markersExt['domain'] = Utility::getDomain($itemValue['pid']);
        $markersExt['pagetitle'] = $itemValue['title'];

        $markersExt['usedtv'] = '';
        $markersExt['usedtvclass'] = '';

        $markersExt['listOnClick'] = Utility::goToModuleList($itemValue['pid']);
        $markersExt['listUrl'] = Utility::goToModuleList($itemValue['pid'], true);
        $markersExt['pageOnClick'] = Utility::goToModulePage($itemValue['pid']);
        $markersExt['pageUrl'] = Utility::goToModulePage($itemValue['pid'], true);

        $markersExt['preview'] = '/index.php?id=' . $itemValue['pid'];

        return $markersExt;
    }
}
