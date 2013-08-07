<?php
class AllPostmarkPluginTest extends PHPUnit_Framework_TestSuite {

/**
 * Suite define the tests for this suite
 *
 * @return void
 */
	public static function suite() {
		$suite = new PHPUnit_Framework_TestSuite('All Postmark Plugin Tests');

		$basePath = CakePlugin::path('Postmark') . DS . 'Test' . DS . 'Case' . DS;

		$suite->addTestFile($basePath . 'Network' . DS . 'Email' . DS . 'PostmarkTransportTest.php');
		$suite->addTestFile($basePath . 'Controller' . DS . 'PostmarkAppControllerTest.php');

		return $suite;
	}

}