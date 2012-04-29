<?php

/**
 * Tests for the Freeway class.
 *
 * @author			Doug Avery
 * @package			Freeway
 */

require_once PATH_THIRD .'freeway/ext.freeway.php';

class Freeway_tests extends Testee_unit_test_case {

	private $_props;
	private $_subject;


	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */

	public function setUp() {
		$this->_props = array(
		);
		echo 'SETUP!';

	}

	public function test__works() {
		echo 'works';
		$this->assertTrue(false);
	}

}

/* End of file			: test.freeway.php */
/* File location		: third_party/testee/tests/test.freeway.php */
