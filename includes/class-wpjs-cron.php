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

class WPJS_Cron
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

	public function wpjs_add_schedules($schedules){
			if(!isset($schedules["5min"])){
				$schedules["5min"] = array(
					'interval' => 5*60,
					'display' => __('Once every 5 minutes'));
			}
			if(!isset($schedules["30min"])){
				$schedules["30min"] = array(
					'interval' => 30*60,
					'display' => __('Once every 30 minutes'));
			}
			return $schedules;
	}

	public static function check_client_api( $site_id )
	{

		$response = WPJS_Service::call_client_api( $site_id, 'confirmClientApi', [] );

		if ( is_wp_error($response) ) {

			$log_entry = array(
				'wpjugglersites_id' => $site_id,
				'log_type' => 'check_client_api',
				'log_result' => 'fail',
				'log_value' =>  $response->get_error_message()
			);
		
		} else {

			$response_code = wp_remote_retrieve_response_code($response);
		
			switch ($response_code) {
				case 0:
					
					$log_entry = array(
						'wpjugglersites_id' => $site_id,
						'log_type' => 'check_client_api',
						'log_result' => 'error',
						'log_value' =>  'Remote client is unresponsive'
					);

					break;
				case 401:
					
					$log_entry = array(
						'wpjugglersites_id' => $site_id,
						'log_type' => 'check_client_api',
						'log_result' => 'error',
						'log_value' =>  '401 - You should check API key'
					);

					break;
				case 500:

					$log_entry = array(
						'wpjugglersites_id' => $site_id,
						'log_type' => 'check_client_api',
						'log_result' => 'error',
						'log_value' =>  '500 - Internal Server Error on remote client'
					);

					break;
				default:
					if ($response_code >= 400) {

						$log_entry = array(
							'wpjugglersites_id' => $site_id,
							'log_type' => 'check_client_api',
							'log_result' => 'error',
							'log_value' =>  $response_code . ' - Client error occurred'
						);
			
					} elseif ($response_code >= 500) {

						$log_entry = array(
							'wpjugglersites_id' => $site_id,
							'log_type' => 'check_client_api',
							'log_result' => 'error',
							'log_value' =>  $response_code . ' - Server error occurred'
						);

					} else {

						$log_entry = array(
							'wpjugglersites_id' => $site_id,
							'log_type' => 'check_client_api',
							'log_result' => 'succ', 
						);
					}
					break;
			}
		}

		WPJS_Cron_Log::insert_log($log_entry);
	}
}

class WPJS_Cron_Log
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

	static function create_database_table()
	{
		global $wpdb;

		$table_name = $wpdb->prefix . 'wpjs_cron_log';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
  			ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  			wpjugglersites_id varchar(256) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  			log_type varchar(256) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  			log_result varchar(256) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
			log_value varchar(256) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  			log_data json DEFAULT NULL,
  			log_time timestamp NOT NULL,
  			PRIMARY KEY  (ID),
			KEY wpjugglersites_id (wpjugglersites_id),
  			KEY log_type (log_type),
  			KEY log_result (log_result),
			KEY log_value (log_value),
  			KEY log_time (log_time)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($sql);

		$the_default_timestamp_query = "ALTER TABLE $table_name MODIFY COLUMN log_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;";

		$wpdb->query($the_default_timestamp_query);
	}

	static function insert_log($event)
	{
		global $wpdb;

		$table_name = $wpdb->prefix . 'wpjs_cron_log';

		if (array_key_exists('log_type', $event)) {

			do_action("wpjs/cron_log/" . $event["log_type"], $event);
		}

		$event_fil = apply_filters("wpjs/cron_log/insert", $event);

		if (array_key_exists('log_data', $event_fil)) {
			$log_data = json_decode($event_fil["log_data"]);
			if (json_last_error() !== JSON_ERROR_NONE) {
				$log_data_array = array(
					"data" => $event_fil["log_data"]
				);

				$log_data = json_encode($log_data_array);
				$event_fil["log_data"] = $log_data;
			}
		}

		$wpdb->insert(
			$table_name,
			$event_fil
		);

		if ($wpdb->last_error !== '') :
			$wpdb->print_error();
		endif;
	}

}
