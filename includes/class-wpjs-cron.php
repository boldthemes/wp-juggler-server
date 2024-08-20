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

	private $bg_process;

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
		$this->bg_process = new WPJS_Background_Process();
	
	}

	public function wpjs_init_scheduler(){
		add_filter( 'cron_schedules', [$this, 'wpjs_add_schedules'] );

		add_action( 'wpjs_check_client_api', [$this, 'check_client_api'] );

		if ( !wp_next_scheduled( 'wpjs_check_client_api' )) {
			wp_schedule_event( time(), '5min', 'wpjs_check_client_api' );
		}
	}

	public function wpjs_add_schedules($schedules){
			if(!isset($schedules["5min"])){
				$schedules["5min"] = array(
					'interval' => 5*60,
					'display' => __('Once every 5 minutes'));
			}
			if(!isset($schedules["10min"])){
				$schedules["10min"] = array(
					'interval' => 10*60,
					'display' => __('Once every 10 minutes'));
			}
			if(!isset($schedules["30min"])){
				$schedules["30min"] = array(
					'interval' => 30*60,
					'display' => __('Once every 30 minutes'));
			}
			return $schedules;
	}

	public function check_client_api()
	{

		$args = array(
			'post_type'  => 'wpjugglersites',
			'post_status'=> 'publish',
			'meta_key'   => 'wp_juggler_site_activation',
			'meta_value' => 'on',
			'fields'     => 'ids',
			'numberposts'=> -1
		);
		
		$site_ids = get_posts($args);

		foreach ( $site_ids as $site_id ) {
			$this->bg_process->push_to_queue( array(
				'site_id' => $site_id,
				'endpoint' => 'confirmClientApi',
				'data' => []
			));
		}

		$this->bg_process->save()->dispatch();

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
