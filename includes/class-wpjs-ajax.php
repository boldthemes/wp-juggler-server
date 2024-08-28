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

		$wpjs_cron_schedules = $this->get_wpjs_cron_schedules();

		$wpjs_cp_slug = get_option('wpjs_cp_slug');
		
		$wpjs_uptime_cron_interval = get_option('wpjs_uptime_cron_interval');
		$wpjs_health_cron_interval = get_option('wpjs_health_cron_interval');
		$wpjs_plugins_cron_interval = get_option('wpjs_plugins_cron_interval');
		$wpjs_checksum_cron_interval = get_option('wpjs_checksum_cron_interval');

		$data = array(
			'wpjs_cp_slug' => $wpjs_cp_slug ? esc_attr($wpjs_cp_slug) : '',
			'wpjs_uptime_cron_interval' => $wpjs_uptime_cron_interval ? esc_attr($wpjs_uptime_cron_interval) : 'wpjs_5min',
			'wpjs_health_cron_interval' => $wpjs_health_cron_interval ? esc_attr($wpjs_health_cron_interval) : 'daily',
			'wpjs_plugins_cron_interval' => $wpjs_plugins_cron_interval ? esc_attr($wpjs_plugins_cron_interval) : 'daily',
			'wpjs_checksum_cron_interval' => $wpjs_checksum_cron_interval ? esc_attr($wpjs_checksum_cron_interval) : 'daily',
			
			'wpjs_cron_schedules' => $wpjs_cron_schedules
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

		$wpjs_uptime_cron_interval = (isset($_POST['wpjs_uptime_cron_interval'])) ? sanitize_text_field($_POST['wpjs_uptime_cron_interval']) : false;
		$wpjs_health_cron_interval = (isset($_POST['wpjs_health_cron_interval'])) ? sanitize_text_field($_POST['wpjs_health_cron_interval']) : false;
		$wpjs_plugins_cron_interval = (isset($_POST['wpjs_plugins_cron_interval'])) ? sanitize_text_field($_POST['wpjs_plugins_cron_interval']) : false;
		$wpjs_checksum_cron_interval = (isset($_POST['wpjs_checksum_cron_interval'])) ? sanitize_text_field($_POST['wpjs_checksum_cron_interval']) : false;

		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], $this->plugin_name . '-settings')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Nonce is not valid'), 401);
			exit;
		}

		if ($wpjs_cp_slug) {
			update_option('wpjs_cp_slug',  $wpjs_cp_slug);
		} else {
			delete_option('wpjs_cp_slug');
		}

		if ($wpjs_uptime_cron_interval) {
			update_option('wpjs_uptime_cron_interval',  $wpjs_uptime_cron_interval);
		} else {
			delete_option('wpjs_uptime_cron_interval');
		}

		if ($wpjs_health_cron_interval) {
			update_option('wpjs_health_cron_interval',  $wpjs_health_cron_interval);
		} else {
			delete_option('wpjs_health_cron_interval');
		}

		if ($wpjs_plugins_cron_interval) {
			update_option('wpjs_plugins_cron_interval',  $wpjs_plugins_cron_interval);
		} else {
			delete_option('wpjs_plugins_cron_interval');
		}

		if ($wpjs_checksum_cron_interval) {
			update_option('wpjs_checksum_cron_interval',  $wpjs_checksum_cron_interval);
		} else {
			delete_option('wpjs_checksum_cron_interval');
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

		function get_uptime_stats_7days( $site_id ){
			global $wpdb;
		
			$results = array_fill(0, 7, [
				'fail_num' => 0,
				'fail_array' => [],
				'total_num' => 0,
			]);
		
			$table_name = $wpdb->prefix . 'wpjs_cron_log';
		
			$query = $wpdb->prepare(
				"SELECT * FROM $table_name WHERE  wpjugglersites_id = %s AND log_time >= %s",
				$site_id,
				date('Y-m-d H:i:s', strtotime('-7 days'))
			);
		
			$logs = $wpdb->get_results($query, ARRAY_A);
			$current_time = time();
		
			foreach ($logs as $log) {
				$index = (int) floor((time() - strtotime($log['log_time'])) / 86400);
				$log_time = strtotime($log['log_time']);
				$days_ago = (int) floor(($current_time - $log_time) / 86400);

				if ($days_ago >= 0 && $days_ago < 7) {
					$results[$days_ago]['total_num']++;
		
					if ($log['log_result'] == 'fail') {
						$results[$days_ago]['fail_num']++;
						$results[$days_ago]['fail_array'][] = $log;
					}
				}
			}
		
			return $results;
		}

		function get_last_plugin_data($site_id) {
			global $wpdb;
		
			$result = $wpdb->get_row(
				$wpdb->prepare(
					"
					SELECT log_data 
					FROM wp_wpjs_cron_log 
					WHERE wpjugglersites_id = %s 
					  AND log_type = 'checkPlugins' 
					  AND log_result = 'succ' 
					ORDER BY log_time DESC 
					LIMIT 1
					",
					$site_id
				),
				ARRAY_A
			);
		
			if (!$result) {
				return false;
			}
		
			$log_data_array = json_decode($result['log_data'], true);

			return $log_data_array;
		}

		function get_plugin_checksum_data($site_id) {
			global $wpdb;
		
			$result = $wpdb->get_row(
				$wpdb->prepare(
					"
					SELECT log_data 
					FROM wp_wpjs_cron_log 
					WHERE wpjugglersites_id = %s 
					  AND log_type = 'checkPluginChecksum' 
					  AND log_result = 'succ' 
					ORDER BY log_time DESC 
					LIMIT 1
					",
					$site_id
				),
				ARRAY_A
			);
		
			if (!$result) {
				return false;
			}
		
			$log_data_array = json_decode($result['log_data'], true);

			return $log_data_array;
		}

		function get_core_checksum_data($site_id) {
			global $wpdb;
		
			$result = $wpdb->get_row(
				$wpdb->prepare(
					"
					SELECT log_data 
					FROM wp_wpjs_cron_log 
					WHERE wpjugglersites_id = %s 
					  AND log_type = 'checkCoreChecksum' 
					  AND log_result = 'succ' 
					ORDER BY log_time DESC 
					LIMIT 1
					",
					$site_id
				),
				ARRAY_A
			);
		
			if (!$result) {
				return false;
			}
		
			$log_data_array = json_decode($result['log_data'], true);

			return $log_data_array;
		}

		function get_updates_and_vul( $plugins_array ){

			if( !$plugins_array ) {
				return false;
			}

			$updates_num = 0;
			$vulnerabilities_num = 0;
		
			foreach ($plugins_array as $item) {
				if (isset($item['UpdateVersion']) && !empty($item['UpdateVersion'])) {
					$updates_num++;
				}
				if (isset($item['Vulnerabilities']) && is_array($item['Vulnerabilities'])) {
					$vulnerabilities_num += count($item['Vulnerabilities']);
				}
			}
		
			return (object) [
				'updates_num' => $updates_num,
				'vulnerabilities_num' => $vulnerabilities_num
			];
		}

		function load_wpjugglertools() {
			$args = array(
				'post_type'      => 'wpjugglertools',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			);
		
			$posts_array = get_posts($args);
			$posts = array();
		
			foreach ($posts_array as $post) {
				$posts[] = array(
					'ID'            => $post->ID,
					'wp_juggler_related_sites' => get_post_meta($post->ID, 'wp_juggler_related_sites', true),
					'wp_juggler_tool_label' => get_post_meta($post->ID, 'wp_juggler_tool_label', true),
					'wp_juggler_tool_url' => get_post_meta($post->ID, 'wp_juggler_tool_url', true),
				);
			}
		
			return $posts;
		}
		
		function get_related_wpjugglertools($site_id, $wpjugglertools_posts) {
			$related_posts = array();
		
			foreach ($wpjugglertools_posts as $post) {
				if (is_array($post['wp_juggler_related_sites']) && in_array($site_id, $post['wp_juggler_related_sites'])) {
					$related_posts[] = $post;
				}
			}
		
			return $related_posts;
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
		
		$tools_array = load_wpjugglertools();

		foreach ($wpjuggler_sites as $site) {

			if (can_user_access_post($site)) {

				$site_activation = get_post_meta($site->ID, 'wp_juggler_site_activation', true) == "on" ? true : false;

				$multisite = get_post_meta($site->ID, 'wp_juggler_multisite', true) == "on" ? true : false;

				$automatic_logon = get_post_meta($site->ID, 'wp_juggler_automatic_login', true) == "on" ? true : false;

				$site_url = get_post_meta($site->ID, 'wp_juggler_server_site_url', true);

				$uptime7 = get_uptime_stats_7days( $site->ID );

				$plugins_data = get_last_plugin_data($site->ID);

				$updates_vul = get_updates_and_vul( $plugins_data );

				$plugins_checksum = get_plugin_checksum_data( $site->ID );

				$core_checksum = get_core_checksum_data( $site->ID );
				
				$newsite = array(
					'id' => $site->ID,
					'title' => get_the_title($site->ID),
					'wp_juggler_server_site_url' => $site_url,
					'wp_juggler_multisite' => $multisite,
					'wp_juggler_site_activation' => $site_activation,
					'wp_juggler_automatic_login' => false,
					'wp_juggler_uptime_7' => $uptime7,
					'wp_pluggins_summary' => $updates_vul,
					'wp_plugins_checksum' => $plugins_checksum,
					'wp_core_checksum' => $core_checksum
				);

				if ($site_activation) {

					$access_token = false;
					$access_user = get_post_meta($site->ID, 'wp_juggler_login_username', true);
					$api_key = get_post_meta($site->ID, 'wp_juggler_api_key', true);

					$related_tools = get_related_wpjugglertools($site->ID, $tools_array);

					if ($automatic_logon && $access_user && $api_key) {
						
						$access_token = WPJS_Service::wpjs_generate_login_token($access_user, $api_key);
						$final_url = WPJS_Service::add_query_var_to_url(rtrim($site_url, '/') . '/wpjs/' , 'wpjs_token', $access_token);
						
						$newsite['wp_juggler_automatic_login'] = true;
						$newsite['wp_juggler_login_url'] = $final_url;
						$newsite['wp_juggler_login_tools'] = array();

						foreach ($related_tools as $tool) {
							$newsite['wp_juggler_login_tools'][] = array(
								'wp_juggler_tool_label' => $tool['wp_juggler_tool_label'],
								'wp_juggler_tool_url' => WPJS_Service::add_query_var_to_url($final_url , 'wpjs_redirect', rtrim($site_url, '/') . '/' . ltrim($tool['wp_juggler_tool_url'], '/'))
							);
						}

					} else {
						$newsite['wp_juggler_automatic_login'] = false;
						$newsite['wp_juggler_login_url'] = rtrim($site_url, '/') . '/wp-admin/';
						$newsite['wp_juggler_login_tools'] = array();

						foreach ($related_tools as $tool) {
							$newsite['wp_juggler_login_tools'][] = array(
								'wp_juggler_tool_label' => $tool['wp_juggler_tool_label'],
								'wp_juggler_tool_url' => rtrim($site_url, '/') . '/' . ltrim($tool['wp_juggler_tool_url'], '/')
							);
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

	private function get_wpjs_cron_schedules() {
		$schedules = wp_get_schedules();
		$wpjs_schedules = array();
	
		foreach ($schedules as $name => $details) {
			if (strpos($name, 'wpjs_') === 0) {
				$wpjs_schedules[$name] = $details['display'];
			}
		}
	
		return $wpjs_schedules;
	}
}
