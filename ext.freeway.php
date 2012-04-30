<?php

/**
 * Freeway Extension Class for ExpressionEngine 2
 *
 * @package Freeway
 * @author Doug Avery <doug.avery@viget.com>
 * @link https://github.com/averyvery/freeway/
 */

	/*

	*/

class Freeway_ext {

	/* @group required vars */

		var $name = 'Freeway';
		var $description = 'A routing system for EE';
		var $version = '0.1';
		var $settings_exist = 'n';
		var $docs_url = 'http://github.com/averyvery/Freeway#readme';

	/* @end */

	/* @group init */

		var $log_data = Array();
		var $vars = Array();
		var $routes = Array();
		var $notices = Array();

		function Freeway_ext() {
			$this->EE =& get_instance();
			$this->original_uri = $this->EE->uri->uri_string;
			if($this->should_execute(REQ)){
				$this->EE->config->_global_vars['freeway_has_run'] = true;
				$this->prepare();
				$this->route();
				$this->output_freeway_data();
			}
		}

		function should_execute($request) {
			return
				!isset($this->EE->config->_global_vars['freeway_has_run']) &&
				$this->original_uri != '' &&
				$request === 'PAGE';
		}

		function prepare() {
			$this->store_original_uri();
			$this->remove_and_store_params();
			$this->routes = $this->load_routes(APPPATH . 'config/freeway_routes.php');
		}

		function route() {
			if($this->uri_matches_pattern($this->routes)){
				$this->parse_new_uri_from_route();
				$this->rebuild_uri_for_parsing();
			};
		}

	/* @end */

	/* @group feedback */

		function log($title, $value){
			$this->log_data[$title] = $value;
		}

		function notice($str){
			array_push($this->notices, $str);
		}

		function output_freeway_data(){

			$output = '<div style="font-size: 11px; font-family: sans-serif;">';
			$output .= '<h3>Notices:</h3>';
			$output .= '<ul>';
			foreach($this->notices as $notice){
				$output .= '<li>';
				$output .= $notice;
				$output .= '</li>';
			}
			$output .= '</ul>';
			$output .= '<h3>Routes:</h3>';
			$output .= '<ul>';
			foreach($this->routes as $match => $route){
				$output .= '<li>';
				$output .= $match . ' => ' . $route;
				$output .= '</li>';
			}
			$output .= '</ul>';
			$output .= '<h3>Vars:</h3>';
			$output .= '<ul>';
			foreach($this->vars as $key => $value){
				$output .= '<li>';
				$output .= $key . ' => ' . $value;
				$output .= '</li>';
			}
			$output .= '</ul>';
			$output .= '<h3>Routed data:</h3>';
			$output .= '<ul>';
			foreach($this->log_data as $title => $value){
				$output .= '<li>';
				$output .= $title . ' => ' . $value;
				$output .= '</li>';
			}
			$output .= '</ul>';
			$output .= '</div>';

			$this->EE->config->_global_vars['freeway_info'] = $output;

		}

	/* @end */

	/* @group loading routes */

		function get_site_name($array = ''){

			$site_name = '';
			$results = Array();

			if(is_array($array)){
					$results = $array;
			} else {
				$query = $this->EE->db->select('site_name')
					->from('sites')
					->where('site_id', $this->EE->config->item('site_id'))
					->get();
				if(isset($query)){
					$results = $query->row();
				}
			}

			foreach($results as $row){
				$site_name = $row;
			}

			return $site_name;

		}

		function load_routes($path) {

			$site_name = $this->get_site_name();
			$routes = '';

			if(file_exists($path)){
				$routes = include($path);
			}

			if(!is_array($routes)) {
				if(file_exists($path)){
					$this->notice('freeway_routes.php is invalid');
				} else {
					$this->notice('freeway_routes.php is missing');
				}
				$routes = Array();
			}

			if(isset($routes[$site_name]) && is_array($routes[$site_name])){
				$this->notice('Routes namespaced to site: ' . $site_name);
				$routes = $routes[$site_name];
			} else {
				$this->notice('No route group matches ' . $site_name . ' in the routes. Assuming all routes are global.');
			}

			return $routes;

		}

	/* @end */

	/* @group managing params */

		function store_original_uri(){
			$uri_array = explode('/', $this->original_uri);
			for($i = 0; $i < 11; $i++){
				$value = (isset($uri_array[$i])) ? $uri_array[$i] : '';
				$count = $i + 1;
				$this->EE->config->_global_vars['freeway_' . $count] = $value;
			}
			$this->log('Original URI', $this->original_uri);
		}

		function remove_and_store_params(){
			$this->param_pattern  = '#(';    // begin match group
			$this->param_pattern .=   '\?';    // match a '?';
			$this->param_pattern .=   '|';   // OR
			$this->param_pattern .=   '\&';    // match a '&';
			$this->param_pattern .= ')';    // end match group
			$this->param_pattern .= '.*$';   // continue matching characters until end of string
			$this->param_pattern .= '#';    // end match
			$matches = Array();
			preg_match($this->param_pattern, $this->EE->uri->uri_string, $matches);
			$this->uri_params = (isset($matches[0])) ? $matches[0] : '';
			$this->EE->uri->uri_string = preg_replace($this->param_pattern, '', $this->EE->uri->uri_string);
		}

		function restore_params(){
			$this->EE->uri->uri_string .= $this->uri_params;
		}

	/* @end */

	/* @group routing */

		function convert_pattern_to_regex($pattern){
			$regex = preg_replace('#\{\{.*?\}\}#', '.*?', $pattern);
			return '#^' . $regex . '($|/)#';
		}

		function uri_matches_pattern($routes){

			$match_occurred = false;
			$reversed_routes = array_reverse($routes);

			foreach($reversed_routes as $pattern => $route){

				$pattern_matches = Array();
				$pattern_match_regex =	$this->convert_pattern_to_regex($pattern);

				preg_match($pattern_match_regex, $this->original_uri, $pattern_matches);
				$match_occurred = isset($pattern_matches[0]);

				if($match_occurred){
					$this->log('Matched Pattern', $pattern);
					$this->log('Matched Route', $route);
					$this->route = $route;
					$this->pattern = $pattern;
					break;
				}

			};

			return $match_occurred;

		}

		function parse_new_uri_from_route(){

			$uri_segments = explode('/', $this->original_uri);
			$pattern_segments = explode('/', $this->pattern);
			$route_segments = explode('/', $this->route);
			$tokens = Array();

			for($i = 0; $i < count($route_segments); $i++){
				preg_match('#^\{\{.*?\}\}$#', $route_segments[$i], $tokens);
				if(isset($tokens[0])){
					$replacement = '';
					$key = '';
					for($j = 0; $j < count($pattern_segments); $j++){
						if($route_segments[$i] == $pattern_segments[$j]){
							$replacement = $uri_segments[$j];
							$key = $pattern_segments[$j];
						}
					}
					$route_segments[$i] = $replacement;
					$key = preg_replace('#(\{|\})#', '', $key);
					$this->vars[$key] = $replacement;
					$this->EE->config->_global_vars['freeway_' . $key] = $replacement;
				}
			}

			$this->EE->uri->uri_string = implode('/', $route_segments);
			$this->restore_params();
			$this->output['Parsed Route'] = $this->EE->uri->uri_string;

		}

		function rebuild_uri_for_parsing(){
			$this->EE->uri->segments = array();
			$this->EE->uri->rsegments = array();
			$this->EE->uri->_explode_segments();
			$this->EE->uri->_reindex_segments();
		}

	/* @end */

	/* @group management */

		function activate_extension() {
			$data = array(
				'class' => 'Freeway_ext',
				'hook' => 'sessions_start',
				'method' => 'Freeway_ext',
				'priority' => 10,
				'version' => $this->version,
				'enabled' => 'y'
			);
		}

		function update_extension() {
			$this->activate_extension();
		}

		function disable_extension() {
			$this->EE->db->where('class', 'Freeway_ext');
			$this->EE->db->delete('exp_extensions');
		}

	/* @end */

}

/* End of file      : ext.freeway.php */
/* File location    : third_party/freeway/ext.freeway.php */
