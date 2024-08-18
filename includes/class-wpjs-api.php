<?php

/**
 * API-specific functionality for the plugin.
 *
 * @link       https://wpjuggler.com
 * @since      1.0.0
 *
 * @package    WP_Juggler_Server
 * @subpackage WP_Juggler_Server/includes
 */

// Prevent direct access.
if (! defined('WPJS_PATH')) exit;

class WPJS_Api
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $wp_juggler_server    The ID of this plugin.
	 */
	private $wp_juggler_server;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $plugin_name;

	private $cron;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $wp_juggler_server       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct($wp_juggler_server, $version, $cron = null)
	{
		$this->wp_juggler_server = $wp_juggler_server;
		$this->version = $version;
		$this->cron = $cron;
		$this->plugin_name = 'wpjs';
	}

	public function api_register_routes()
	{
		register_rest_route('juggler/v1', '/triggerCron/', array(
			'methods' => 'POST',
			'callback' => array($this, 'api_trigger_cron'),
			'args' => array(),
			'permission_callback' => array($this, 'api_validate_api_key')
		));
	}


	public function api_validate_api_key()
	{
		
		$token = $this->get_api_key();
		$site_id = false;

		if($token) {
			$site_id = $this->get_site_id_by_api_key($token);
		}
	
		if ( $site_id ) {
			return true;
		} else {
			 return false;
		}
	}

	private function get_site_id_by_api_key( $api_key ){

		$args = array(
			'post_type'  => 'wpjugglersites',
			'meta_query' => array(
				array(
					'key'   => 'wp_juggler_api_key',
					'value' => $api_key,
				),
			),
			'fields'     => 'ids',
			'numberposts' => 1,
		);
	
		$posts = get_posts($args);

		if (!empty($posts)) {
			return $posts[0];
		} else {
			return false;
		}
	}

	private function get_api_key(){

		$auth_header = !empty($_SERVER['HTTP_AUTHORIZATION']) ? sanitize_text_field($_SERVER['HTTP_AUTHORIZATION']) : false;
		/* Double check for different auth header string (server dependent) */
		if (!$auth_header) {
			$auth_header = !empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? sanitize_text_field($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) : false;
		}

		if (!$auth_header) {
			return false;
		}

		/**
		 * Check if the auth header is not bearer, if so, return the user
		 */
		if (strpos($auth_header, 'Bearer') !== 0) {
			return false;
		}

		[$token] = sscanf($auth_header, 'Bearer %s');

		return $token;

	}

	
	public function api_trigger_cron(WP_REST_Request $request)
	{

		$api_key = $this->get_api_key();

		$site_id = $this->get_site_id_by_api_key($api_key);

		$this->cron->check_client_api();
		
		$data = array( 
			'user_id' => $site_id
		);

		wp_send_json_success($data, 200);

	}

}


