<?php
require_once TESTS_PATH . '/application/ControllerTestCase.php';
/**
 * Description of IndexControllerTest
 *
 * @author Darshan
 */
class IndexControllerTest extends ControllerTestCase {

	public function testIndex() {

		$this->dispatch('/index/forms');

		$this->assertModule('default');
		$this->assertController('index');
		$this->assertAction('forms');
	}

}