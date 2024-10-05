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

	private $cron;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $wp_juggler_server       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct($wp_juggler_server, $version, $cron)
	{
		$this->wp_juggler_server = $wp_juggler_server;
		$this->version = $version;
		$this->plugin_name = 'wpjs';
		$this->cron = $cron;
	}

	public function ajax_get_dashboard()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		/* $args = array(
			'post_type' => 'wpjugglersites',
			'post_status' => 'publish',
			'numberposts' => -1
		); */

		$hook_slugs = [
			"wpjs_check_health_api",
			"wpjs_check_debug_api",
			"wpjs_check_core_checksum_api",
			"wpjs_check_client_api",
			"wpjs_check_plugins_api",
			"wpjs_check_plugins_checksum_api",
			"wpjs_check_notices_api"
		];

		$crons = $this->get_wp_cron_events_info($hook_slugs);

		/* $wpjuggler_sites = get_posts($args);
		$data = array(); */

		$data = $crons;

		wp_send_json_success($data, 200);
	}

	function get_wp_cron_events_info($hook_slugs)
	{
		if (!is_array($hook_slugs)) {
			return false;
		}

		$cron_events = _get_cron_array();
		$schedules = wp_get_schedules();
		$result = array();

		$timezone = wp_timezone();

		foreach ($hook_slugs as $hook_slug) {
			foreach ($cron_events as $timestamp => $cron) {
				if (isset($cron[$hook_slug])) {
					foreach ($cron[$hook_slug] as $key => $event) {
						$frequency = isset($schedules[$event['schedule']]) ? $schedules[$event['schedule']]['display'] : 'One-time';

						$datetime = new DateTime();
						$datetime->setTimestamp($timestamp);
						$datetime->setTimezone($timezone);
						$crontime = $datetime->format('Y-m-d H:i:s');
						$label_ago = $this->get_time_to($timestamp);

						$result[] = (object) array(
							'hook_slug' => $hook_slug,
							'timestamp' => $timestamp,
							'frequency' => $frequency,
							'time' => $crontime,
							'label_ago' => $label_ago
						);
					}
				}
			}
		}

		return $result;
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
		$wpjs_debug_cron_interval = get_option('wpjs_debug_cron_interval');
		$wpjs_core_checksum_cron_interval = get_option('wpjs_core_checksum_cron_interval');
		$wpjs_plugins_cron_interval = get_option('wpjs_plugins_cron_interval');
		$wpjs_plugins_checksum_cron_interval = get_option('wpjs_plugins_checksum_cron_interval');
		$wpjs_notices_cron_interval = get_option('wpjs_notices_cron_interval');
		

		$data = array(
			'wpjs_cp_slug' => $wpjs_cp_slug ? esc_attr($wpjs_cp_slug) : '',
			'wpjs_uptime_cron_interval' => $wpjs_uptime_cron_interval ? esc_attr($wpjs_uptime_cron_interval) : 'wpjs_5min',
			'wpjs_health_cron_interval' => $wpjs_health_cron_interval ? esc_attr($wpjs_health_cron_interval) : 'wpjs_daily',
			'wpjs_debug_cron_interval' => $wpjs_debug_cron_interval ? esc_attr($wpjs_debug_cron_interval) : 'wpjs_daily',
			'wpjs_core_checksum_cron_interval' => $wpjs_core_checksum_cron_interval ? esc_attr($wpjs_core_checksum_cron_interval) : 'wpjs_daily',
			'wpjs_plugins_cron_interval' => $wpjs_plugins_cron_interval ? esc_attr($wpjs_plugins_cron_interval) : 'wpjs_daily',
			'wpjs_plugins_checksum_cron_interval' => $wpjs_plugins_checksum_cron_interval ? esc_attr($wpjs_plugins_checksum_cron_interval) : 'wpjs_daily',
			'wpjs_notices_cron_interval' => $wpjs_notices_cron_interval ? esc_attr($wpjs_notices_cron_interval) : 'wpjs_daily',

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
		$wpjs_debug_cron_interval = (isset($_POST['wpjs_debug_cron_interval'])) ? sanitize_text_field($_POST['wpjs_debug_cron_interval']) : false;
		$wpjs_core_checksum_cron_interval = (isset($_POST['wpjs_core_checksum_cron_interval'])) ? sanitize_text_field($_POST['wpjs_core_checksum_cron_interval']) : false;
		$wpjs_plugins_cron_interval = (isset($_POST['wpjs_plugins_cron_interval'])) ? sanitize_text_field($_POST['wpjs_plugins_cron_interval']) : false;
		$wpjs_plugins_checksum_cron_interval = (isset($_POST['wpjs_plugins_checksum_cron_interval'])) ? sanitize_text_field($_POST['wpjs_plugins_checksum_cron_interval']) : false;
		$wpjs_notices_cron_interval = (isset($_POST['wpjs_notices_cron_interval'])) ? sanitize_text_field($_POST['wpjs_notices_cron_interval']) : false;

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

		if ($wpjs_debug_cron_interval) {
			update_option('wpjs_debug_cron_interval',  $wpjs_debug_cron_interval);
		} else {
			delete_option('wpjs_debug_cron_interval');
		}

		if ($wpjs_core_checksum_cron_interval) {
			update_option('wpjs_core_checksum_cron_interval',  $wpjs_core_checksum_cron_interval);
		} else {
			delete_option('wpjs_core_checksum_cron_interval');
		}

		if ($wpjs_plugins_cron_interval) {
			update_option('wpjs_plugins_cron_interval',  $wpjs_plugins_cron_interval);
		} else {
			delete_option('wpjs_plugins_cron_interval');
		}

		if ($wpjs_plugins_checksum_cron_interval) {
			update_option('wpjs_plugins_checksum_cron_interval',  $wpjs_plugins_checksum_cron_interval);
		} else {
			delete_option('wpjs_plugins_checksum_cron_interval');
		}

		if ($wpjs_notices_cron_interval) {
			update_option('wpjs_notices_cron_interval',  $wpjs_notices_cron_interval);
		} else {
			delete_option('wpjs_notices_cron_interval');
		}

		$data = array();
		wp_send_json_success($data, 200);
	}

	private function can_user_access_post($post)
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

	private function get_uptime_stats($site_id)
	{
		global $wpdb;

		$results = array_fill(0, 7, [
			'fail_num' => 0,
			//'fail_array' => [],
			'total_num' => 0,
		]);

		$table_name = $wpdb->prefix . 'wpjs_cron_log';

		$query = $wpdb->prepare(
			"SELECT * FROM $table_name WHERE  wpjugglersites_id = %s AND log_time >= %s AND log_type IN (%s, %s) AND log_result IN (%s, %s)",
			$site_id,
			date('Y-m-d H:i:s', strtotime('-7 days')),
			'confirmClientApi',
			'confirmFrontEnd',
			'fail',
			'error'
		);

		$logs = $wpdb->get_results($query, ARRAY_A);
		$current_time = time();

		foreach ($logs as $log) {
			$log_time = strtotime($log['log_time']);
			$days_ago = (int) floor(($current_time - $log_time) / 86400);

			if ($days_ago >= 0 && $days_ago < 7) {
				$results[$days_ago]['total_num']++;

				if ($log['log_result'] == 'fail' || $log['log_result'] == 'error') {
					$results[$days_ago]['fail_num']++;
					//$results[$days_ago]['fail_array'][] = $log;
				}
			}
		}

		foreach ($results as $index => &$day) {
			$timestamp = strtotime("-$index days");
			$day_label = date('D, M j', $timestamp);
			$day['day_label'] = $day_label;
		}

		$results_rev = array_reverse($results);

		$final_res = [
			'uptime_timeline' => $results_rev
		];

		$final_res['summary'] = array();

		$uptime_periods = [
			'-1 day',
			'-7 days',
			'-30 days',
			'-3 months',
		];

		foreach ($uptime_periods as $period) {

			$query = $wpdb->prepare(
				"SELECT COUNT(ID) AS Num FROM $table_name WHERE  wpjugglersites_id = %s AND log_time >= %s AND log_type IN (%s) AND log_result IN (%s, %s)",
				$site_id,
				date('Y-m-d H:i:s', strtotime($period)),
				'confirmClientApi',
				'fail',
				'error'
			);

			$logs = $wpdb->get_results($query, ARRAY_A);

			$numapi = $logs[0]['Num'];

			$query = $wpdb->prepare(
				"SELECT COUNT(ID) AS Num FROM $table_name WHERE  wpjugglersites_id = %s AND log_time >= %s AND log_type IN (%s) AND log_result IN (%s, %s)",
				$site_id,
				date('Y-m-d H:i:s', strtotime($period)),
				'confirmFrontEnd',
				'fail',
				'error'
			);

			$logs = $wpdb->get_results($query, ARRAY_A);

			$numfront = $logs[0]['Num'];

			$obj = [
				'api' => $numapi,
				'front' => $numfront
			];

			$final_res['summary'][] = $obj;
		}

		return $final_res;
	}

	private function get_latest_plugin_data($site_id)
	{
		global $wpdb;

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT * 
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

		$result_checksum = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT * 
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

		if(!$result_checksum) {
			$log_data_checksum_array = [];
		} else {
			$log_data_checksum_array = json_decode($result_checksum['log_data'], true);
		}

		return [
			'data' => array_merge_recursive($log_data_array['plugins_data'], $log_data_checksum_array['plugins_data']),
			'timestamp' => strtotime($result['log_time']),
			'timestamp_checksum' => $result_checksum? strtotime($result_checksum['log_time']): false
		];
	}

	private function get_latest_themes_data($site_id)
	{
		global $wpdb;

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT * 
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

		return [
			'data' => $log_data_array['themes_data'],
			'timestamp' => strtotime($result['log_time'])
		];
	}

	private function get_plugin_checksum_data($site_id)
	{
		global $wpdb;

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT * 
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
		$plugins_data = $log_data_array['plugins_data'];

		return [
			'data' => $log_data_array,
			'timestamp' => strtotime($result['log_time'])
		];
	}

	private function get_core_checksum_data($site_id)
	{
		global $wpdb;

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT * 
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

		return [
			'data' => $log_data_array,
			'timestamp' => strtotime($result['log_time'])
		];
	}

	private function get_health_data($site_id)
	{
		global $wpdb;

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT * 
				FROM wp_wpjs_cron_log 
				WHERE wpjugglersites_id = %s 
					AND log_type = 'checkHealth' 
					AND log_result = 'succ' 
				ORDER BY log_time DESC 
				LIMIT 1
				",
				$site_id
			),
			ARRAY_A
		);

		$result_debug = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT * 
				FROM wp_wpjs_cron_log 
				WHERE wpjugglersites_id = %s 
					AND log_type = 'checkDebug' 
					AND log_result = 'succ' 
				ORDER BY log_time DESC 
				LIMIT 1
				",
				$site_id
			),
			ARRAY_A
		);

		$result_core_checksum = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT * 
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

		if(!$result_debug) {
			$log_data_debug_array = [];
		} else {
			$log_data_debug_array = json_decode($result_debug['log_data'], true);
		}

		if(!$result_core_checksum) {
			$log_data_core_checksum = [];
		} else {
			$log_data_core_checksum = json_decode($result_core_checksum['log_data'], true);
		}

		return [
			'data' => array_merge_recursive(array_merge_recursive($log_data_array, $log_data_debug_array), $log_data_core_checksum),
			'timestamp' => strtotime($result['log_time']),
			'timestamp_debug' => $result_debug? strtotime($result_debug['log_time']) : false,
			'timestamp_core_checksum' => $result_core_checksum? strtotime($result_core_checksum['log_time']): false
		];
	}

	public function ajax_get_plugins_panel()
	{
		global $wpdb;

		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		if (isset($_POST['siteId'])) {
			$site_id = sanitize_text_field($_POST['siteId']);
		}

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT * 
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

		$result_checksum = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT * 
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

		if(!$result_checksum) {
			$log_data_checksum_array = [];
		} else {
			$log_data_checksum_array = json_decode($result_checksum['log_data'], true);
		}

		$automatic_logon = get_post_meta($site_id, 'wp_juggler_automatic_login', true) == "on" ? true : false;
		$access_user = get_post_meta($site_id, 'wp_juggler_login_username', true);
		$api_key = get_post_meta($site_id, 'wp_juggler_api_key', true);
		$site_url = get_post_meta($site_id, 'wp_juggler_server_site_url', true);


		if ($automatic_logon && $access_user && $api_key) {
			$access_token = WPJS_Service::wpjs_generate_login_token($access_user, $api_key);
			$final_url = WPJS_Service::add_query_var_to_url(rtrim($site_url, '/') . '/wpjs/', 'wpjs_token', $access_token);

			$wp_juggler_login_plugin_url = WPJS_Service::add_query_var_to_url($final_url, 'wpjs_redirect', rtrim($site_url, '/') . '/wp-admin/plugins.php');
			$wp_juggler_login_themes_url = WPJS_Service::add_query_var_to_url($final_url, 'wpjs_redirect', rtrim($site_url, '/') . '/wp-admin/themes.php');
		} else {
			$wp_juggler_login_plugin_url = rtrim($site_url, '/') . '/wp-admin/plugins.php';
			$wp_juggler_login_themes_url = rtrim($site_url, '/') . '/wp-admin/themes.php';
		}

		if (!$result) {
			$data['themes_data'] = false;
			$data['plugins_data'] = false;
			$data['wp_juggler_plugins_timestamp'] = false;
			$data['wp_juggler_plugins_checksum_timestamp'] = false;
			$data['wp_juggler_login_plugin_url'] = $wp_juggler_login_plugin_url;
			$data['wp_juggler_login_themes_url'] = $wp_juggler_login_themes_url;
		} else {

			$log_data = json_decode($result['log_data'], true);		
			$log_data1 = array_merge_recursive($log_data, $log_data_checksum_array);
			$log_data = $log_data1;

			$data = $log_data;
			$data['wp_juggler_plugins_timestamp'] = $this->get_time_ago(strtotime($result['log_time']));
			$data['wp_juggler_plugins_checksum_timestamp'] = $this->get_time_ago(strtotime($result_checksum['log_time']));
			$data['wp_juggler_login_plugin_url'] = $wp_juggler_login_plugin_url;
			$data['wp_juggler_login_themes_url'] = $wp_juggler_login_themes_url;
		}


		wp_send_json_success($data, 200);
	}

	private function get_latest_notices($site_id)
	{

		global $wpdb;

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT * 
				FROM wp_wpjs_cron_log 
				WHERE wpjugglersites_id = %s 
					AND log_type = 'checkNotices' 
					AND log_result = 'succ' 
				ORDER BY log_time DESC 
				LIMIT 1
				",
				$site_id
			),
			ARRAY_A
		);

		$result1 = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT COUNT(ID) AS log_count
				FROM wp_wpjs_cron_log 
				WHERE wpjugglersites_id = %s 
					AND log_type = 'checkNotices' 
					AND log_result = 'succ' 
					AND	NOT JSON_LENGTH(log_data) = 0
				ORDER BY log_time DESC 
				",
				$site_id
			),
			ARRAY_A
		);



		if (!$result) {
			$ret_obj = array(
				'wp_juggler_notices' => false,
				'wp_juggler_notices_timestamp' =>  false,
				'wp_juggler_history_count' => $result1['log_count']
			);
		} else {

			$wp_juggler_notices = json_decode($result['log_data'], true);


			$automatic_logon = get_post_meta($site_id, 'wp_juggler_automatic_login', true) == "on" ? true : false;
			$access_user = get_post_meta($site_id, 'wp_juggler_login_username', true);
			$api_key = get_post_meta($site_id, 'wp_juggler_api_key', true);
			$site_url = get_post_meta($site_id, 'wp_juggler_server_site_url', true);

			if ($automatic_logon && $access_user && $api_key) {

				$access_token = WPJS_Service::wpjs_generate_login_token($access_user, $api_key);
				$final_url = WPJS_Service::add_query_var_to_url(rtrim($site_url, '/') . '/wpjs/', 'wpjs_token', $access_token);

				$wp_juggler_notices = $this->replaceNoticeHref($wp_juggler_notices, parse_url($site_url, PHP_URL_HOST), $final_url);
			}

			$ret_obj = array(
				'wp_juggler_notices' => $wp_juggler_notices,
				'wp_juggler_notices_timestamp' =>  $this->get_time_ago(strtotime($result['log_time'])),
				'wp_juggler_history_count' => $result1['log_count']
			);
		}

		return $ret_obj;
	}

	private function get_plugin_view()
	{
		global $wpdb;

		$log_entries = $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT t1.*
					FROM wp_wpjs_cron_log t1
					INNER JOIN (
						SELECT wpjugglersites_id, MAX(log_time) as latest_log_time
						FROM wp_wpjs_cron_log
						WHERE log_type = %s 
						GROUP BY wpjugglersites_id
					) t2
					ON t1.wpjugglersites_id = t2.wpjugglersites_id AND t1.log_time = t2.latest_log_time
					WHERE t1.log_type = %s
				",
				'checkPlugins',
				'checkPlugins',
			)
		);

		// Initialize arrays to hold installed plugins and themes
		$installed_plugins = [];
		$installed_themes = [];

		// Utility function to merge theme/plugin info
		function merge_info(&$array, $name, $slug, $site_info)
		{
			foreach ($array as &$item) {
				if ($item['Slug'] === $slug) {
					$item['Sites'][] = $site_info;
					return;
				}
			}
			$array[] = [
				'Name' => $name,
				'Slug' => $slug,
				'Sites' => [$site_info]
			];
		}

		// Process each log entry
		foreach ($log_entries as $entry) {
			$log_data = json_decode($entry->log_data, true);

			$automatic_logon = get_post_meta($entry->wpjugglersites_id, 'wp_juggler_automatic_login', true) == "on" ? true : false;
			$access_user = get_post_meta($entry->wpjugglersites_id, 'wp_juggler_login_username', true);
			$api_key = get_post_meta($entry->wpjugglersites_id, 'wp_juggler_api_key', true);
			$site_url = get_post_meta($entry->wpjugglersites_id, 'wp_juggler_server_site_url', true);


			if ($automatic_logon && $access_user && $api_key) {

				$access_token = WPJS_Service::wpjs_generate_login_token($access_user, $api_key);
				$final_url = WPJS_Service::add_query_var_to_url(rtrim($site_url, '/') . '/wpjs/', 'wpjs_token', $access_token);

				$wp_juggler_login_plugin_url = WPJS_Service::add_query_var_to_url($final_url, 'wpjs_redirect', rtrim($site_url, '/') . '/wp-admin/plugins.php');
				$wp_juggler_login_themes_url = WPJS_Service::add_query_var_to_url($final_url, 'wpjs_redirect', rtrim($site_url, '/') . '/wp-admin/themes.php');
			} else {
				$wp_juggler_login_plugin_url = rtrim($site_url, '/') . '/wp-admin/plugins.php';
				$wp_juggler_login_themes_url = rtrim($site_url, '/') . '/wp-admin/themes.php';
			}



			$site_info = [
				'wpjugglersites_id' => $entry->wpjugglersites_id,
				'site_name' => get_the_title($entry->wpjugglersites_id),
				'site_url' => get_post_meta($entry->wpjugglersites_id, 'wp_juggler_server_site_url', true),
				'wp_juggler_automatic_login' => get_post_meta($entry->wpjugglersites_id, 'wp_juggler_automatic_login', true) == "on" ? true : false,
				'wp_juggler_login_plugin_url' => $wp_juggler_login_plugin_url,
				'wp_juggler_login_themes_url' =>  $wp_juggler_login_themes_url,
				'wp_juggler_site_activation' => get_post_meta($entry->wpjugglersites_id, 'wp_juggler_site_activation', true) == "on" ? true : false
			];

			// Process themes data
			if (isset($log_data['themes_data'])) {
				foreach ($log_data['themes_data'] as $theme) {
					$site_info = array_merge($site_info, $theme);
					merge_info($installed_themes, $theme['Name'], $theme['Slug'], $site_info);
				}
			}

			$result_checksum = $wpdb->get_row(
				$wpdb->prepare(
					"
					SELECT * 
					FROM wp_wpjs_cron_log 
					WHERE wpjugglersites_id = %s 
						AND log_type = 'checkPluginChecksum' 
						AND log_result = 'succ' 
					ORDER BY log_time DESC 
					LIMIT 1
					",
					$entry->wpjugglersites_id
				),
				ARRAY_A
			);

			if(!$result_checksum) {
				$log_data_checksum_array = [];
			} else {
				$log_data_checksum_array = json_decode($result_checksum['log_data'], true);
			}
			
			$log_data1 = array_merge_recursive($log_data, $log_data_checksum_array);
			$log_data = $log_data1;

			// Process plugins data
			if (isset($log_data['plugins_data'])) {
				foreach ($log_data['plugins_data'] as $plugin) {
					$site_info = array_merge($site_info, $plugin);
					merge_info($installed_plugins, $plugin['Name'], $plugin['Slug'], $site_info);
				}
			}
		}

		return array(
			'plugins' => $installed_plugins,
			'themes' => $installed_themes
		);

	}

	public function ajax_get_latest_notices()
	{

		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		if (isset($_POST['siteId'])) {
			$site_id = sanitize_text_field($_POST['siteId']);
		}

		$data[] = $this->get_latest_notices($site_id);

		wp_send_json_success($data, 200);
	}

	public function ajax_get_uptime_panel()
	{
		global $wpdb;

		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		if (isset($_POST['siteId'])) {
			$site_id = sanitize_text_field($_POST['siteId']);
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT * 
				FROM wp_wpjs_cron_log 
				WHERE wpjugglersites_id = %s 
					AND log_type = 'confirmClientApi' 
					AND log_result IN (%s, %s)
					AND log_time >= NOW() - INTERVAL %d DAY 
				ORDER BY log_time DESC 
				",
				$site_id,
				'error',
				'fail',
				90
			),
			ARRAY_A
		);

		foreach ($results as &$item) {
			if (isset($item['log_time'])) {
				$item['log_timestamp'] = strtotime($item['log_time']);
			}
		}
		unset($item);

		$results1 = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT * 
				FROM wp_wpjs_cron_log 
				WHERE wpjugglersites_id = %s 
					AND log_type = 'confirmFrontEnd' 
					AND log_result IN (%s, %s)
					AND log_time >= NOW() - INTERVAL %d DAY 
				ORDER BY log_time DESC 
				",
				$site_id,
				'error',
				'fail',
				90
			),
			ARRAY_A
		);

		foreach ($results1 as &$item) {
			if (isset($item['log_time'])) {
				$item['log_timestamp'] = strtotime($item['log_time']);
			}
		}
		unset($item);

		$ret_obj = array(
			'wp_juggler_api_downs' => $results,
			'wp_juggler_fe_downs' => $results1,
		);

		$data[] = $ret_obj;

		wp_send_json_success($data, 200);
	}

	public function ajax_get_notices_history()
	{
		global $wpdb;

		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		$site_id = (isset($_POST['siteId'])) ? sanitize_text_field($_POST['siteId']) : false;;
		$page = (isset($_POST['page'])) ? sanitize_text_field($_POST['page']) : false;

		if (intval($page) == 0) {

			$results = $wpdb->get_results(
				$wpdb->prepare(
					"
				SELECT * 
				FROM wp_wpjs_cron_log 
				WHERE wpjugglersites_id = %s 
					AND log_type = 'checkNotices' 
					AND log_result = 'succ'
					AND	NOT JSON_LENGTH(log_data) = 0 
				ORDER BY ID DESC 
				LIMIT 20
				",
					$site_id
				),
				ARRAY_A
			);
		} else {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"
				SELECT * 
				FROM wp_wpjs_cron_log 
				WHERE wpjugglersites_id = %s 
					AND log_type = 'checkNotices' 
					AND log_result = 'succ'
					AND	NOT JSON_LENGTH(log_data) = 0
					AND ID < %s 
				ORDER BY ID DESC 
				LIMIT 20
				",
					$site_id,
					intval($page)
				),
				ARRAY_A
			);
		}

		//$wp_juggler_notices = $this->replaceNoticeTimestamp($wp_juggler_notices);

		foreach ($results as &$item) {

			if (isset($item['log_time'])) {

				$unix_timestamp = strtotime($item['log_time']);
				$timezone = wp_timezone();

				$datetime = new DateTime();
				$datetime->setTimestamp($unix_timestamp);
				$datetime->setTimezone($timezone);
				$logtime = $datetime->format('Y-m-d H:i:s');

				$item['log_time'] = $logtime;
				$item['log_timestamp'] = $unix_timestamp;
			}
		}
		unset($item);

		wp_send_json_success($results, 200);
	}

	public function ajax_get_dashboard_history()
	{
		global $wpdb;

		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		$page = (isset($_POST['page'])) ? sanitize_text_field($_POST['page']) : false;

		if (intval($page) == 0) {

			$results = $wpdb->get_results(
				$wpdb->prepare(
					"
				SELECT * 
				FROM wp_wpjs_cron_log 
				WHERE log_type IN (%s, %s, %s, %s, %s, %s) 
					AND log_result IN (%s, %s)
				ORDER BY ID DESC 
				LIMIT 20
				",
					'checkPlugins',
					'checkNotices',
					'checkHealth',
					'checkDebug',
					'checkCoreChecksum',
					'checkPluginChecksum',
					'error',
					'fail',
				),
				ARRAY_A
			);
		} else {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"
				SELECT * 
				FROM wp_wpjs_cron_log 
				WHERE log_type IN (%s, %s, %s) 
					AND log_result IN (%s, %s)
					AND ID < %s 
				ORDER BY ID DESC 
				LIMIT 20
				",
					'checkPlugins',
					'checkNotices',
					'checkHealth',
					'error',
					'fail',
					intval($page)
				),
				ARRAY_A
			);
		}

		foreach ($results as &$item) {

			if (isset($item['log_time'])) {

				$site_title = get_the_title($item['wpjugglersites_id']);
				$site_url = get_post_meta($item['wpjugglersites_id'], 'wp_juggler_server_site_url', true);

				$unix_timestamp = strtotime($item['log_time']);
				$timezone = wp_timezone();

				$datetime = new DateTime();
				$datetime->setTimestamp($unix_timestamp);
				$datetime->setTimezone($timezone);
				$logtime = $datetime->format('Y-m-d H:i:s');

				$item['log_time'] = $logtime;
				$item['log_timestamp'] = $unix_timestamp;
				$item['site_name'] = $site_title;
				$item['site_url'] = $site_url;
			}
		}
		unset($item);

		wp_send_json_success($results, 200);
	}

	public function ajax_get_uptime_history()
	{
		global $wpdb;

		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		$site_id = (isset($_POST['siteId'])) ? sanitize_text_field($_POST['siteId']) : false;;
		$page = (isset($_POST['page'])) ? sanitize_text_field($_POST['page']) : false;

		if (intval($page) == 0) {

			$results = $wpdb->get_results(
				$wpdb->prepare(
					"
				SELECT * 
				FROM wp_wpjs_cron_log 
				WHERE wpjugglersites_id = %s 
					AND log_type IN (%s, %s) 
					AND log_result IN (%s, %s)
				ORDER BY ID DESC 
				LIMIT 20
				",
					$site_id,
					'confirmClientApi',
					'confirmFrontEnd',
					'error',
					'fail',
				),
				ARRAY_A
			);
		} else {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"
				SELECT * 
				FROM wp_wpjs_cron_log 
				WHERE wpjugglersites_id = %s 
					AND log_type IN (%s, %s) 
					AND log_result IN (%s, %s)
					AND ID < %s 
				ORDER BY ID DESC 
				LIMIT 20
				",
					$site_id,
					'confirmClientApi',
					'confirmFrontEnd',
					'error',
					'fail',
					intval($page)
				),
				ARRAY_A
			);
		}

		foreach ($results as &$item) {

			if (isset($item['log_time'])) {

				$unix_timestamp = strtotime($item['log_time']);
				$timezone = wp_timezone();

				$datetime = new DateTime();
				$datetime->setTimestamp($unix_timestamp);
				$datetime->setTimezone($timezone);
				$logtime = $datetime->format('Y-m-d H:i:s');

				$item['log_time'] = $logtime;
				$item['log_timestamp'] = $unix_timestamp;
			}
		}
		unset($item);

		wp_send_json_success($results, 200);
	}

	private function get_theme_updates($themes_array)
	{

		if (!$themes_array) {
			return false;
		}

		$updates_num = 0;

		foreach ($themes_array as $item) {
			if (isset($item['Update']) && $item['Update']) {
				$updates_num++;
			}
		}


		return (object) [
			'updates_num' => $updates_num
		];
	}

	private function get_plugin_updates_and_vul($plugins_array)
	{

		if (!$plugins_array) {
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

	private function load_wpjugglertools()
	{
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

	private function get_related_wpjugglertools($site_id, $wpjugglertools_posts)
	{
		$related_posts = array();

		foreach ($wpjugglertools_posts as $post) {
			if (is_array($post['wp_juggler_related_sites']) && in_array($site_id, $post['wp_juggler_related_sites'])) {
				$related_posts[] = $post;
			}
		}

		return $related_posts;
	}

	private function get_time_ago($time)
	{
		$time_difference = time() - $time;

		if ($time_difference < 1) {
			return 'less than 1 second ago';
		}
		$condition = array(
			12 * 30 * 24 * 60 * 60 =>  'year',
			30 * 24 * 60 * 60       =>  'month',
			24 * 60 * 60            =>  'day',
			60 * 60                 =>  'hour',
			60                      =>  'minute',
			1                       =>  'second'
		);

		foreach ($condition as $secs => $str) {
			$d = $time_difference / $secs;

			if ($d >= 1) {
				$t = round($d);
				return $t . ' ' . $str . ($t > 1 ? 's' : '') . ' ago';
			}
		}
	}

	private function get_time_to($time)
	{
		$time_difference = $time - time();

		if ($time_difference < 1) {
			return 'in less than 1 second';
		}
		$condition = array(
			12 * 30 * 24 * 60 * 60 =>  'year',
			30 * 24 * 60 * 60       =>  'month',
			24 * 60 * 60            =>  'day',
			60 * 60                 =>  'hour',
			60                      =>  'minute',
			1                       =>  'second'
		);

		foreach ($condition as $secs => $str) {
			$d = $time_difference / $secs;

			if ($d >= 1) {
				$t = round($d);
				return 'in ' . $t . ' ' . $str . ($t > 1 ? 's' : '');
			}
		}
	}

	public function ajax_get_health_panel()
	{

		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		if (isset($_POST['siteId'])) {
			$site_id = sanitize_text_field($_POST['siteId']);
		}

		$health_data = $this->get_health_data($site_id);

		if (!$health_data) {
			$health_data_status = false;
			$health_data_info = false;
			$health_data_timestamp = false;
		} else {
			$health_data_status = $health_data['data']['site_status']['direct'];

			$automatic_logon = get_post_meta($site_id, 'wp_juggler_automatic_login', true) == "on" ? true : false;
			$access_user = get_post_meta($site_id, 'wp_juggler_login_username', true);
			$api_key = get_post_meta($site_id, 'wp_juggler_api_key', true);
			$site_url = get_post_meta($site_id, 'wp_juggler_server_site_url', true);


			if ($automatic_logon && $access_user && $api_key) {

				$access_token = WPJS_Service::wpjs_generate_login_token($access_user, $api_key);
				$final_url = WPJS_Service::add_query_var_to_url(rtrim($site_url, '/') . '/wpjs/', 'wpjs_token', $access_token);

				$health_data_status = $this->replaceHealthHref($health_data_status, parse_url($site_url, PHP_URL_HOST), $final_url);
			}

			$final_debug_array = array();

			foreach ($health_data['data']['debug'] as $key => $value) {
				//$value->slug = $key;
				$fin_value = $value;

				$fields_obj = $value['fields'];
				$final_fields = array();

				foreach ($fields_obj as $key_obj => $value_obj) {
					$field_fin_value = $value_obj;
					$field_fin_value['slug'] = $key_obj;
					$final_fields[] = $field_fin_value;
				}

				$fin_value['fields'] = $final_fields;

				$fin_value['slug'] = $key;
				$final_debug_array[] = $fin_value;
			}

			$health_data_info = $final_debug_array;
			$health_data_core = $health_data['data']['core_checksum'];
			$health_data_timestamp = $this->get_time_ago($health_data['timestamp']);
			$debug_data_timestamp = $this->get_time_ago($health_data['timestamp_debug']);
			$core_checksum_data_timestamp = $this->get_time_ago($health_data['timestamp_core_checksum']);
			$health_data_upgrade = get_post_meta($site_id, 'wp_juggler_wordpress_update_version', true);
			$health_data_upgrade = $health_data_upgrade == '' ? false : $health_data_upgrade;

			if( $health_data_upgrade !== false ){
				$automatic_logon = get_post_meta($site_id, 'wp_juggler_automatic_login', true) == "on" ? true : false;
				$access_user = get_post_meta($site_id, 'wp_juggler_login_username', true);
				$api_key = get_post_meta($site_id, 'wp_juggler_api_key', true);
				if ($automatic_logon && $access_user && $api_key) {
					$access_token = WPJS_Service::wpjs_generate_login_token($access_user, $api_key);
					$final_url = WPJS_Service::add_query_var_to_url(rtrim($site_url, '/') . '/wpjs/', 'wpjs_token', $access_token);
					$health_data_upgrade_url = WPJS_Service::add_query_var_to_url($final_url, 'wpjs_redirect', rtrim($site_url, '/') . '/wp-admin/update-core.php');
				} else {
					$health_data_upgrade_url = rtrim($site_url, '/') . '/wp-admin/update-core.php';
				}
			} else {
				$health_data_upgrade_url = false;
			}
		}

		$newsite = array(
			'wp_juggler_health_data_status' => $health_data_status,
			'wp_juggler_health_data_info' => $health_data_info,
			'wp_juggler_health_data_timestamp' => $health_data_timestamp,
			'wp_juggler_debug_data_timestamp' => $debug_data_timestamp,
			'wp_juggler_core_checksum_data_timestamp' => $core_checksum_data_timestamp,
			'wp_juggler_health_data_core' => $health_data_core,
			'wp_juggler_health_data_upgrade' => $health_data_upgrade,
			'wp_juggler_health_data_upgrade_url' => $health_data_upgrade_url
		);

		$data[] = $newsite;

		wp_send_json_success($data, 200);
	}

	private function replaceHealthHref($array, $domain, $final_url)
	{
		foreach ($array as &$item) {
			if (array_key_exists('actions', $item)) {
				$item['actions'] = preg_replace_callback(
					'/<a[^>]*href=["\'](https?:\/\/(?:www\.)?' . preg_quote($domain, '/') . '\/[^"\']+)["\'][^>]*>/i',
					function ($matches) use ($final_url) {
						return str_replace($matches[1], $this->addTokenToUrl($matches[1], $final_url), $matches[0]);
					},
					$item['actions']
				);
			}
		}
		return $array;
	}

	private function replaceNoticeHref($array, $domain, $final_url)
	{
		foreach ($array as &$item) {
			if (array_key_exists('NoticeHTML', $item)) {
				$item['NoticeHTML'] = preg_replace_callback(
					'/<a[^>]*href=["\'](https?:\/\/(?:www\.)?' . preg_quote($domain, '/') . '\/[^"\']+)["\'][^>]*>/i',
					function ($matches) use ($final_url) {
						return str_replace($matches[1], $this->addTokenToUrl($matches[1], $final_url), $matches[0]);
					},
					$item['NoticeHTML']
				);
			}
		}
		return $array;
	}

	private function addTokenToUrl($currentHref, $final_url)
	{

		$tempurl = WPJS_Service::add_query_var_to_url($final_url, 'wpjs_redirect', $currentHref);

		return $tempurl;
	}


	public function ajax_get_control_panel()
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
		$data['sites_view'] = array();

		$tools_array = $this->load_wpjugglertools();

		foreach ($wpjuggler_sites as $site) {

			if ($this->can_user_access_post($site)) {

				$wp_version = get_post_meta($site->ID, 'wp_juggler_wordpress_version', true);
				$site_activation = get_post_meta($site->ID, 'wp_juggler_site_activation', true) == "on" ? true : false;
				$multisite = get_post_meta($site->ID, 'wp_juggler_multisite', true) == "on" ? true : false;
				$automatic_logon = get_post_meta($site->ID, 'wp_juggler_automatic_login', true) == "on" ? true : false;
				$site_url = get_post_meta($site->ID, 'wp_juggler_server_site_url', true);

				$uptime_stats = $this->get_uptime_stats($site->ID);

				$plugins_data = $this->get_latest_plugin_data($site->ID);

				if (!$plugins_data) {
					$updates_vul = false;
					$plugins_timestamp = false;
				} else {
					$updates_vul = $this->get_plugin_updates_and_vul($plugins_data['data']);
					$plugins_timestamp = $this->get_time_ago($plugins_data['timestamp']);
				}

				$themes_data = $this->get_latest_themes_data($site->ID);

				if (!$themes_data) {
					$themes_array = false;
				} else {
					$themes_array = $this->get_theme_updates($themes_data['data']);
				}

				if (!$plugins_data) {
					$plugins_checksum = false;
				} else {
					$plugins_checksum = 0;
					foreach ($plugins_data['data'] as $plugin) {
						if (isset($plugin['Checksum']) && !$plugin['Checksum'] && ( $plugin['Version'] == $plugin['ChecksumVersion'])) {
							$plugins_checksum++;
						}
					}
				}

				// Check
				$health_data = $this->get_health_data($site->ID);

				if (!$health_data) {
					$health_data_issues = false;
					$health_data_timestamp = false;
					$core_checksum = false;
					$health_data_count = false;
				} else {

					$core_checksum = $health_data['data']['core_checksum'];
					// TODO zameniti sledeci red prebrajanjem statusa
					$health_data_issues = $health_data['data']['site_status']['issues'];
					$health_data_timestamp = $this->get_time_ago($health_data['timestamp']);

					$health_data_count = [
						"good" => 0,
						"recommended" => 0,
						"critical" => 0
					];

					foreach ($health_data['data']['site_status']['direct'] as $obj) {
						if (isset($health_data_count[$obj['status']])) {
							$health_data_count[$obj['status']]++;
						} else {
							$health_data_count[$obj['status']] = 1;
						}
					}
				}

				$notices_data = $this->get_latest_notices($site->ID);

				if (!$notices_data['wp_juggler_notices_timestamp'] || is_null($notices_data['wp_juggler_notices']) || !is_array($notices_data['wp_juggler_notices'])) {
					$notices_data_number = false;
					$notices_data_timestamp = false;
				} else {
					$notices_data_number = count($notices_data['wp_juggler_notices']);
					$notices_data_timestamp = $notices_data['wp_juggler_notices_timestamp'];
				}

				$newsite = array(
					'id' => $site->ID,
					'title' => get_the_title($site->ID),
					'wp_juggler_server_site_url' => $site_url,
					'wp_juggler_multisite' => $multisite,
					'wp_juggler_wordpress_version' => $wp_version,
					'wp_juggler_site_activation' => $site_activation,
					'wp_juggler_automatic_login' => false,
					'wp_juggler_uptime_stats' => $uptime_stats,
					'wp_juggler_plugins_summary' => $updates_vul,
					'wp_juggler_plugins_summary_timestamp' => $plugins_timestamp,
					'wp_juggler_themes_summary' => $themes_array,
					//'wp_juggler_themes_summary_timestamp' => $themes_timestamp,
					'wp_juggler_plugins_checksum' => $plugins_checksum,
					//'wp_juggler_plugins_checksum_timestamp' => $plugins_checksum_timestamp,
					'wp_juggler_core_checksum' => $core_checksum,
					'wp_juggler_health_data_issues' => $health_data_issues,
					'wp_juggler_health_data_count' => $health_data_count,
					'wp_juggler_health_data_timestamp' => $health_data_timestamp,
					'wp_juggler_notices_timestamp' => $notices_data_timestamp,
					'wp_juggler_notices_count' => $notices_data_number
				);

				if ($site_activation) {

					$access_token = false;
					$access_user = get_post_meta($site->ID, 'wp_juggler_login_username', true);
					$api_key = get_post_meta($site->ID, 'wp_juggler_api_key', true);

					$related_tools = $this->get_related_wpjugglertools($site->ID, $tools_array);

					if ($automatic_logon && $access_user && $api_key) {

						$access_token = WPJS_Service::wpjs_generate_login_token($access_user, $api_key);
						$final_url = WPJS_Service::add_query_var_to_url(rtrim($site_url, '/') . '/wpjs/', 'wpjs_token', $access_token);

						$newsite['wp_juggler_automatic_login'] = true;
						$newsite['wp_juggler_login_url'] = $final_url;
						$newsite['wp_juggler_login_tools'] = array();

						foreach ($related_tools as $tool) {
							$newsite['wp_juggler_login_tools'][] = array(
								'wp_juggler_tool_label' => $tool['wp_juggler_tool_label'],
								'wp_juggler_tool_url' => WPJS_Service::add_query_var_to_url($final_url, 'wpjs_redirect', rtrim($site_url, '/') . '/' . ltrim($tool['wp_juggler_tool_url'], '/'))
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

				$data['sites_view'][] = $newsite;
			}
		}

		$data['plugins_view'] = $this->get_plugin_view();

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

	private function get_wpjs_cron_schedules()
	{
		$schedules = wp_get_schedules();
		$wpjs_schedules = array();

		foreach ($schedules as $name => $details) {
			if (strpos($name, 'wpjs_') === 0) {
				$wpjs_schedules[$name] = $details['display'];
			}
		}

		return $wpjs_schedules;
	}

	public function ajax_refresh_plugins()
	{

		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		if (isset($_POST['siteId'])) {
			$site_id = sanitize_text_field($_POST['siteId']);
		}

		$response_api = WPJS_Service::check_plugins_api($site_id);

		if (is_wp_error($response_api)) {

			$response = [
				'code' => $response_api->get_error_code(),
				//'message' => $response_api->get_error_message(),
				'message' => 'No valid response from client WP Juggler Instance',
				'data' => $response_api->get_error_data(),
			];

			wp_send_json_error($response);
		} else {

			$data = [];
			wp_send_json_success($data, 200);
		}
	}

	public function ajax_refresh_plugins_checksum()
	{

		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		if (isset($_POST['siteId'])) {
			$site_id = sanitize_text_field($_POST['siteId']);
		}

		$response_api = WPJS_Service::check_plugins_checksum_api($site_id);

		if (is_wp_error($response_api)) {

			$response = [
				'code' => $response_api->get_error_code(),
				//'message' => $response_api->get_error_message(),
				'message' => 'No valid response from client WP Juggler Instance',
				'data' => $response_api->get_error_data(),
			];

			wp_send_json_error($response);
		} else {

			$data = [];
			wp_send_json_success($data, 200);
		}
	}

	public function ajax_refresh_health()
	{

		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		if (isset($_POST['siteId'])) {
			$site_id = sanitize_text_field($_POST['siteId']);
		}

		$response_api = WPJS_Service::check_health_api($site_id);

		if (is_wp_error($response_api)) {

			$response = [
				'code' => $response_api->get_error_code(),
				'message' => 'No valid response from client WP Juggler Instance',
				'data' => $response_api->get_error_data(),
			];

			wp_send_json_error($response);
		} else {

			$data = [];
			wp_send_json_success($data, 200);
		}
	}

	public function ajax_refresh_debug()
	{

		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		if (isset($_POST['siteId'])) {
			$site_id = sanitize_text_field($_POST['siteId']);
		}

		$response_api = WPJS_Service::check_debug_api($site_id);

		if (is_wp_error($response_api)) {

			$response = [
				'code' => $response_api->get_error_code(),
				'message' => 'No valid response from client WP Juggler Instance',
				'data' => $response_api->get_error_data(),
			];

			wp_send_json_error($response);
		} else {

			$data = [];
			wp_send_json_success($data, 200);
		}
	}

	public function ajax_refresh_core_checksum()
	{

		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		if (isset($_POST['siteId'])) {
			$site_id = sanitize_text_field($_POST['siteId']);
		}

		$response_api = WPJS_Service::check_core_checksum_api($site_id);

		if (is_wp_error($response_api)) {

			$response = [
				'code' => $response_api->get_error_code(),
				'message' => 'No valid response from client WP Juggler Instance',
				'data' => $response_api->get_error_data(),
			];

			wp_send_json_error($response);
		} else {

			$data = [];
			wp_send_json_success($data, 200);
		}
	}

	public function ajax_refresh_notices()
	{

		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		if (isset($_POST['siteId'])) {
			$site_id = sanitize_text_field($_POST['siteId']);
		}

		$response_api = WPJS_Service::check_notices_api($site_id);

		if (is_wp_error($response_api)) {

			$response = [
				'code' => $response_api->get_error_code(),
				'message' => 'No valid response from client WP Juggler Instance',
				'data' => $response_api->get_error_data(),
			];

			wp_send_json_error($response);
		} else {

			$data = [];
			wp_send_json_success($data, 200);
		}
	}

	public function ajax_update_plugin()
	{

		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		if (isset($_POST['siteId'])) {
			$site_id = sanitize_text_field($_POST['siteId']);
		}

		if (isset($_POST['pluginSlug'])) {
			$plugin_slug = sanitize_text_field($_POST['pluginSlug']);
		}

		if (isset($_POST['withoutRefresh'])) {
			$withoutRefresh = filter_var( sanitize_text_field($_POST['withoutRefresh']) , FILTER_VALIDATE_BOOLEAN);
		} else {
			$withoutRefresh = false;
		}

		$response_api = WPJS_Service::update_plugin($site_id, $plugin_slug);

		if (is_wp_error($response_api)) {

			$response = [
				'code' => $response_api->get_error_code(),
				'message' => 'No valid response from client WP Juggler Instance',
				'data' => $response_api->get_error_data(),
			];

			wp_send_json_error($response);
		} else {

			if( !$withoutRefresh ){
				$response_api = WPJS_Service::check_plugins_api($site_id);

				if (is_wp_error($response_api)) {

					$response = [
						'code' => $response_api->get_error_code(),
						'message' => 'No valid response from client WP Juggler Instance',
						'data' => $response_api->get_error_data(),
					];

					wp_send_json_error($response);
				}
			}
			$data = [];
			wp_send_json_success($data, 200);
		}
	}

	public function ajax_update_theme()
	{

		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		if (isset($_POST['siteId'])) {
			$site_id = sanitize_text_field($_POST['siteId']);
		}

		if (isset($_POST['themeSlug'])) {
			$theme_slug = sanitize_text_field($_POST['themeSlug']);
		}

		if (isset($_POST['withoutRefresh'])) {
			$withoutRefresh = filter_var( sanitize_text_field($_POST['withoutRefresh']) , FILTER_VALIDATE_BOOLEAN);
		} else {
			$withoutRefresh = false;
		}

		$response_api = WPJS_Service::update_theme($site_id, $theme_slug);

		if (is_wp_error($response_api)) {

			$response = [
				'code' => $response_api->get_error_code(),
				'message' => 'No valid response from client WP Juggler Instance',
				'data' => $response_api->get_error_data(),
			];

			wp_send_json_error($response);
		} else {

			if( !$withoutRefresh ){
				$response_api = WPJS_Service::check_plugins_api($site_id);

				if (is_wp_error($response_api)) {

					$response = [
						'code' => $response_api->get_error_code(),
						'message' => 'No valid response from client WP Juggler Instance',
						'data' => $response_api->get_error_data(),
					];

					wp_send_json_error($response);
				}
			}
			$data = [];
			wp_send_json_success($data, 200);
		}
	}

	public function ajax_deactivate_plugin()
	{

		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		if (isset($_POST['siteId'])) {
			$site_id = sanitize_text_field($_POST['siteId']);
		}

		if (isset($_POST['pluginSlug'])) {
			$plugin_slug = sanitize_text_field($_POST['pluginSlug']);
		}

		$response_api = WPJS_Service::deactivate_plugin($site_id, $plugin_slug);

		if (is_wp_error($response_api)) {

			$response = [
				'code' => $response_api->get_error_code(),
				'message' => 'No valid response from client WP Juggler Instance',
				'data' => $response_api->get_error_data(),
			];

			wp_send_json_error($response);
		} else {

			$data = [];
			wp_send_json_success($data, 200);
		}
	}

	public function ajax_activate_plugin()
	{

		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		if (isset($_POST['siteId'])) {
			$site_id = sanitize_text_field($_POST['siteId']);
		}

		if (isset($_POST['pluginSlug'])) {
			$plugin_slug = sanitize_text_field($_POST['pluginSlug']);
		}

		if (isset($_POST['networkWide'])) {
			$network_wide = filter_var($_POST['networkWide'], FILTER_VALIDATE_BOOLEAN);
		}

		$response_api = WPJS_Service::activate_plugin($site_id, $plugin_slug, $network_wide);

		if (is_wp_error($response_api)) {

			$response = [
				'code' => $response_api->get_error_code(),
				'message' => 'No valid response from client WP Juggler Instance',
				'data' => $response_api->get_error_data(),
			];

			wp_send_json_error($response);
		} else {

			$data = [];
			wp_send_json_success($data, 200);
		}
	}

	public function ajax_start_cron()
	{

		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		if (isset($_POST['hookSlug'])) {
			$hookSlug = sanitize_text_field($_POST['hookSlug']);
		}

		if ( $hookSlug == "wpjs_check_health_api"){
			$this->cron->check_all_health_api();
		}

		if ( $hookSlug == "wpjs_check_debug_api"){
			$this->cron->check_all_debug_api();
		}

		if ( $hookSlug == "wpjs_check_core_checksum_api"){
			$this->cron->check_all_core_checksum_api();
		}

		if ( $hookSlug == "wpjs_check_plugins_api"){
			$this->cron->check_all_plugins_api();
		}

		if ( $hookSlug == "wpjs_check_plugins_checksum_api"){
			$this->cron->check_all_plugins_checksum_api();
		}

		if ( $hookSlug == "wpjs_check_notices_api"){
			$this->cron->check_all_notices_api();
		}


		$data = [];
		wp_send_json_success($data, 200);
		
	}
}
