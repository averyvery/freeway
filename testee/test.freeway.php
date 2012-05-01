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
			$this->f->EE->uri->uri_string = '';
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
			$this->f->EE->uri->uri_string = '';
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

			public function tests__calls_right_methods_when_should_execute() {
				$this->constructor__calls_helper(false);
			}

			public function tests__calls_right_methods_when_shouldnt_execute() {
				$this->constructor__calls_helper(true);
			}

		/* @end */

	/* @group should_execute */

			public function tests__executes_when_page_and_uri() {
				$this->f->EE->uri->uri_string = 'xxx';
				$this->assertTrue($this->f->should_execute('PAGE'));
			}

			public function tests__doesnt_execute_when_page_and_no_uri() {
				$this->f->EE->uri->uri_string = '';
				$this->assertFalse($this->f->should_execute('PAGE'));
			}

			public function tests__doesnt_execute_when_cp_and_uri() {
				$this->f->uri = 'xxx';
				$this->assertFalse($this->f->should_execute('CP'));
			}

		/* @end */

		/* @group prepare */

			public function tests__calls_prep_methods() {
				$this->mock('Prepare', 'set_uri', 'remove_and_store_query_string', 'close_uri', 'store_uri', 'load_routes');
				$this->f->prepare();
				$this->f->expectOnce('set_uri', false);
				$this->f->expectOnce('remove_and_store_query_string', false);
				$this->f->expectOnce('close_uri', false);
				$this->f->expectOnce('store_uri', false);
				$this->f->expectOnce('load_routes', false);
			}

		/* @end */

		/* @group route */

			public function route_helper($matches) {
				$this->mock('Route', 'uri_matches_pattern', 'parse_new_uri_from_route', 'rebuild_uri_for_parsing');
				$this->f->returns('uri_matches_pattern', $matches);
				$this->f->route();
				$this->f->expectCallCount('parse_new_uri_from_route', $matches);
				$this->f->expectCallCount('rebuild_uri_for_parsing', $matches);
			}

			public function tests__calls_route_methods_when_uri_matches() {
				$this->route_helper(true);
			}

			public function tests__calls_route_methods_when_uri_doesnt_match() {
				$this->route_helper(false);
			}

		/* @end */

	/* @end */

	/* @group feedback */

		/* @group log */

			public function tests__logs_values() {
				$this->f->log('foo', 'bar');
				$this->assertEqual($this->f->log_data['foo'], 'bar');
			}

		/* @end */

		/* @group notice */

			public function tests__logs_notices() {
				$this->f->notice('foo');
				$this->assertEqual($this->f->notices[0], 'foo');
			}

		/* @end */

		/* @group output_freeway_data */

			public function tests__saves_data_as_freeway_info() {
				$this->f->EE->config->_global_vars['freeway_info'] = '';
				$this->f->output_freeway_data();
				$this->assertPattern('#Routes#', $this->f->EE->config->_global_vars['freeway_info']);
			}

		/* @end */

	/* @end */

	/* @group loading routes */

		/* @group get_site_name */

			public function tests__returns_blank_sitename_from_empty_array() {
				$this->assertEqual($this->f->get_site_name(Array()), '');
			}

			public function tests__returns_blank_sitename_from_full_array() {
				$this->assertEqual($this->f->get_site_name(Array('foo')), 'foo');
			}

		/* @end */

		/* @group load_routes*/

			var $file_path = '/tmp/freeway_routes.php';

			public function routes_make_file($str) {
				$file = fopen($this->file_path, 'w');
				fwrite($file, $str);
				fclose($file);
			}

			public function routes_delete_file() {
				unlink($this->file_path);
			}

			public function tests__helper_makes_file() {
				$this->routes_make_file('');
				$this->assertTrue(file_exists($this->file_path));
			}

			public function tests__helper_deletes_file() {
				$this->routes_delete_file();
				$this->assertFalse(file_exists($this->file_path));
			}

			public function tests__loads_blank_array_from_no_file() {
				$this->assertEqual($this->f->load_routes(''), Array());
			}

			public function tests__loads_array_from_valid_file() {
				$this->routes_make_file('<?php return array("foo" => "bar");');
				$this->assertEqual($this->f->load_routes($this->file_path), Array('foo' => 'bar'));
			}

			public function tests__loads_blank_array_from_blank_file() {
				$this->routes_make_file('');
				$this->assertEqual($this->f->load_routes($this->file_path), Array());
			}

			public function tests__deletes_file() {
				$this->routes_delete_file();
			}

		/* @end */

	/* @end */

	/* @group managing uri */

		/* @group set_uri */

			public function tests__sets_uri(){
				unset($this->f->uri);
				$this->f->EE->uri->uri_string = 'foo';
				$this->f->set_uri();
				$this->assertEqual($this->f->uri, 'foo');
			}

		/* @end */

		/* @group store_uri */

			public function tests__stores_uri_as_freeway_vars(){
				$this->f->uri = 'one/two/';
				$this->f->store_uri();
				$this->assertEqual($this->f->EE->config->_global_vars['freeway_1'], 'one');
				$this->assertEqual($this->f->EE->config->_global_vars['freeway_2'], 'two');
			}

			public function tests__stores_11_blank_vars_for_unmatched_segments(){
				$this->f->uri = 'one/';
				$this->f->store_uri();
				for ($i = 2; $i < 12; $i++) {
					$this->assertEqual($this->f->EE->config->_global_vars['freeway_' . $i], '');
				}
			}

		/* @end */

		/* @group close URI */

			public function tests__closes_uri(){
				$this->f->uri = 'foo';
				$this->f->close_uri();
				$this->assertEqual('foo/', $this->f->uri);
			}

			public function tests__doesnt_alter_closed_uri(){
				$this->f->uri = 'foo/';
				$this->f->close_uri();
				$this->assertEqual('foo/', $this->f->uri);
			}

		/* @end */

		/* @group remove_and_store_query_string */

			public function tests__removes_normal_query_string(){
				$this->f->EE->uri->uri_string = 'one?foo=bar&bar=foo';
				$this->f->remove_and_store_query_string();
				$this->assertEqual($this->f->query_string, '?foo=bar&bar=foo');
			}

			public function tests__removes_ampersand_query_string(){
				$this->f->EE->uri->uri_string = 'one&foo=bar&bar=foo';
				$this->f->remove_and_store_query_string();
				$this->assertEqual($this->f->query_string, '&foo=bar&bar=foo');
			}

			public function tests__removes_normal_query_string_from_slashed_uri(){
				$this->f->EE->uri->uri_string = 'one/?foo=bar&bar=foo';
				$this->f->remove_and_store_query_string();
				$this->assertEqual($this->f->query_string, '?foo=bar&bar=foo');
			}

			public function tests__removes_ampersand_query_string_from_slashed_uri(){
				$this->f->EE->uri->uri_string = 'one/&foo=bar&bar=foo';
				$this->f->remove_and_store_query_string();
				$this->assertEqual($this->f->query_string, '&foo=bar&bar=foo');
			}

		/* @end */

		/* @group restore_query_string */

			function tests__restores_query_string() {
				$this->f->EE->uri->uri_string = 'one';
				$this->f->query_string = 'two';
				$this->f->restore_query_string();
				$this->assertEqual($this->f->EE->uri->uri_string, 'onetwo');
			}

		/* @end */

	/* @end */

	/* @group routing */

		/* @group convert_pattern_to_regex */

			function helper__convert_pattern_to_regex($pattern, $output) {
				$this->assertEqual($this->f->convert_pattern_to_regex($pattern), $output);
			}

			function tests__converts_simple_pattern(){
				$this->helper__convert_pattern_to_regex('foo', '#^foo($|/)#');
			}

			function tests__converts_tokened_pattern(){
				$this->helper__convert_pattern_to_regex('foo/{{bar}}', '#^foo/.*?($|/)#');
				$this->helper__convert_pattern_to_regex('foo/{{bar}}/foo', '#^foo/.*?/foo($|/)#');
			}

			function tests__converts_wildcard_pattern(){
				$this->helper__convert_pattern_to_regex('foo/*', '#^foo/.*$#');
				$this->helper__convert_pattern_to_regex('foo/{{bar}}/foo/*', '#^foo/.*?/foo/.*$#');
				$this->helper__convert_pattern_to_regex('foo/*/foo($|/)', '#^foo/*/foo($|/)#');
			}

		/* @end */

		/* @group uri_matches_pattern */

			function helper__uri_matches_pattern($uri, $pattern, $method = true) {
				$method = $method ? 'assertTrue' : 'assertFalse';
				$this->f->uri = $uri;
				$this->{$method}($this->f->uri_matches_pattern(Array($pattern => 'bar')));
			}

			function tests__uri_matches_exactly() {
				$this->helper__uri_matches_pattern('foo', 'foo');
				$this->helper__uri_matches_pattern('fo', 'foo', false);
				$this->helper__uri_matches_pattern('ffoo', 'foo', false);
				$this->helper__uri_matches_pattern('fooo', 'foo', false);
			}

			function tests__uri_matches_only_start_segments() {
				$this->helper__uri_matches_pattern('foo/bar', 'foo');
				$this->helper__uri_matches_pattern('fooo/bar', 'foo', false);
				$this->helper__uri_matches_pattern('bar/foo', 'foo', false);
			}

			function tests__uri_doesnt_match_partial() {
				$this->helper__uri_matches_pattern('foo', 'foo/bar', false);
			}

			function tests__uri_matches_tokens() {
				$this->helper__uri_matches_pattern('foo', '{{a}}');
				$this->helper__uri_matches_pattern('foo/bar', 'foo/{{a}}');
				$this->helper__uri_matches_pattern('foo/bar', 'foo/bar/{{a}}', false);
				$this->helper__uri_matches_pattern('foo/bar', '{{a}}/bar');
				$this->helper__uri_matches_pattern('foo/bar', '{{a}}/{{b}}');
			}

			function tests__uri_matches_wildecards() {
				$this->helper__uri_matches_pattern('foo/bar/foobar', '{{a}}/{{b}}/*');
				$this->helper__uri_matches_pattern('foo/bar', '{{a}}/{{b}}/*', false);
			}

		/* @end */

		/* @group parse_new_uri_from_route */

			function helper__route($uri, $pattern, $route, $expectation, $method=true) {
				$method = $method ? 'assertEqual' : 'assertNotEqual';
				$this->f->query_string = '';
				$this->f->uri = $uri;
				$this->f->pattern = $pattern;
				$this->f->route = $route;
				$this->f->parse_new_uri_from_route();
				$this->{$method}($this->f->EE->uri->uri_string, $expectation);
			}

			function tests__matches_simple_routes() {
				$this->helper__route('a', 'a', 'b', 'b');
				$this->helper__route('a', 'a', 'b', 'a', false);
				$this->helper__route('a/b/c/d', 'a', 'b', 'b');
			}

			function tests__replaces_tokes() {
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
