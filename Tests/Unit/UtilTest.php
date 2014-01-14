<?php

class UtilTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_Framework
	 */
	private $testingFramework;

	public function setUp() {
		require_once(PATH_typo3 . 'template.php');
		$this->testingFramework = new Tx_Phpunit_Framework('additional_reports');
	}

	/**
	 * @test
	 */
	public function getReportsList() {
		$this->testArray(tx_additionalreports_util::getReportsList());
	}

	/**
	 * @test
	 */
	public function getBaseUrl() {
		$this->assertFalse(filter_var(tx_additionalreports_util::getBaseUrl(), FILTER_VALIDATE_URL) === FALSE);
	}

	/**
	 * @test
	 */
	public function getSubModules() {
		$this->testArray(tx_additionalreports_util::getSubModules());
	}

	/**
	 * @test
	 */
	public function getTreeList() {
		$tree = explode(',', tx_additionalreports_util::getTreeList(1, 1));
		$this->testArray($tree);
	}

	/**
	 * @test
	 */
	public function getCountPagesUids() {
		$this->assertTrue(tx_additionalreports_util::getCountPagesUids('1,47') > 0);
	}

	/**
	 * @test
	 */
	public function isUsedInTv() {
		if (!defined('TYPO3_cliMode')) {
			$this->assertTrue(tx_additionalreports_util::isUsedInTv(192, 6));
			$this->assertFalse(tx_additionalreports_util::isUsedInTv(99999, 6));
		}
	}

	/**
	 * @test
	 */
	public function intFromVer() {
		$this->assertTrue(tx_additionalreports_util::intFromVer('4.5.5') == '4005005');
	}

	/**
	 * @test
	 */
	public function splitVersionRange() {
		$split = tx_additionalreports_util::splitVersionRange('4.5.5-4.5.6');
		$this->assertTrue($split[0] == '4.5.5');
		$this->assertTrue($split[1] == '4.5.6');
		$split = tx_additionalreports_util::splitVersionRange('4.5.5');
		$this->assertTrue($split[0] == '4.5.5');
		$this->assertTrue($split[1] == '0.0.0');
		$split = tx_additionalreports_util::splitVersionRange('-4.5.5');
		$this->assertTrue($split[0] == '0.0.0');
		$this->assertTrue($split[1] == '4.5.5');
	}

	/**
	 * @test
	 */
	public function getInstExtList() {
		$dbSchema = tx_additionalreports_util::getDatabaseSchema();
		$allExtension = tx_additionalreports_util::getInstExtList(PATH_typo3conf . 'ext/', $dbSchema);
		$this->testArray($allExtension);
		$this->testArray($allExtension['ter']);
		$this->testArray($allExtension['dev']);
		$this->testArray($allExtension['unloaded']);
		unset($dbSchema);
		unset($allExtension);
	}

	/**
	 * @test
	 */
	public function includeEMCONF() {
		$emconf = tx_additionalreports_util::includeEMCONF(PATH_typo3conf . 'ext/additional_reports/ext_emconf.php', 'additional_reports');
		$this->testArray($emconf);
	}

	/**
	 * @test
	 */
	public function checkExtensionUpdate() {
		$extkey['extkey'] = 'additional_reports';
		$this->testArray(tx_additionalreports_util::checkExtensionUpdate($extkey));
	}

	/**
	 * @test
	 */
	public function findMD5ArrayDiff() {
		$emconf = tx_additionalreports_util::includeEMCONF(PATH_typo3conf . 'ext/additional_reports/ext_emconf.php', 'additional_reports');
		$new = unserialize($emconf['_md5_values_when_last_written']);
		unset($new['ChangeLog']);
		$this->testArray(tx_additionalreports_util::findMD5ArrayDiff($new, unserialize($emconf['_md5_values_when_last_written'])));
	}

	/**
	 * @test
	 */
	public function getFilesMDArray() {
		$extInfo['extkey'] = 'additional_reports';
		$extInfo['type'] = 'L';
		$emconf = tx_additionalreports_util::includeEMCONF(PATH_typo3conf . 'ext/additional_reports/ext_emconf.php', 'additional_reports');
		$new = unserialize($emconf['_md5_values_when_last_written']);
		unset($new['ChangeLog']);
		$emconf['_md5_values_when_last_written'] = serialize($new);
		$extInfo['EM_CONF'] = $emconf;
		$this->testArray(tx_additionalreports_util::getExtAffectedFiles($extInfo));
	}

	/**
	 * @test
	 */
	public function typePath() {
		$this->assertTrue(tx_additionalreports_util::typePath('S') == PATH_typo3 . 'sysext/');
		$this->assertTrue(tx_additionalreports_util::typePath('G') == PATH_typo3 . 'ext/');
		$this->assertTrue(tx_additionalreports_util::typePath('L') == PATH_typo3conf . 'ext/');
	}

	/**
	 * @test
	 */
	public function getExtIcon() {
		$this->assertTrue(tx_additionalreports_util::getExtIcon('additional_reports') == t3lib_div::getIndpEnv('TYPO3_SITE_URL') . 'typo3conf/ext/additional_reports/ext_icon.gif');
	}

	/**
	 * @test
	 */
	public function getIconZoom() {
		$this->assertTrue(tx_additionalreports_util::getIconZoom() == '<img src="' . t3lib_div::getIndpEnv('TYPO3_SITE_URL') . 'typo3/sysext/t3skin/icons/gfx/zoom.gif"/>');
	}

	/**
	 * @test
	 */
	public function getIconDomain() {
		$this->assertTrue(tx_additionalreports_util::getIconDomain() == '<img src="' . t3lib_div::getIndpEnv('TYPO3_SITE_URL') . 'typo3/sysext/t3skin/icons/gfx/i/domain.gif"/>');
	}

	/**
	 * @test
	 */
	public function getIconWebPage() {
		$this->assertTrue(tx_additionalreports_util::getIconWebPage() == '<img src="' . t3lib_div::getIndpEnv('TYPO3_SITE_URL') . 'typo3/sysext/t3skin/icons/module_web_layout.gif"/>');
	}

	/**
	 * @test
	 */
	public function getIconWebList() {
		$this->assertTrue(tx_additionalreports_util::getIconWebList() == '<img src="' . t3lib_div::getIndpEnv('TYPO3_SITE_URL') . 'typo3/sysext/t3skin/icons/module_web_list.gif"/>');
	}

	/**
	 * @test
	 */
	public function getIconPage() {
		$this->assertTrue(tx_additionalreports_util::getIconPage() == '<img src="' . t3lib_div::getIndpEnv('TYPO3_SITE_URL') . 'typo3/sysext/t3skin/icons/gfx/i/pages.gif"/>');
		$this->assertTrue(tx_additionalreports_util::getIconPage(TRUE) == '<img src="' . t3lib_div::getIndpEnv('TYPO3_SITE_URL') . 'typo3/sysext/t3skin/icons/gfx/i/pages__h.gif"/>');
	}

	/**
	 * @test
	 */
	public function getIconContent() {
		$this->assertTrue(tx_additionalreports_util::getIconContent() == '<img src="' . t3lib_div::getIndpEnv('TYPO3_SITE_URL') . 'typo3/sysext/t3skin/icons/gfx/i/tt_content.gif"/>');
		$this->assertTrue(tx_additionalreports_util::getIconContent(TRUE) == '<img src="' . t3lib_div::getIndpEnv('TYPO3_SITE_URL') . 'typo3/sysext/t3skin/icons/gfx/i/tt_content__h.gif"/>');
	}

	/**
	 * @test
	 */
	public function getExtensionType() {
		$ext = tx_additionalreports_util::getExtensionType('additional_reports');
		$this->assertTrue($ext['type'] == 'L');
		$this->assertTrue($ext['siteRelPath'] == 'typo3conf/ext/additional_reports/');
		$this->assertTrue($ext['typo3RelPath'] == '../typo3conf/ext/additional_reports/');
	}

	/**
	 * @test
	 */
	public function getRootLine() {
		$this->testArray(tx_additionalreports_util::getRootLine(1));
	}

	/**
	 * @test
	 */
	public function getDomain() {
		$domain = tx_additionalreports_util::getDomain(1);
		$this->assertTrue(!empty($domain));
	}

	/**
	 * @test
	 */
	public function getExtPath() {
		$this->assertTrue(tx_additionalreports_util::getExtPath('additional_reports') == PATH_typo3conf . 'ext/additional_reports/');
	}

	/**
	 * @test
	 */
	public function getSqlUpdateStatements() {
		$this->testArray(tx_additionalreports_util::getSqlUpdateStatements());
	}

	/**
	 * @test
	 */
	public function getExtSqlUpdateStatements() {
		$dbSchema = tx_additionalreports_util::getDatabaseSchema();
		$allExtension = tx_additionalreports_util::getInstExtList(PATH_typo3conf . 'ext/', $dbSchema);
		$this->testArray($allExtension['ter']['tt_news']['fdfile']['tt_news']['fields']);
	}

	/**
	 * @test
	 */
	public function getInstallSqlClass() {
		$class = tx_additionalreports_util::getInstallSqlClass();
		$this->assertTrue(!empty($class));
	}

	/**
	 * @test
	 */
	public function getDatabaseSchema() {
		$this->testArray(tx_additionalreports_util::getDatabaseSchema());
	}

	/**
	 * @test
	 */
	public function versionCompare() {
		$msg = tx_additionalreports_util::versionCompare('4.5.0-4.5.1');
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function viewArray() {
		$msg = tx_additionalreports_util::viewArray(array());
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function goToModuleList() {
		$msg = tx_additionalreports_util::goToModuleList(1);
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function goToModulePage() {
		$msg = tx_additionalreports_util::goToModulePage(1);
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function goToModulePageTv() {
		$msg = tx_additionalreports_util::goToModulePageTv(1);
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function goToModuleEm() {
		$msg = tx_additionalreports_util::goToModuleEm('additional_reports');
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function generateLink() {
		$msg = tx_additionalreports_util::generateLink(array('href' => '#'), 'link');
		$this->assertTrue($msg == '<a href="#">link</a>');
	}

	/**
	 * @test
	 */
	public function getExtensionVersion() {
		$msg = tx_additionalreports_util::getExtensionVersion('additional_reports');
		$emconf = tx_additionalreports_util::includeEMCONF(PATH_typo3conf . 'ext/additional_reports/ext_emconf.php', 'additional_reports');
		$this->assertTrue($msg == $emconf['version']);
	}

	/**
	 * @test
	 */
	public function getCacheFilePrefix() {
		$msg = tx_additionalreports_util::getCacheFilePrefix();
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function getMySqlCacheInformations() {
		$msg = tx_additionalreports_util::getMySqlCacheInformations();
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function getMySqlCharacterSet() {
		$msg = tx_additionalreports_util::getMySqlCharacterSet();
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function writeInformation() {
		$msg = tx_additionalreports_util::writeInformation('foo', 'bar');
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function writeInformationList() {
		$msg = tx_additionalreports_util::writeInformationList('foo', array());
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function writePopUp() {
		$msg = tx_additionalreports_util::writePopUp('foo', 'bar', 'foo');
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function getAllDifferentPlugins() {
		$this->testArray(tx_additionalreports_util::getAllDifferentPlugins('AND 1=1 '));
	}

	/**
	 * @test
	 */
	public function getAllDifferentPluginsSelect() {
		$msg = tx_additionalreports_util::getAllDifferentPluginsSelect(TRUE);
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function getAllDifferentCtypes() {
		$this->testArray(tx_additionalreports_util::getAllDifferentCtypes('AND 1=1 '));
	}

	/**
	 * @test
	 */
	public function getAllDifferentCtypesSelect() {
		$msg = tx_additionalreports_util::getAllDifferentCtypesSelect(TRUE);
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function getAllPlugins() {
		$this->testArray(tx_additionalreports_util::getAllPlugins('AND 1=1 '));
	}

	/**
	 * @test
	 */
	public function getAllCtypes() {
		$this->testArray(tx_additionalreports_util::getAllCtypes('AND 1=1 '));
	}

	/**
	 * TODO: XCLASS for 6.0>
	 */

	/**
	 * @test
	 */
	public function getJsonVersionInfos() {
		$this->testArray(tx_additionalreports_util::getJsonVersionInfos());
	}

	/**
	 * @test
	 */
	public function getCurrentVersionInfos() {
		$jsonVersions = tx_additionalreports_util::getJsonVersionInfos();
		$this->testArray(tx_additionalreports_util::getCurrentVersionInfos($jsonVersions));
	}

	/**
	 * @test
	 */
	public function getCurrentBranchInfos() {
		$jsonVersions = tx_additionalreports_util::getJsonVersionInfos();
		$this->testArray(tx_additionalreports_util::getCurrentBranchInfos($jsonVersions));
	}

	/**
	 * @test
	 */
	public function getLatestStableInfos() {
		$jsonVersions = tx_additionalreports_util::getJsonVersionInfos();
		$this->testArray(tx_additionalreports_util::getLatestStableInfos($jsonVersions));
	}

	/**
	 * @test
	 */
	public function getLatestLtsInfos() {
		$jsonVersions = tx_additionalreports_util::getJsonVersionInfos();
		$this->testArray(tx_additionalreports_util::getLatestLtsInfos($jsonVersions));
	}

	/**
	 * @test
	 */
	public function downloadT3x() {
		$content = tx_additionalreports_util::downloadT3x('additional_reports', tx_additionalreports_util::getExtensionVersion('additional_reports'), 'ext_tables.php');
		$this->assertTrue(!empty($content));
	}

	/**
	 * @test
	 */
	public function initTSFE() {
		tx_additionalreports_util::initTSFE(0);
		$this->assertTrue(!empty($GLOBALS['TSFE']));
	}

	/*
	 * @test
	 */
	public function testArray($array = array('foo')) {
		$this->assertTrue(is_array($array));
		$this->assertTrue(count($array) > 0);
	}


}