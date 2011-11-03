<?php 

/**
 * Freeway Extension Class for ExpressionEngine 2
 *
 * @package		Freeway
 * @author		Doug Avery <doug.avery@viget.com>
 *
 */

	/* 

	*/

class Freeway_ext {

	/**
	 * Required vars
	 */
	var $name = 'Freeway';
	var $description = 'A routing system for EE'; 
	var $version = '0.0.1';
	var $settings_exist = 'y';
	var $docs_url = 'http://github.com/averyvery/Freeway#readme';
	
	/**
	 * Settings
	 */
	var $settings = array();
	var $settings_default = array();

	function settings(){
		$settings['routes'] = array('t', null, '');
		return $settings;
	}

	/**
	 * Extension constructor
	 */
	function Freeway_ext($settings='')
	{

		$this->setup($settings);

		if($this->should_execute()){

			$this->store_original_uri();
			$this->load_routes();
			$this->remove_and_store_params();

			if($this->uri_matches_pattern()){
				$this->parse_new_uri_from_route();
				$this->rebuild_uri_for_parsing();
			};

			$this->output_freeway_data();

		}

	}

	function load_routes(){
	
		$routes_from_settings = $this->settings['routes'];
		$routes_array = explode("\n", $routes_from_settings);
		$this->routes = Array();

		foreach($routes_array as $route){
			$route_is_blank = $route == "";
			if($route_is_blank == false){
				$route_pair = explode(' => ', $route);
				$this->routes[$route_pair[0]] = $route_pair[1];
			}
		}

		return count($this->routes) > 0;

	}

	function setup($settings){
	
		$this->settings = $settings;
		$this->EE =& get_instance();
		$this->original_uri = $this->EE->uri->uri_string;
		$this->output = Array();
		$this->vars = Array();

	}

	function remove_and_store_params(){

		// Store URI for debugging
		$this->param_pattern  = '#(';    // begin match group
		$this->param_pattern .=   '\?';    // match a '?';
		$this->param_pattern .=   '|';   // OR
		$this->param_pattern .=   '\&';    // match a '?';
		$this->param_pattern .= ')';    // end match group
		$this->param_pattern .= '.*$';   // continue matching characters until end of string
		$this->param_pattern .= '#';    // end match

		$matches = Array();
		preg_match($this->param_pattern, $this->EE->uri->uri_string, $matches);
		$this->url_params = (isset($matches[0])) ? $matches[0] : '';
		$this->EE->uri->uri_string = preg_replace($this->param_pattern, '', $this->EE->uri->uri_string);

	}

	function restore_params(){
		$this->EE->uri->uri_string .= $this->url_params;
	}

	function log($title, $value){
		$this->output[$title] = $value;
	}
	
	function store_original_uri(){

		$uri_array = explode('/', $this->original_uri);
		for($i = 0; $i < 11; $i++){
			$value = (isset($uri_array[$i])) ? $uri_array[$i] : '';
			$count = $i + 1;
			$this->EE->config->_global_vars['freeway_' . $count] = $value;
		}
		$this->log('Original URI', $this->original_uri);

	}

	function should_execute(){
		
		// is a URI? (lame test for checking to see if we're viewing the CP or not)
		return 
			isset($this->EE->uri->uri_string) &&
			 $this->EE->uri->uri_string != '' &&
			// Reeroute actually executes twice - but the second timee
			// the "settings" object isn't an array, which breaks it.
			gettype($this->settings) == 'array';
		
	}

	function convert_pattern_to_regex($pattern){
		$regex = preg_replace('#\{\{.*?\}\}#', '.*?', $pattern);
		return '#' . $regex . '#';
	}

	function uri_matches_pattern(){

		$match_occurred = false;
		$reversed_routes = array_reverse($this->routes);

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

		for($i = 1; $i < count($route_segments); $i++){
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
		$this->EE->uri->uri_string = '/blank_segment/' . $this->EE->uri->uri_string;

	}

	function output_freeway_data(){

		$output = '<div style="font-size: 11px; font-family: sans-serif;">';
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
		foreach($this->output as $title => $value){
			$output .= '<li>';
			$output .= $title . ' => ' . $value;
			$output .= '</li>';
		}
		$output .= '</ul>';
		$output .= '</div>';

		$this->EE->config->_global_vars['freeway_info'] = $output;

	}

	function rebuild_uri_for_parsing(){

		$this->EE->uri->segments = array();
		$this->EE->uri->rsegments = array();
		$this->EE->uri->_explode_segments();

	}
	 
	/**
	 * Activate Extension
	 */
	function activate_extension()
	{
		
		$data = array(
			'class' => 'Freeway_ext',
			'hook' => 'sessions_start',
			'method' => 'Freeway_ext',
			'settings' => serialize($this->settings_default),
			'priority' => 10,
			'version' => $this->version,
			'enabled' => 'y'
		);

		// insert in database
		$this->EE->functions->clear_caching('db');
		$this->EE->db->insert('exp_extensions', $data);
					
	}

	/**
	 * Update Extension
	 */
	function update_extension()
	{
		$this->activate_extension();
	}

	/**
	 * Delete extension
	 */
	function disable_extension()
	{
		$this->EE->functions->clear_caching('db');
		$this->EE->db->where('class', 'Freeway_ext');
		$this->EE->db->delete('exp_extensions');
	}

}

?>
