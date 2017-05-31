<?php

class MainTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_Framework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new Tx_Phpunit_Framework('additional_reports');
	}

	/**
	 * @test
	 */
	public function getCss() {
		$str = \Sng\AdditionalReports\Main::getCss();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function getLl() {
//		$str = \Sng\AdditionalReports\Main::getLl('extensions_extensions');
//		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayXclass() {
		$str = \Sng\AdditionalReports\Main::displayXclass();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayAjax() {
		$str = \Sng\AdditionalReports\Main::displayAjax();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayCliKeys() {
		$str = \Sng\AdditionalReports\Main::displayCliKeys();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayEid() {
		$str = \Sng\AdditionalReports\Main::displayEid();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayExtDirect() {
		$str = \Sng\AdditionalReports\Main::displayExtDirect();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayExtensions() {
		$str = \Sng\AdditionalReports\Main::displayExtensions();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayHooks() {
		$str = \Sng\AdditionalReports\Main::displayHooks();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayStatus() {
		$str = \Sng\AdditionalReports\Main::displayStatus();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayPlugins() {
		$str = \Sng\AdditionalReports\Main::displayPlugins();
		$this->assertTrue(!empty($str));
		$_GET['display'] = 1;
		$str = \Sng\AdditionalReports\Main::displayPlugins();
		$this->assertTrue(!empty($str));
		$_GET['display'] = 3;
		$str = \Sng\AdditionalReports\Main::displayPlugins();
		$this->assertTrue(!empty($str));
		$_GET['display'] = 4;
		$str = \Sng\AdditionalReports\Main::displayPlugins();
		$this->assertTrue(!empty($str));
		$_GET['display'] = 5;
		$str = \Sng\AdditionalReports\Main::displayPlugins();
		$this->assertTrue(!empty($str));
		$_GET['display'] = 6;
		$str = \Sng\AdditionalReports\Main::displayPlugins();
		$this->assertTrue(!empty($str));
		$_GET['display'] = 7;
		$str = \Sng\AdditionalReports\Main::displayPlugins();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayRealUrlErrors() {
		$str = \Sng\AdditionalReports\Main::displayRealUrlErrors();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayLogErrors() {
		$str = \Sng\AdditionalReports\Main::displayLogErrors();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayWebsitesConf() {
		$str = \Sng\AdditionalReports\Main::displayWebsitesConf();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayDbCheck() {
		$str = \Sng\AdditionalReports\Main::displayDbCheck();
		$this->assertTrue(!empty($str));
	}
}
