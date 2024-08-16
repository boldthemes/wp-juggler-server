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
if (! defined('WPJS_PATH')) exit;

class WPJS_AJAX
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

	public function ajax_get_settings()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		$wpjs_cp_slug = get_option('wpjs_cp_slug');

		$data = array(
			'wpjs_cp_slug' => $wpjs_cp_slug ? esc_attr($wpjs_cp_slug) : '',
		);

		wp_send_json_success($data, 200);
	}

	public function ajax_save_settings()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		$wpjs_cp_slug = (isset($_POST['wpjs_cp_slug'])) ? sanitize_text_field($_POST['wpjs_cp_slug']) : false;

		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], $this->plugin_name . '-settings')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Nonce is not valid'), 401);
			exit;
		}

		if ($wpjs_cp_slug) {
			update_option('wpjs_cp_slug',  $wpjs_cp_slug);
		} else {
			delete_option('wpjs_cp_slug');
		}

		$data = array();
		wp_send_json_success($data, 200);
	}

	public function ajax_get_control_panel()
	{
		function can_user_access_post($post)
		{
			$allowed_users = get_post_meta($post->ID, 'wp_juggler_login_users', true); // Retrieve allowed user usernames

			$current_user = wp_get_current_user()->user_login; // Get current user username

			$allowed_usernames = array_map(function ($user_id) {
				$user_info = get_userdata($user_id);
				return $user_info ? $user_info->user_login : null;
			}, $allowed_users);

			if (!is_array($allowed_usernames)) {
				$allowed_usernames = array();
			}

			return in_array($current_user, $allowed_usernames); // Check if current user is in allowed users
		}

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
			if (can_user_access_post($site)) {

				$site_activation = get_post_meta($site->ID, 'wp_juggler_site_activation', true) == "on" ? true : false;

				$automatic_logon = get_post_meta($site->ID, 'wp_juggler_automatic_login', true) == "on" ? true : false;

				$newsite = array(
					'id' => $site->ID,
					'title' => get_the_title($site->ID),
					'wp_juggler_automatic_login' => $automatic_logon,
					'wp_juggler_server_site_url' => get_post_meta($site->ID, 'wp_juggler_server_site_url', true),
					'wp_juggler_site_activation' => $site_activation,
					'wp_juggler_automatic_login' => false
				);

				if ($site_activation) {
					
					$access_token = false;
					$access_user = get_post_meta($site->ID, 'wp_juggler_login_username', true);

					if ($automatic_logon && $access_user) {

						$api_key = get_post_meta($site->ID, 'wp_juggler_api_key', true);

						if ($api_key) {
							$access_token = WPJS_Service::wpjs_generate_login_token($access_user, $api_key);
							$newsite['wp_juggler_automatic_login'] = true;
							$newsite['wp_juggler_access_token'] = $access_token;
						}
					}
				}

				$data[] = $newsite;
			}
		}

		wp_send_json_success($data, 200);
	}

	public function wpjs_user_search()
	{
		$nonce = sanitize_text_field($_GET['wp_juggler_server_nonce']);

		wp_verify_nonce($this->plugin_name, '-user', $nonce);

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
