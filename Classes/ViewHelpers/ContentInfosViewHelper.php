<?php

namespace Sng\AdditionalReports\ViewHelpers;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Localization\LocalizationFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
     * @var boolean
     */
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        $this->registerArgument('item', 'array', 'Current item array', false, null);
        $this->registerArgument('as', 'string', 'Name of the items array', false, null);
        $this->registerArgument('plugin', 'boolean', 'Is it a plugin?', false, null);
        $this->registerArgument('ctype', 'boolean', 'Is it a CType?', false, null);
    }

    /**
     * Renders else-child or else-argument if variable $item is in $list
     */
    public function render()
    {
        $item = $this->arguments['item'];
        $as = $this->arguments['as'];
        $plugin = $this->arguments['plugin'];
        $ctype = $this->arguments['ctype'];

        // plugin
        if ($plugin === true) {
            $item = array_merge($item, \Sng\AdditionalReports\Utility::getContentInfosFromTca('plugin', $item['list_type']));
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
    }

    /**
     * Return informations about a ctype or plugin
     *
     * @param array $itemValue
     * @return array
     */
    public function getContentInfos(array $itemValue): array
    {
        $markersExt = [];

        $domain = Utility::getDomain($itemValue['pid']);
        $markersExt['domain'] = Utility::getIconDomain() . ' ' . $domain;

        $iconPage = ($itemValue['hiddenpages'] == 0) ? Utility::getIconPage() : Utility::getIconPage(true);
        $iconContent = ($itemValue['hiddentt_content'] == 0) ? Utility::getIconContent() : Utility::getIconContent(true);

        $markersExt['pid'] = $iconPage . ' ' . $itemValue['pid'];
        $markersExt['uid'] = $iconContent . ' ' . $itemValue['uid'];
        $markersExt['pagetitle'] = $itemValue['title'];

        $markersExt['usedtv'] = '';
        $markersExt['usedtvclass'] = '';

        $linkAtt = ['href' => '#', 'title' => Utility::getLl('switch'), 'onclick' => Utility::goToModuleList($itemValue['pid']), 'class' => 'btn btn-default'];
        $markersExt['db'] = Utility::generateLink($linkAtt, Utility::getIconWebList());

        $linkAtt = ['href' => Utility::goToModuleList($itemValue['pid'], true), 'target' => '_blank', 'title' => Utility::getLl('newwindow'), 'class' => 'btn btn-default'];
        $markersExt['db'] .= Utility::generateLink($linkAtt, Utility::getIconWebList());

        $linkAtt = ['href' => '#', 'title' => Utility::getLl('switch'), 'onclick' => Utility::goToModulePage($itemValue['pid']), 'class' => 'btn btn-default'];
        $markersExt['page'] = Utility::generateLink($linkAtt, Utility::getIconWebPage());

        $linkAtt = ['href' => Utility::goToModulePage($itemValue['pid'], true), 'target' => '_blank', 'title' => Utility::getLl('newwindow'), 'class' => 'btn btn-default'];
        $markersExt['page'] .= Utility::generateLink($linkAtt, Utility::getIconWebPage());

        $markersExt['preview'] = '/index.php?id=' . $itemValue['pid'];

        return $markersExt;
    }
}
