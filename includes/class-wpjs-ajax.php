<?php

/**
 * AJAX-specific functionality for the plugin.
 *
 * @link       https://wpjuggler.com
 * @since      1.0.0
 *
 * @package    WP_Juggler_Server
 * @subpackage WP_Juggler_Server/includes
 */

// Prevent direct access.
if ( ! defined( 'WPJS_PATH' ) ) exit;

class WPJS_AJAX {

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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $wp_juggler_server       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct($wp_juggler_server, $version)
	{
		$this->wp_juggler_server = $wp_juggler_server;
		$this->version = $version;
		$this->plugin_name = 'wpjs';
	}

	public function ajax_get_dashboard()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		$args = array(
			'post_type' => 'wpjugglersites',
			'post_status' => 'publish',
			'numberposts' => -1
		);
		
		$wpjuggler_sites = get_posts($args);
		$data = array();
		
		foreach ($wpjuggler_sites as $site) {
			$data[] = array(
				'title' => get_the_title($site->ID),
				'wp_juggler_automatic_login' => get_post_meta($site->ID, 'wp_juggler_automatic_login', true),
				'wp_juggler_server_site_url' => get_post_meta($site->ID, 'wp_juggler_server_site_url', true)
			);
		}

		wp_send_json_success($data, 200);
	}

	public function wpjs_user_search()
	{	
		$nonce = sanitize_text_field($_GET['wp_juggler_server_nonce']);

		wp_verify_nonce( $this->plugin_name , '-user', $nonce);

		// Check for user capabilities
		if (!current_user_can('edit_posts')) {
			wp_send_json_error('You do not have permission to perform this action.');
			wp_die();
		}

		$term = sanitize_text_field($_GET['term']);

		$user_query = new WP_User_Query(array(
			'search' => '*' . esc_attr($term) . '*',
			'search_columns' => array('user_login', 'user_nicename', 'user_email', 'display_name'),
		));

		$users = $user_query->get_results();
		$results = array();
		foreach ($users as $user) {
			$results[] = array(
				'label' => $user->user_login,
				'value' => $user->user_login,
			);
		}
		wp_send_json($results);
	}

}