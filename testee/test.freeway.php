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
				$this->assertEqual(isset($this->f->EE->config->_global_vars['freeway_has_run']), $should_execute);
				$this->f->expectCallCount('prepare', $should_execute);
				$this->f->expectCallCount('route', $should_execute);
				$this->f->expectCallCount('output_freeway_data', $should_execute);
			}

			public function tests_constructor() {
				$this->constructor__calls_helper(false);
				$this->constructor__calls_helper(true);
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
				$this->assertEqual($this->f->log_data['foo'], 'bar');
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
				$this->assertEqual($this->f->get_site_name(Array()), '');
				$this->assertEqual($this->f->get_site_name(Array('foo')), 'foo');
			}

		/* @end */

		/* @group load_routes*/

			var $file_path = '/tmp/freeway_routes.php';

			public function load_routes__make_file($str) {
				$file = fopen($this->file_path, 'w');
				fwrite($file, $str);
				fclose($file);
			}

			public function load_routes__delete_file() {
				unlink($this->file_path);
			}

			public function tests_file_creation() {
				$this->load_routes__make_file('');
				$this->assertTrue(file_exists($this->file_path));
				$this->load_routes__delete_file();
				$this->assertFalse(file_exists($this->file_path));
			}

			public function tests_load_routes() {
				$this->load_routes__make_file('<?php return array("foo" => "bar");');
				$this->assertEqual($this->f->load_routes($this->file_path), Array('foo' => 'bar'));
				$this->load_routes__make_file('');
				$this->assertEqual($this->f->load_routes($this->file_path), Array());
				$this->assertEqual($this->f->load_routes(''), Array());
				$this->load_routes__delete_file();
			}

		/* @end */

	/* @end */

	/* @group managing params */

		/* @group store_original_uri */

			public function tests_store_original_uri(){
				$this->f->original_uri = 'one/two/three';
				$this->f->store_original_uri();
				$this->assertEqual($this->f->EE->config->_global_vars['freeway_1'], 'one');
				$this->assertEqual($this->f->EE->config->_global_vars['freeway_2'], 'two');
				$this->assertEqual($this->f->EE->config->_global_vars['freeway_3'], 'three');
				$this->assertEqual($this->f->EE->config->_global_vars['freeway_4'], '');
			}

		/* @end */

		/* @group remove_and_store_params */

			public function tests_remove_and_store_params(){
				$this->f->EE->uri->uri_string = 'one?foo=bar&bar=foo';
				$this->f->remove_and_store_params();
				$this->assertEqual($this->f->uri_params, '?foo=bar&bar=foo');
				$this->f->EE->uri->uri_string = 'one&foo=bar&bar=foo';
				$this->f->remove_and_store_params();
				$this->assertEqual($this->f->uri_params, '&foo=bar&bar=foo');
			}

		/* @end */

		/* @group restore_params */

			function tests_restore_params(){
				$this->f->EE->uri->uri_string = 'one';
				$this->f->uri_params = 'two';
				$this->f->restore_params();
				$this->assertEqual($this->f->EE->uri->uri_string, 'onetwo');
			}

		/* @end */

	/* @end */

	/* @group routing */

		/* @group convert_pattern_to_regex */

			function helper__convert_pattern_to_regex($pattern, $output) {
				$this->assertEqual($this->f->convert_pattern_to_regex($pattern), $output);
			}

			function tests_convert_pattern_to_regex(){
				$this->helper__convert_pattern_to_regex('foo', '#^foo($|/)#');
				$this->helper__convert_pattern_to_regex('foo/{{bar}}', '#^foo/.*?($|/)#');
				$this->helper__convert_pattern_to_regex('foo/{{bar}}/foo', '#^foo/.*?/foo($|/)#');
			}

		/* @end */

		/* @group uri_matches_pattern */

			function helper__uri_matches_pattern($uri, $pattern, $method = true) {
				$method = $method ? 'assertTrue' : 'assertFalse';
				$this->f->original_uri = $uri;
				$this->{$method}($this->f->uri_matches_pattern(Array($pattern => 'bar')));
			}

			function tests_uri_matches_pattern(){

				// doesn't match too much or too little
				$this->helper__uri_matches_pattern('foo', 'foo');
				$this->helper__uri_matches_pattern('fo', 'foo', false);
				$this->helper__uri_matches_pattern('ffoo', 'foo', false);
				$this->helper__uri_matches_pattern('fooo', 'foo', false);

				// matches start segments
				$this->helper__uri_matches_pattern('foo/bar', 'foo');
				$this->helper__uri_matches_pattern('fooo/bar', 'foo', false);
				$this->helper__uri_matches_pattern('bar/foo', 'foo', false);

				// doesn't match when only part of pattern
				$this->helper__uri_matches_pattern('foo', 'foo/bar', false);

				// matches tokens
				$this->helper__uri_matches_pattern('foo', '{{a}}');
				$this->helper__uri_matches_pattern('foo/bar', 'foo/{{a}}');
				$this->helper__uri_matches_pattern('foo/bar', 'foo/bar/{{a}}', false);
				$this->helper__uri_matches_pattern('foo/bar', '{{a}}/bar');
				$this->helper__uri_matches_pattern('foo/bar', '{{a}}/{{b}}');

			}

		/* @end */

		/* @group parse_new_uri_from_route */

			function helper__route($uri, $pattern, $route, $expectation, $method=true) {
				$method = $method ? 'assertEqual' : 'assertNotEqual';
				$this->f->uri_params = '';
				$this->f->original_uri = $uri;
				$this->f->pattern = $pattern;
				$this->f->route = $route;
				$this->f->parse_new_uri_from_route();
				$this->{$method}($this->f->EE->uri->uri_string, $expectation);
			}

			function tests_parse_new_uri_from_route() {

				// Basics
				$this->helper__route('a', 'a', 'b', 'b');
				$this->helper__route('a', 'a', 'b', 'a', false);
				$this->helper__route('a/b/c/d', 'a', 'b', 'b');

				// token replacement
				$this->helper__route('a', '{{foo}}', '{{foo}}', 'a');
				$this->helper__route('a/b', 'a/{{foo}}', '{{foo}}/a', 'b/a');
				$this->helper__route('a/b/c', 'a/{{foo}}', '{{foo}}/{{foo}}/{{foo}}', 'b/b/b');
				$this->helper__route('a/b/c', '{{foo}}/{{bar}}/', 'c/{{bar}}', 'c/b');

			}

		/* @end */

		/* @group rebuild_uri_for_parsing */

			function tests_rebuild_uri_for_parsing() {
				$this->f->EE->uri->uri_string = 'foo/bar';
				$this->f->rebuild_uri_for_parsing();
				$this->f->EE->uri->expectOnce('_explode_segments', false);
				$this->f->EE->uri->expectOnce('_reindex_segments', false);
			}

		/* @end */

	/* @end */

}

/* End of file			: test.freeway.php */
/* File location		: third_party/testee/tests/test.freeway.php */
