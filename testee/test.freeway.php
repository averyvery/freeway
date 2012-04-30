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

	/* @group helpers*/

		public function setUp() {
			parent::setUp();
			$this->f = new Freeway_ext();
		}

		public function tearDown() {
			unset($this->f);
			unset($this->m);
		}

		public function mock(){
			$methods = func_get_args();
			$title = array_shift($methods);
			$class_name = 'MockFor' . $title . 'Test';
			Mock::generatePartial(
				'Freeway_ext',
				$class_name,
				$methods
			);
			$this->f = new $class_name();
		}

		public function unset_vars($vars) {
			foreach($vars as $var){
				unset($this->f->{$var});
			}
		}

		public function assert_vars($vars, $method) {
			foreach($vars as $var){
				$this->{$method}(isset($this->f->{$var}));
			}
		}

	/* @end */

	/* @group init */

		/* @group constructor */

			public function constructor__calls_helper($should_execute) {
				$this->mock('Constructor', 'set_vars', 'should_execute', 'prepare', 'route', 'output_freeway_data');
				$this->f->returns('should_execute', $should_execute);
				$this->f->Freeway_ext();
				$this->f->expectOnce('set_vars', false);
				$this->f->expectOnce('should_execute', false);
				$this->f->expectCallCount('prepare', $should_execute);
				$this->f->expectCallCount('route', $should_execute);
				$this->f->expectOnce('output_freeway_data', false);
			}

			public function tests_constructor__calls() {
				$this->constructor__calls_helper(true);
				$this->constructor__calls_helper(false);
			}

		/* @end */

		/* @group set vars */

			public function tests_set_vars() {
				$vars = array('EE', 'log', 'vars', 'notices', 'routes');
				$this->unset_vars($vars);
				$this->assert_vars($vars, 'assertFalse');
				$this->f->set_vars();
				$this->assert_vars($vars, 'assertTrue');
			}

		/* @end */

	/* @group should_execute */

			public function tests_should_execute__uri_string_detection() {
				$this->f->original_uri = 'xxx';
				$this->assertTrue($this->f->should_execute('PAGE'));
				$this->f->original_uri = '';
				$this->assertFalse($this->f->should_execute('PAGE'));
			}

			public function tests_should_execute__request_detection() {
				$this->f->original_uri = 'xxx';
				$this->assertFalse($this->f->should_execute('CP'));
				$this->assertFalse($this->f->should_execute('CP'));
			}

		/* @end */

		/* @group prepare */

			public function tests_prepare() {
				$this->mock('Prepare', 'store_original_uri', 'remove_and_store_params', 'load_routes');
				$this->f->returns('load_routes', array('foo', 'bar'));
				unset($this->m->routes);
				$this->assertFalse(isset($this->m->routes));
				$this->f->prepare();
				$this->f->expectOnce('store_original_uri', false);
				$this->f->expectOnce('remove_and_store_params', false);
				$this->assertEqual($this->f->routes, array('foo', 'bar'));
			}

		/* @end */

		/* @group route */

			public function route_helper($matches) {
				$this->f->returns('uri_matches_pattern', $matches);
				$this->route();
				$this->f->expectCallCount('parse_new_uri_from_route', $matches);
				$this->f->expectCallCount('rebuild_uri_for_parsing', $matches);
			}

			public function route() {
				$this->mock('Route', 'uri_matches_pattern', 'parse_new_uri_from_route', 'rebuild_uri_for_parsing');
				$this->route_helper(false);
				$this->route_helper(true);
			}

		/* @end */

	/* @end */

	/* @group feedback */

		/* @group log */

			public function tests_log() {
				$this->f->log('foo', 'bar');
				$this->assertEqual($this->f->log['foo'], 'bar');
			}

		/* @end */

		/* @group notice */

			public function tests_notice() {
				$this->f->notice('foo');
				$this->assertEqual($this->f->notices[0], 'foo');
			}

		/* @end */

		/* @group output_freeway_data */

			public function tests_output_freeway_data() {
				$this->f->EE->config->_global_vars['freeway_info'] = '';
				$this->f->output_freeway_data();
				$this->assertPattern('#Routes#', $this->f->EE->config->_global_vars['freeway_info']);
			}

		/* @end */

	/* @end */

	/* @group loading routes */

		/* @group get_site_name */

			public function tests_get_site_name() {
				$this->EE->db->expectCallCount('get', 0);
				$this->assertEqual($this->f->get_site_name(Array()), '');
				$this->assertEqual($this->f->get_site_name(Array('foo')), 'foo');
				$this->EE->db->expectCallCount('get', 2);
			}

		/* @end */

		/* @group load_routes*/

			public function tests_load_routes() {
				$this->assertTrue(false);
			}

		/* @end */

	/* @end */

	/* @group managing params */

		/* @group store_original_uri */

			public function tests_store_original_uri(){
				$this->assertTrue(false);
			}

		/* @end */

		/* @group remove_and_store_params */

			public function tests_remove_and_store_params(){
				$this->assertTrue(false);
			}

		/* @end */

		/* @group restore_params */

			function tests_restore_params(){
				$this->assertTrue(false);
			}

		/* @end */

	/* @end */

	/* @group routing */

		/* @group convert_pattern_to_regex */

			function tests_convert_pattern_to_regex(){
				$this->assertTrue(false);
			}

		/* @end */

		/* @group uri_matches_pattern */

			function tests_uri_matches_pattern(){
				$this->assertTrue(false);
			}

		/* @end */

		/* @group parse_new_uri_from_route */

			function tests_parse_new_uri_from_route(){
				$this->assertTrue(false);
			}

		/* @end */

		/* @group rebuild_uri_for_parsing */

			function tests_rebuild_uri_for_parsing(){
				$this->assertTrue(false);
			}

		/* @end */

	/* @end */

}

/* End of file			: test.freeway.php */
/* File location		: third_party/testee/tests/test.freeway.php */
