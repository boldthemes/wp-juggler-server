<?php

use Tmeister\Firebase\JWT\JWT;
use Tmeister\Firebase\JWT\Key;

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

require_once WPJS_PATH . 'includes/api-classes/class-wpjs-plugin-checksum.php';

class WPJS_Service
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

	public static $plugin_checksum;

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
		WPJS_Service::$plugin_checksum = new WPJSPluginChecksum();
	}

	public static function wpjs_generate_login_token($username, $wpjs_api_key)
	{

		$algorithm = WPJS_Service::get_algorithm();

		if (!$wpjs_api_key || !$algorithm) {
			return false;
		}

		$issuedAt  = time();
		$expire    = $issuedAt + 30 * 60; // 30 minutes;

		$token = [
			'iat'  => $issuedAt,
			'exp'  => $expire
		];

		if ($username) {
			$token['wpjs_username'] = $username;
		}

		$token = WPJS\Firebase\JWT\JWT::encode(
			$token,
			$wpjs_api_key,
			$algorithm
		);

		return $token;
	}

	public static function add_query_var_to_url($url, $key, $value)
	{
		$parsed_url = wp_parse_url($url);
		$query_args = isset($parsed_url['query']) ? wp_parse_args($parsed_url['query']) : array();
		$query_args[$key] = $value;

		$new_query_string = build_query($query_args);

		return untrailingslashit($parsed_url['scheme'] . '://' . $parsed_url['host']) . (isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '') . $parsed_url['path'] . '?' . $new_query_string;
	}

	static private function get_algorithm()
	{
		$supported_algorithms = [
			'HS256',
			'HS384',
			'HS512',
			'RS256',
			'RS384',
			'RS512',
			'ES256',
			'ES384',
			'ES512',
			'PS256',
			'PS384',
			'PS512'
		];

		$algorithm = apply_filters('jwt_auth_algorithm', 'HS256');

		if (!in_array($algorithm, $supported_algorithms)) {
			return false;
		}

		return $algorithm;
	}

	static function call_client_api($site_id, $endpoint, $data)
	{

		$api_key = get_post_meta($site_id, 'wp_juggler_api_key', true);
		$site_url = get_post_meta($site_id, 'wp_juggler_server_site_url', true);

		if (!$site_url) {
			return false;
		}

		$url = rtrim($site_url, '/') . '/wp-json/juggler/v1/' . $endpoint;

		$response = wp_remote_post($url, array(
			'body'    => json_encode($data),
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_key,
				'Content-type' => 'application/json',
			),
			'timeout' => 30,

			// TODO SHOULD BE REMOVED FOR PRODUCTION !!!!!!!
			'sslverify'   => false
		));

		if (is_wp_error($response)) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code($response);

		if ($response_code != '200' && $response_code != '201') {

			$body = wp_remote_retrieve_body($response);

			$data = json_decode($body, true);

			if (json_last_error() === JSON_ERROR_NONE) {

				if (array_key_exists('success', $data) && !$data['success']) {
					$error_code = $data['data'][0]['code'];
					$error_msg = $data['data'][0]['message'];
				} else {
					$error_code = $data['code'];
					$error_msg = $data['message'];
				}
				$response = new WP_Error($error_code, $error_msg);
			} else {
				$response = new WP_Error('Error: ' . $response_code, 'Error retreiveng data');
			}
		}

		return $response;
	}

	static function check_front_end($url, $domain, $string)
	{
		$parsed_url = parse_url($url);

		$parsed_domain = parse_url($domain);

		if (empty($parsed_url['host']) || strpos($parsed_url['host'], $parsed_domain['host']) === false) {
			return new WP_Error('Front End Check Failed', 'Domains must match');
		}

		$response = wp_remote_get($url, [
			'sslverify' => false,
			'timeout' 	=> 10
		]);

		if (is_wp_error($response)) {
			return $response;
		}

		if (!in_array(wp_remote_retrieve_response_code($response), [200, 201])) {
			return new WP_Error('Front End Check Failed', 'Error retreiving the page');
		}

		$content = wp_remote_retrieve_body($response);

		if ($string) {
			$normalized_content = preg_replace('/\s+/', ' ', $content);
			$normalized_string = preg_replace('/\s+/', ' ', wp_specialchars_decode($string));
			if (strpos($normalized_content, $normalized_string) === false) {
				return new WP_Error('Front End Check Failed', 'String not found');
			}
		}

		// Step 4: Return false if string was not found
		return true;
	}

	static function check_health_api($site_id)
	{

		$log_entry = array(
			'wpjugglersites_id' => $site_id,
			'log_type' => 'checkHealth',
			'log_result' => 'init'
		);

		$task_id = WPJS_Cron_Log::insert_log($log_entry);

		$data = [
			'taskType' => 'checkHealth',
			'taskId' => $task_id
		];

		$response = WPJS_Service::call_client_api($site_id, 'initiateTask', $data);

		if (is_wp_error($response)) {

			$log_entry = array(
				'ID' => $task_id,
				'log_result' => 'fail',
				'log_value' =>  $response->get_error_message()
			);

			$task_id = WPJS_Cron_Log::update_log($log_entry);
		} else {

			$body = json_decode(wp_remote_retrieve_body($response), true);

			if ($body['data']['update_version']) {
				update_post_meta($site_id, 'wp_juggler_wordpress_update_version', sanitize_text_field($body['data']['update_version']));
			} else {
				delete_post_meta($site_id, 'wp_juggler_wordpress_update_version');
			}

			update_post_meta($site_id, 'wp_juggler_wordpress_version',  sanitize_text_field($body['data']['wp_version']));

			$log_entry = array(
				'ID' => $task_id,
				'log_result' => 'succ',
				'log_value' =>  null,
				'log_data' => json_encode($body['data'])
			);

			WPJS_Cron_Log::update_log($log_entry);
		}

		return $response;
	}

	static function check_debug_api($site_id)
	{

		$log_entry = array(
			'wpjugglersites_id' => $site_id,
			'log_type' => 'checkDebug',
			'log_result' => 'init'
		);

		$task_id = WPJS_Cron_Log::insert_log($log_entry);

		$data = [
			'taskType' => 'checkDebug',
			'taskId' => $task_id
		];

		$response = WPJS_Service::call_client_api($site_id, 'initiateTask', $data);

		if (is_wp_error($response)) {

			$log_entry = array(
				'ID' => $task_id,
				'log_result' => 'fail',
				'log_value' =>  $response->get_error_message()
			);

			$task_id = WPJS_Cron_Log::update_log($log_entry);
		} else {

			$body = json_decode(wp_remote_retrieve_body($response), true);

			$log_entry = array(
				'ID' => $task_id,
				'log_result' => 'succ',
				'log_value' =>  null,
				'log_data' => json_encode($body['data'])
			);

			WPJS_Cron_Log::update_log($log_entry);
		}

		return $response;
	}

	static function check_core_checksum_api($site_id)
	{

		$log_entry = array(
			'wpjugglersites_id' => $site_id,
			'log_type' => 'checkCoreChecksum',
			'log_result' => 'init'
		);

		$task_id = WPJS_Cron_Log::insert_log($log_entry);

		$data = [
			'taskType' => 'checkCoreChecksum',
			'taskId' => $task_id
		];

		$response = WPJS_Service::call_client_api($site_id, 'initiateTask', $data);

		if (is_wp_error($response)) {

			$log_entry = array(
				'ID' => $task_id,
				'log_result' => 'fail',
				'log_value' =>  $response->get_error_message()
			);

			$task_id = WPJS_Cron_Log::update_log($log_entry);
		} else {

			$body = json_decode(wp_remote_retrieve_body($response), true);

			$log_entry = array(
				'ID' => $task_id,
				'log_result' => 'succ',
				'log_value' =>  null,
				'log_data' => json_encode($body['data'])
			);

			WPJS_Cron_Log::update_log($log_entry);
		}

		return $response;
	}

	static function check_plugins_api($site_id)
	{

		$log_entry = array(
			'wpjugglersites_id' => $site_id,
			'log_type' => 'checkPlugins',
			'log_result' => 'init'
		);

		$task_id = WPJS_Cron_Log::insert_log($log_entry);

		$data = [
			'taskType' => 'checkPlugins',
			'taskId' => $task_id
		];

		$response = WPJS_Service::call_client_api($site_id, 'initiateTask', $data);

		if (is_wp_error($response)) {

			$log_entry = array(
				'ID' => $task_id,
				'log_result' => 'fail',
				'log_value' =>  $response->get_error_message()
			);

			$task_id = WPJS_Cron_Log::update_log($log_entry);
		} else {

			$body = json_decode(wp_remote_retrieve_body($response), true);


			$plugins = $body['data']['plugins_data'];
			$themes = $body['data']['themes_data'];

			foreach ($plugins as $plugin => $plugininfo) {
				$plugin_vulnerabilities = WPJS_Service::get_plugin_vulnerabilities($plugininfo['Slug'], $plugininfo['Version']);
				$plugins[$plugin]['Vulnerabilities'] = $plugin_vulnerabilities;
			}

			$log_entry = array(
				'ID' => $task_id,
				'log_result' => 'succ',
				'log_value' =>  null,
				'log_data' => json_encode(
					array(
						'plugins_data' => $plugins,
						'themes_data' => $themes
					)
				)
			);

			WPJS_Cron_Log::update_log($log_entry);
		}

		return $response;
	}

	static function check_plugins_checksum_api($site_id)
	{

		$log_entry = array(
			'wpjugglersites_id' => $site_id,
			'log_type' => 'checkPluginChecksum',
			'log_result' => 'init'
		);

		$task_id = WPJS_Cron_Log::insert_log($log_entry);

		$data = [
			'taskType' => 'checkPluginChecksum',
			'taskId' => $task_id
		];

		$response = WPJS_Service::call_client_api($site_id, 'initiateTask', $data);

		if (is_wp_error($response)) {

			$log_entry = array(
				'ID' => $task_id,
				'log_result' => 'fail',
				'log_value' =>  $response->get_error_message()
			);

			$task_id = WPJS_Cron_Log::update_log($log_entry);
		} else {

			$body = json_decode(wp_remote_retrieve_body($response), true);

			$plugins = $body['data'];

			foreach ($plugins as $plugin => $plugininfo) {
				if ($plugininfo['ChecksumFiles']) {
					$cf = base64_decode($plugininfo['ChecksumFiles']);
					$unzipped = gzuncompress($cf);
					$plugins[$plugin]['ChecksumFiles'] = json_decode($unzipped, true);
				} else {
					$plugins[$plugin]['ChecksumFiles'] = false;
				}
			}

			$data_checksum = WPJS_Service::$plugin_checksum->wpjs_plugin_checksum($plugins);

			foreach ($plugins as $plugin => $plugininfo) {
				$slug = WPJS_Service::get_plugin_name($plugin);
				$plugins[$plugin]['ChecksumDetails'] = [];
				$plugins[$plugin]['Slug'] = $slug;
				if (in_array($slug, $data_checksum['failures_list'])) {
					$plugins[$plugin]['Checksum'] = false;
				} else {
					$plugins[$plugin]['Checksum'] = true;
				}
			}

			foreach ($data_checksum['failures_details'] as $failure) {
				$plugin_file = WPJS_Service::findElementByAttribute($plugins, 'Slug', $failure['plugin_name']);
				$plugins[$plugin_file]['ChecksumDetails'][] = $failure;
			}

			foreach ($plugins as $plugin => $plugininfo) {
				unset($plugins[$plugin]['ChecksumFiles']);
				unset($plugins[$plugin]['Slug']);
			}

			$log_entry = array(
				'ID' => $task_id,
				'log_result' => 'succ',
				'log_value' =>  null,
				'log_data' => json_encode(
					array(
						'plugins_data' => $plugins
					)
				)
			);

			WPJS_Cron_Log::update_log($log_entry);
		}

		return $response;
	}

	static function check_notices_api($site_id)
	{

		$log_entry = array(
			'wpjugglersites_id' => $site_id,
			'log_type' => 'checkNotices',
			'log_result' => 'init'
		);

		$task_id = WPJS_Cron_Log::insert_log($log_entry);

		$data = [
			'taskType' => 'checkNotices',
			'taskId' => $task_id
		];

		$response = WPJS_Service::call_client_api($site_id, 'initiateTask', $data);

		if (is_wp_error($response)) {

			$log_entry = array(
				'ID' => $task_id,
				'log_result' => 'fail',
				'log_value' =>  $response->get_error_message()
			);

			$task_id = WPJS_Cron_Log::update_log($log_entry);
		} else {

			$body = json_decode(wp_remote_retrieve_body($response), true);

			$log_entry = array(
				'ID' => $task_id,
				'log_result' => 'succ',
				'log_value' =>  null,
				'log_data' => json_encode($body['data'])
			);

			WPJS_Cron_Log::update_log($log_entry);
		}

		return $response;
	}

	static function findElementByAttribute($array, $attribute, $value)
	{
		foreach ($array as $key => $element) {
			if (isset($element[$attribute]) && $element[$attribute] == $value) {
				return $key;
			}
		}
		return null;
	}

	static function update_plugin($site_id, $plugin_slug)
	{

		/* $log_entry = array(
			'wpjugglersites_id' => $site_id,
			'log_type' => 'checkThemes',
			'log_result' => 'init'
		); 

		$task_id = WPJS_Cron_Log::insert_log($log_entry); */

		$data = [
			'pluginSlug' => $plugin_slug
		];

		$response = WPJS_Service::call_client_api($site_id, 'updatePlugin', $data);

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (!is_wp_error($response)) {
			
			$plugins = $body['data'];
			$checksum = false;

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

			$plugin_key = array_key_first($plugins['plugins_data']);
		
			$checksum_array[$plugin_key] = array(
				'Slug' => $plugins['plugins_data'][$plugin_key]['Slug'],
				'ChecksumFiles' => $plugins['plugins_data'][$plugin_key]['ChecksumFiles'],
				'ChecksumVersion' => $plugins['plugins_data'][$plugin_key]['ChecksumVersion']
			);

			unset($plugins['plugins_data'][$plugin_key]['ChecksumFiles']);

			$log_data = json_decode($result['log_data'], true);

			$plugin_vulnerabilities = WPJS_Service::get_plugin_vulnerabilities($plugins['plugins_data'][$plugin_key]['Slug'], $plugins['plugins_data'][$plugin_key]['Version']);
			$plugins['plugins_data'][$plugin_key]['Vulnerabilities'] = $plugin_vulnerabilities;

			$log_data['plugins_data'][$plugin_key] = $plugins['plugins_data'][$plugin_key];

			$log_entry = array(
				'ID' => $result['ID'],
				'log_data' =>  json_encode($log_data)
			);

			$task_id = WPJS_Cron_Log::update_log($log_entry);

			if( $checksum_array[$plugin_key]['ChecksumFiles'] ){
				$cf = base64_decode($checksum_array[$plugin_key]['ChecksumFiles']);
				$unzipped = gzuncompress($cf);
				$checksum_array[$plugin_key]['ChecksumFiles'] = json_decode( $unzipped, true );
			} else {
				$checksum_array[$plugin_key]['ChecksumFiles'] = false;
			}

			$data_checksum = WPJS_Service::$plugin_checksum->wpjs_plugin_checksum( $checksum_array );

			foreach ($checksum_array as $plugin => $plugininfo) {
				$slug = WPJS_Service::get_plugin_name($plugin);
				$checksum_array[$plugin]['ChecksumDetails'] = [];
				$checksum_array[$plugin]['Slug'] = $slug;
				if( in_array( $slug, $data_checksum['failures_list'] )){
					$checksum_array[$plugin]['Checksum'] = false;
				} else {
					$checksum_array[$plugin]['Checksum'] = true;
				}
			}

			foreach ($data_checksum['failures_details'] as $failure){
				$plugin_file = WPJS_Service::findElementByAttribute($checksum_array, 'Slug', $failure['plugin_name']);
				$checksum_array[$plugin_file]['ChecksumDetails'][] = $failure;
			}

			foreach ($checksum_array as $plugin => $plugininfo) {
				unset($checksum_array[$plugin]['ChecksumFiles']);
				unset($checksum_array[$plugin]['Slug']);
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
					$site_id
				),
				ARRAY_A
			);

			if(!$result_checksum) {
				$log_data_checksum_array = [];
			} else {
				$log_data_checksum_array = json_decode($result_checksum['log_data'], true);
			}
			
			$log_data_checksum_array['plugins_data'][$plugin_key] = $checksum_array[$plugin_key];


			$log_entry = array(
				'ID' => $result_checksum['ID'],
				'log_data' =>  json_encode($log_data_checksum_array)
			);

			$task_id = WPJS_Cron_Log::update_log($log_entry);

		}

		return $response;
	}

	static function update_theme($site_id, $theme_slug)
	{

		$data = [
			'themeSlug' => $theme_slug
		];

		$response = WPJS_Service::call_client_api($site_id, 'updateTheme', $data);

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (!is_wp_error($response)) {
			
			$themes = $body['data'];

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

			$theme_key = array_key_first($themes['themes_data']);

			$log_data = json_decode($result['log_data'], true);

			$log_data['themes_data'][$theme_key] = $themes['themes_data'][$theme_key];

			$log_entry = array(
				'ID' => $result['ID'],
				'log_data' =>  json_encode($log_data)
			);

			$task_id = WPJS_Cron_Log::update_log($log_entry);

		}

		return $response;
	}

	static function deactivate_plugin($site_id, $plugin_slug)
	{

		$data = [
			'pluginSlug' => $plugin_slug
		];

		$response = WPJS_Service::call_client_api($site_id, 'deactivatePlugin', $data);

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (!is_wp_error($response)) {
			
			$plugins = $body['data'];

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

			$log_data = json_decode($result['log_data'], true);
			$final_log_data = array_replace_recursive($log_data, $plugins);

			$log_entry = array(
				'ID' => $result['ID'],
				'log_data' =>  json_encode($final_log_data)
			);

			$task_id = WPJS_Cron_Log::update_log($log_entry);

		}

		return $response;
	}

	static function activate_plugin($site_id, $plugin_slug, $network_wide = false)
	{

		$data = [
			'pluginSlug' => $plugin_slug,
			'networkWide' => $network_wide
		];

		$response = WPJS_Service::call_client_api($site_id, 'activatePlugin', $data);

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (!is_wp_error($response)) {
			
			$plugins = $body['data'];

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

			$log_data = json_decode($result['log_data'], true);
			$final_log_data = array_replace_recursive($log_data, $plugins);

			$log_entry = array(
				'ID' => $result['ID'],
				'log_data' =>  json_encode($final_log_data)
			);

			$task_id = WPJS_Cron_Log::update_log($log_entry);

		}

		return $response;
	}

	static function get_plugin_vulnerabilities($plugin_slug, $plugin_version)
	{
		$cache_key = 'vulnerabilities/' . $plugin_slug . '/' . $plugin_version;

		$relevant_vulnerabilities = get_transient($cache_key);

		if (false === $relevant_vulnerabilities) {

			$api_url = "https://www.wpvulnerability.net/plugin/{$plugin_slug}";
			$response = wp_remote_get($api_url, [
				'timeout' => 10
			]);

			if (is_wp_error($response)) {
				return [];  // Return an empty array on error.
			}

			$body = wp_remote_retrieve_body($response);
			$data = json_decode($body, true);

			if (!isset($data['data']['vulnerability'])) {
				return [];  // Return an empty array if no vulnerabilities are found.
			}

			$vulnerabilities = $data['data']['vulnerability'];
			$relevant_vulnerabilities = [];

			foreach ($vulnerabilities as $vulnerability) {
				$max_version = $vulnerability['operator']['max_version'] ?? null;
				$max_operator = $vulnerability['operator']['max_operator'] ?? null;

				if ($max_version && version_compare($plugin_version, $max_version, $max_operator)) {
					$relevant_vulnerabilities[] = $vulnerability;
				}
			}
		}

		return $relevant_vulnerabilities;
	}

	static function get_plugin_name($basename)
	{
		if (false === strpos($basename, '/')) {
			$name = basename($basename, '.php');
		} else {
			$name = dirname($basename);
		}

		if ('hello' === $name) {
			$name = 'hello-dolly';
		}

		return $name;
	}
}
