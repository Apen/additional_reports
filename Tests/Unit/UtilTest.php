<?php

class UtilTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_Framework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new Tx_Phpunit_Framework('additional_reports');
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::removeCacheFiles();
	}

	/**
	 * @test
	 */
	public function getReportsList() {
		$this->testArray(\Sng\AdditionalReports\Utility::getReportsList());
	}

	/**
	 * @test
	 */
	public function getBaseUrl() {
		$this->assertFalse(filter_var(\Sng\AdditionalReports\Utility::getBaseUrl(), FILTER_VALIDATE_URL) === FALSE);
	}

	/**
	 * @test
	 */
	public function getSubModules() {
		$this->testArray(\Sng\AdditionalReports\Utility::getSubModules());
	}

	/**
	 * @test
	 */
	public function getTreeList() {
		$tree = explode(',', \Sng\AdditionalReports\Utility::getTreeList(1, 1));
		$this->testArray($tree);
	}

	/**
	 * @test
	 */
	public function getCountPagesUids() {
		$this->assertTrue(\Sng\AdditionalReports\Utility::getCountPagesUids('1,47') > 0);
	}

	/**
	 * @test
	 */
	public function isUsedInTv() {
		// test only in my instance
		if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('templavoila') && PATH_site == '/home/html/dev/packagedev/') {
			$this->assertTrue(\Sng\AdditionalReports\Utility::isUsedInTv(192, 6));
			$this->assertFalse(\Sng\AdditionalReports\Utility::isUsedInTv(99999, 6));
		}
	}

	/**
	 * @test
	 */
	public function intFromVer() {
		$this->assertTrue(\Sng\AdditionalReports\Utility::intFromVer('4.5.5') == '4005005');
	}

	/**
	 * @test
	 */
	public function splitVersionRange() {
		$split = \Sng\AdditionalReports\Utility::splitVersionRange('4.5.5-4.5.6');
		$this->assertTrue($split[0] == '4.5.5');
		$this->assertTrue($split[1] == '4.5.6');
		$split = \Sng\AdditionalReports\Utility::splitVersionRange('4.5.5');
		$this->assertTrue($split[0] == '4.5.5');
		$this->assertTrue($split[1] == '0.0.0');
		$split = \Sng\AdditionalReports\Utility::splitVersionRange('-4.5.5');
		$this->assertTrue($split[0] == '0.0.0');
		$this->assertTrue($split[1] == '4.5.5');
	}

	/**
	 * @test
	 */
	public function getInstExtList() {
		$dbSchema = \Sng\AdditionalReports\Utility::getDatabaseSchema();
		$allExtension = \Sng\AdditionalReports\Utility::getInstExtList(PATH_typo3conf . 'ext/', $dbSchema);
		$this->testArray($allExtension);
		$this->testArray($allExtension['ter']);
		//$this->testArray($allExtension['dev']);
		//$this->testArray($allExtension['unloaded']);
		unset($dbSchema);
		unset($allExtension);
	}

	/**
	 * @test
	 */
	public function includeEMCONF() {
		$emconf = \Sng\AdditionalReports\Utility::includeEMCONF(PATH_typo3conf . 'ext/additional_reports/ext_emconf.php', 'additional_reports');
		$this->testArray($emconf);
	}

	/**
	 * @test
	 */
	public function checkExtensionUpdate() {
		$extkey['extkey'] = 'additional_reports';
		$this->testArray(\Sng\AdditionalReports\Utility::checkExtensionUpdate($extkey));
	}

	/**
	 * @test
	 */
	public function findMD5ArrayDiff() {
		$emconf = \Sng\AdditionalReports\Utility::includeEMCONF(PATH_typo3conf . 'ext/additional_reports/ext_emconf.php', 'additional_reports');
		$new = unserialize($emconf['_md5_values_when_last_written']);
		unset($new['ChangeLog']);
		$this->testArray(\Sng\AdditionalReports\Utility::findMD5ArrayDiff($new, unserialize($emconf['_md5_values_when_last_written'])));
	}

	/**
	 * @test
	 */
	public function getFilesMDArray() {
		$extInfo['extkey'] = 'additional_reports';
		$extInfo['type'] = 'L';
		$this->testArray(\Sng\AdditionalReports\Utility::getExtAffectedFiles($extInfo));
	}

	/**
	 * @test
	 */
	public function getFilesMDArrayFromT3x() {
		$this->testArray(\Sng\AdditionalReports\Utility::getFilesMDArrayFromT3x('additional_reports', '2.6.5'));
	}

	/**
	 * @test
	 */
	public function getExtAffectedFiles() {
		$extInfo['extkey'] = 'additional_reports';
		$extInfo['type'] = 'L';
		$emconf = \Sng\AdditionalReports\Utility::includeEMCONF(PATH_typo3conf . 'ext/additional_reports/ext_emconf.php', 'additional_reports');
		$new = unserialize($emconf['_md5_values_when_last_written']);
		unset($new['ChangeLog']);
		$emconf['_md5_values_when_last_written'] = serialize($new);
		$extInfo['EM_CONF'] = $emconf;
		$this->testArray(\Sng\AdditionalReports\Utility::getExtAffectedFiles($extInfo));
	}

	/**
	 * @test
	 */
	public function getExtAffectedFilesLastVersion() {
		$extInfo['extkey'] = 'additional_reports';
		$extInfo['type'] = 'L';
		$emconf = \Sng\AdditionalReports\Utility::includeEMCONF(PATH_typo3conf . 'ext/additional_reports/ext_emconf.php', 'additional_reports');
		$new = unserialize($emconf['_md5_values_when_last_written']);
		unset($new['ChangeLog']);
		$emconf['_md5_values_when_last_written'] = serialize($new);
		$extInfo['EM_CONF'] = $emconf;
		$extInfo['lastversion'] = \Sng\AdditionalReports\Utility::checkExtensionUpdate($extInfo);
		$this->testArray(\Sng\AdditionalReports\Utility::getExtAffectedFilesLastVersion($extInfo));
	}

	/**
	 * @test
	 */
	public function typePath() {
		$this->assertTrue(\Sng\AdditionalReports\Utility::typePath('S') == PATH_typo3 . 'sysext/');
		$this->assertTrue(\Sng\AdditionalReports\Utility::typePath('G') == PATH_typo3 . 'ext/');
		$this->assertTrue(\Sng\AdditionalReports\Utility::typePath('L') == PATH_typo3conf . 'ext/');
	}

	/**
	 * @test
	 */
	public function getExtIcon() {
		$this->assertTrue(\Sng\AdditionalReports\Utility::getExtIcon('additional_reports') == \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . 'typo3conf/ext/additional_reports/ext_icon.gif');
	}

	/**
	 * @test
	 */
	public function getIconZoom() {
		$this->assertTrue(\Sng\AdditionalReports\Utility::getIconZoom() == '<img src="' . \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . 'typo3/sysext/t3skin/icons/gfx/zoom.gif"/>');
	}

	/**
	 * @test
	 */
	public function getIconDomain() {
		$this->assertTrue(\Sng\AdditionalReports\Utility::getIconDomain() == '<img src="' . \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . 'typo3/sysext/t3skin/icons/gfx/i/domain.gif"/>');
	}

	/**
	 * @test
	 */
	public function getIconWebPage() {
		$this->assertTrue(\Sng\AdditionalReports\Utility::getIconWebPage() == '<img src="' . \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . 'typo3/sysext/t3skin/icons/module_web_layout.gif"/>');
	}

	/**
	 * @test
	 */
	public function getIconWebList() {
		$this->assertTrue(\Sng\AdditionalReports\Utility::getIconWebList() == '<img src="' . \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . 'typo3/sysext/t3skin/icons/module_web_list.gif"/>');
	}

	/**
	 * @test
	 */
	public function getIconPage() {
		$this->assertTrue(\Sng\AdditionalReports\Utility::getIconPage() == '<img src="' . \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . 'typo3/sysext/t3skin/icons/gfx/i/pages.gif"/>');
		$this->assertTrue(\Sng\AdditionalReports\Utility::getIconPage(TRUE) == '<img src="' . \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . 'typo3/sysext/t3skin/icons/gfx/i/pages__h.gif"/>');
	}

	/**
	 * @test
	 */
	public function getIconContent() {
		$this->assertTrue(\Sng\AdditionalReports\Utility::getIconContent() == '<img src="' . \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . 'typo3/sysext/t3skin/icons/gfx/i/tt_content.gif"/>');
		$this->assertTrue(\Sng\AdditionalReports\Utility::getIconContent(TRUE) == '<img src="' . \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . 'typo3/sysext/t3skin/icons/gfx/i/tt_content__h.gif"/>');
	}

	/**
	 * @test
	 */
	public function getExtensionType() {
		$ext = \Sng\AdditionalReports\Utility::getExtensionType('additional_reports');
		$this->assertTrue($ext['type'] == 'L');
		$this->assertTrue($ext['siteRelPath'] == 'typo3conf/ext/additional_reports/');
		$this->assertTrue($ext['typo3RelPath'] == '../typo3conf/ext/additional_reports/');
		$ext = \Sng\AdditionalReports\Utility::getExtensionType('reports');
		$this->assertTrue($ext['type'] == 'S');
		$this->assertTrue($ext['siteRelPath'] == 'typo3/sysext/reports/');
		$this->assertTrue($ext['typo3RelPath'] == 'sysext/reports/');
	}

	/**
	 * @test
	 */
	public function getRootLine() {
		$this->testArray(\Sng\AdditionalReports\Utility::getRootLine(1));
	}

	/**
	 * @test
	 */
	public function getDomain() {
		$domain = \Sng\AdditionalReports\Utility::getDomain(1);
		$this->assertTrue(!empty($domain));
		$domain = \Sng\AdditionalReports\Utility::getDomain(123456789);
		$this->assertTrue(!empty($domain));
	}

	/**
	 * @test
	 */
	public function getExtPath() {
		$this->assertTrue(\Sng\AdditionalReports\Utility::getExtPath('additional_reports') == PATH_typo3conf . 'ext/additional_reports/');
	}

	/**
	 * @test
	 */
	public function getSqlUpdateStatements() {
		$this->testArray(\Sng\AdditionalReports\Utility::getSqlUpdateStatements());
	}

	/**
	 * @test
	 */
	public function getExtSqlUpdateStatements() {
		$dbSchema = \Sng\AdditionalReports\Utility::getDatabaseSchema();
		$allExtension = \Sng\AdditionalReports\Utility::getInstExtList(PATH_typo3conf . 'ext/', $dbSchema);
		$this->testArray($allExtension['ter']['tt_news']['fdfile']['tt_news']['fields']);
	}

	/**
	 * @test
	 */
	public function getInstallSqlClass() {
		$class = \Sng\AdditionalReports\Utility::getInstallSqlClass();
		$this->assertTrue(!empty($class));
	}

	/**
	 * @test
	 */
	public function getDatabaseSchema() {
		$this->testArray(\Sng\AdditionalReports\Utility::getDatabaseSchema());
	}

	/**
	 * @test
	 */
	public function versionCompare() {
		$msg = \Sng\AdditionalReports\Utility::versionCompare('4.5.0-4.5.1');
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function viewArray() {
		$msg = \Sng\AdditionalReports\Utility::viewArray(array());
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function goToModuleList() {
		$msg = \Sng\AdditionalReports\Utility::goToModuleList(1);
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function goToModulePage() {
		$msg = \Sng\AdditionalReports\Utility::goToModulePage(1);
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function goToModulePageTv() {
		$msg = \Sng\AdditionalReports\Utility::goToModulePageTv(1);
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function generateLink() {
		$msg = \Sng\AdditionalReports\Utility::generateLink(array('href' => '#'), 'link');
		$this->assertTrue($msg == '<a href="#">link</a>');
	}

	/**
	 * @test
	 */
	public function getExtensionVersion() {
		$msg = \Sng\AdditionalReports\Utility::getExtensionVersion('additional_reports');
		$emconf = \Sng\AdditionalReports\Utility::includeEMCONF(PATH_typo3conf . 'ext/additional_reports/ext_emconf.php', 'additional_reports');
		$this->assertTrue($msg == $emconf['version']);
	}

	/**
	 * @test
	 */
	public function getCacheFilePrefix() {
		$msg = \Sng\AdditionalReports\Utility::getCacheFilePrefix();
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function getMySqlCacheInformations() {
		$msg = \Sng\AdditionalReports\Utility::getMySqlCacheInformations();
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function getMySqlCharacterSet() {
		$msg = \Sng\AdditionalReports\Utility::getMySqlCharacterSet();
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function writeInformation() {
		$msg = \Sng\AdditionalReports\Utility::writeInformation('foo', 'bar');
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function writeInformationList() {
		$msg = \Sng\AdditionalReports\Utility::writeInformationList('foo', array());
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function writePopUp() {
		$msg = \Sng\AdditionalReports\Utility::writePopUp('foo', 'bar', 'foo');
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function getAllDifferentPlugins() {
		$this->testArray(\Sng\AdditionalReports\Utility::getAllDifferentPlugins('AND 1=1 '));
	}

	/**
	 * @test
	 */
	public function getAllDifferentPluginsSelect() {
		$msg = \Sng\AdditionalReports\Utility::getAllDifferentPluginsSelect(TRUE);
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function getAllDifferentCtypes() {
		$this->testArray(\Sng\AdditionalReports\Utility::getAllDifferentCtypes('AND 1=1 '));
	}

	/**
	 * @test
	 */
	public function getAllDifferentCtypesSelect() {
		$msg = \Sng\AdditionalReports\Utility::getAllDifferentCtypesSelect(TRUE);
		$this->assertTrue(!empty($msg));
	}

	/**
	 * @test
	 */
	public function getAllPlugins() {
		$this->testArray(\Sng\AdditionalReports\Utility::getAllPlugins('AND 1=1 '));
	}

	/**
	 * @test
	 */
	public function getAllCtypes() {
		$this->testArray(\Sng\AdditionalReports\Utility::getAllCtypes('AND 1=1 '));
	}

	/**
	 * TODO: XCLASS for 6.0>
	 */

	/**
	 * @test
	 */
	public function getJsonVersionInfos() {
		$jsonVersions = \Sng\AdditionalReports\Utility::getJsonVersionInfos();
		$currentVersion = explode('.', '4.5.32');
		$this->testArray($jsonVersions);
		$this->testArray($jsonVersions[$currentVersion[0] . '.' . $currentVersion[1]]);
		$this->testArray($jsonVersions[$currentVersion[0] . '.' . $currentVersion[1]]['releases']);
		$this->testArray($jsonVersions[$currentVersion[0] . '.' . $currentVersion[1]]['releases']['4.5.32']);
	}

	/**
	 * @test
	 */
	public function getCurrentVersionInfos() {
		$jsonVersions = \Sng\AdditionalReports\Utility::getJsonVersionInfos();
		$this->testArray(\Sng\AdditionalReports\Utility::getCurrentVersionInfos($jsonVersions, '4.5.32'));
	}

	/**
	 * @test
	 */
	public function getCurrentBranchInfos() {
		$jsonVersions = \Sng\AdditionalReports\Utility::getJsonVersionInfos();
		$this->testArray(\Sng\AdditionalReports\Utility::getCurrentBranchInfos($jsonVersions, '4.5.32'));
	}

	/**
	 * @test
	 */
	public function getLatestStableInfos() {
		$jsonVersions = \Sng\AdditionalReports\Utility::getJsonVersionInfos();
		$this->testArray(\Sng\AdditionalReports\Utility::getLatestStableInfos($jsonVersions));
	}

	/**
	 * @test
	 */
	public function getLatestLtsInfos() {
		$jsonVersions = \Sng\AdditionalReports\Utility::getJsonVersionInfos();
		$this->testArray(\Sng\AdditionalReports\Utility::getLatestLtsInfos($jsonVersions));
	}

	/**
	 * @test
	 */
	public function extractExtensionDataFromT3x() {
		$content = \TYPO3\CMS\Core\Utility\GeneralUtility::getURL('http://typo3.org/fileadmin/ter/a/d/additional_reports_2.6.4.t3x');
		$testExplode = explode(':', $content, 3);
		$this->assertTrue(preg_match('/^[a-f0-9]{32}$/', $testExplode[0]) > 0);
		$this->assertTrue($testExplode[1] === 'gzcompress');
		$this->assertTrue(strlen($testExplode[2]) === 474613);
		$files = \Sng\AdditionalReports\Utility::extractExtensionDataFromT3x($content);
		$this->testArray($files['FILES']);
	}

	/**
	 * @test
	 */
	public function downloadT3x() {
		$content = \Sng\AdditionalReports\Utility::downloadT3x('additional_reports', '2.6.4', 'ext_tables.php');
		$this->assertTrue(!empty($content));
	}

	/**
	 * @test
	 */
	public function isGzuncompress() {
		$this->assertTrue(function_exists('gzuncompress'));
	}

	/**
	 * @test
	 */
	public function initTSFE() {
		if (!defined('TYPO3_cliMode')) {
			\Sng\AdditionalReports\Utility::initTSFE(1);
			$this->assertTrue(!empty($GLOBALS['TSFE']));
		}
	}

	/*
	 * @test
	 */
	public function testArray($array = array('foo')) {
		$this->assertTrue(is_array($array));
		$this->assertTrue(count($array) > 0);
	}


}