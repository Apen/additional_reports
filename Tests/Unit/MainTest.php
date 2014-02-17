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
		$str = tx_additionalreports_main::getCss();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function getLl() {
//		$str = tx_additionalreports_main::getLl('extensions_extensions');
//		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayXclass() {
		$str = tx_additionalreports_main::displayXclass();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayAjax() {
		$str = tx_additionalreports_main::displayAjax();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayCliKeys() {
		$str = tx_additionalreports_main::displayCliKeys();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayEid() {
		$str = tx_additionalreports_main::displayEid();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayExtDirect() {
		$str = tx_additionalreports_main::displayExtDirect();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayExtensions() {
		$str = tx_additionalreports_main::displayExtensions();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayHooks() {
		$str = tx_additionalreports_main::displayHooks();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayStatus() {
		$str = tx_additionalreports_main::displayStatus();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayPlugins() {
		$str = tx_additionalreports_main::displayPlugins();
		$this->assertTrue(!empty($str));
		$_GET['display'] = 1;
		$str = tx_additionalreports_main::displayPlugins();
		$this->assertTrue(!empty($str));
		$_GET['display'] = 3;
		$str = tx_additionalreports_main::displayPlugins();
		$this->assertTrue(!empty($str));
		$_GET['display'] = 4;
		$str = tx_additionalreports_main::displayPlugins();
		$this->assertTrue(!empty($str));
		$_GET['display'] = 5;
		$str = tx_additionalreports_main::displayPlugins();
		$this->assertTrue(!empty($str));
		$_GET['display'] = 6;
		$str = tx_additionalreports_main::displayPlugins();
		$this->assertTrue(!empty($str));
		$_GET['display'] = 7;
		$str = tx_additionalreports_main::displayPlugins();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayRealUrlErrors() {
		$str = tx_additionalreports_main::displayRealUrlErrors();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayLogErrors() {
		$str = tx_additionalreports_main::displayLogErrors();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayWebsitesConf() {
		$str = tx_additionalreports_main::displayWebsitesConf();
		$this->assertTrue(!empty($str));
	}

	/**
	 * @test
	 */
	public function displayDbCheck() {
		$str = tx_additionalreports_main::displayDbCheck();
		$this->assertTrue(!empty($str));
	}
}
