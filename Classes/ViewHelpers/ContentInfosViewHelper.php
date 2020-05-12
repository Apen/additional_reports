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
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * ViewHelper to get all infos on a plugin or content
 *
 * Example
 * <ar:contentInfos item="{item}" as="item" ctype="TRUE"/>
 */
class ContentInfosViewHelper extends AbstractConditionViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('item', 'array', 'Current item array', false, null);
        $this->registerArgument('as', 'string', 'Name of the items array', false, null);
        $this->registerArgument('plugin', 'boolean', 'Is it a plugin?', false, null);
        $this->registerArgument('ctype', 'boolean', 'Is it a CType?', false, null);
    }

    /**
     * Renders else-child or else-argument if variable $item is in $list
     *
     * @param string $list
     * @param string $item
     * @return string
     */
    public function render()
    {
        $item = $this->arguments['item'];
        $as = $this->arguments['as'];
        $plugin = $this->arguments['plugin'];
        $ctype = $this->arguments['ctype'];

        $languageFactory = GeneralUtility::makeInstance(LocalizationFactory::class);

        // plugin
        if ($plugin === true) {
            foreach ($GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] as $itemValue) {
                if (trim($itemValue[1]) == $item['list_type']) {
                    preg_match('#EXT:(.*?)\/#', $itemValue[0], $ext);
                    preg_match('#^LLL:(EXT:.*?):(.*)#', $itemValue[0], $llfile);
                    $localLang = $languageFactory->getParsedData($llfile[1], Utility::getLanguageService()->lang);
                    $item['iconext'] = Utility::getExtIcon($ext[1]);
                    $item['extension'] = $ext[1];
                    $item['plugin'] = Utility::getLanguageService()->getLLL($llfile[2], $localLang) . ' (' . $item['list_type'] . ')';
                } else {
                    $item['plugin'] = $item['list_type'];
                }
            }
        }

        // CType
        if ($ctype === true) {
            foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $itemValue) {
                if ($itemValue[1] != '--div--') {
                    if (trim($itemValue[1]) == $item['CType']) {
                        preg_match('#^LLL:(EXT:.*?):(.*)#', $itemValue[0], $llfile);
                        $localLang = $languageFactory->getParsedData($llfile[1], Utility::getLanguageService()->lang);
                        $item['iconext'] = Utility::getContentTypeIcon($itemValue[2]);
                        $item['ctype'] = Utility::getLanguageService()->getLLL($llfile[2], $localLang) . ' (' . $item['CType'] . ')';
                    } else {
                        $item['ctype'] = $item['CType'];
                    }
                }
            }
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
    public function getContentInfos($itemValue)
    {
//        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($itemValue);
        $markersExt = [];

        $domain = \Sng\AdditionalReports\Utility::getDomain($itemValue['pid']);
        $markersExt['domain'] = \Sng\AdditionalReports\Utility::getIconDomain() . ' ' . $domain;

        $iconPage = ($itemValue['hiddenpages'] == 0) ? \Sng\AdditionalReports\Utility::getIconPage() : \Sng\AdditionalReports\Utility::getIconPage(true);
        $iconContent = ($itemValue['hiddentt_content'] == 0) ? \Sng\AdditionalReports\Utility::getIconContent() : \Sng\AdditionalReports\Utility::getIconContent(true);

        $markersExt['pid'] = $iconPage . ' ' . $itemValue['pid'];
        $markersExt['uid'] = $iconContent . ' ' . $itemValue['uid'];
        $markersExt['pagetitle'] = $itemValue['title'];

        $markersExt['usedtv'] = '';
        $markersExt['usedtvclass'] = '';

        $linkAtt = ['href' => '#', 'title' => \Sng\AdditionalReports\Utility::getLl('switch'), 'onclick' => \Sng\AdditionalReports\Utility::goToModuleList($itemValue['pid']), 'class' => 'btn btn-default'];
        $markersExt['db'] = \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebList());

        $linkAtt = ['href' => \Sng\AdditionalReports\Utility::goToModuleList($itemValue['pid'], true), 'target' => '_blank', 'title' => \Sng\AdditionalReports\Utility::getLl('newwindow'), 'class' => 'btn btn-default'];
        $markersExt['db'] .= \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebList());

        $linkAtt = ['href' => '#', 'title' => \Sng\AdditionalReports\Utility::getLl('switch'), 'onclick' => \Sng\AdditionalReports\Utility::goToModulePage($itemValue['pid']), 'class' => 'btn btn-default'];
        $markersExt['page'] = \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebPage());

        $linkAtt = ['href' => \Sng\AdditionalReports\Utility::goToModulePage($itemValue['pid'], true), 'target' => '_blank', 'title' => \Sng\AdditionalReports\Utility::getLl('newwindow'), 'class' => 'btn btn-default'];
        $markersExt['page'] .= \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebPage());

        // todo better handle that
        $markersExt['preview'] = 'http://' . $domain . '/index.php?id=' . $itemValue['pid'];

//        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($markersExt);

        return $markersExt;
    }
}
