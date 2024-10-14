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

class WPJS_Mailer
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

	public static function wpjs_send_email( $arg = null )
	{
		$to = 'recipient@example.com';

		// Email subject
		$subject = 'Email subject';

		// Email message
		$message = WPJS_Mailer::fe_alert_start(array( 'site_name' => 'Novi' ));

		// Email headers
		$headers = array('Content-Type: text/html; charset=UTF-8');

		// Send the email
		wp_mail($to, $subject, $message, $headers);
	}

	public static function wpjs_send_fe_alert_start( $site_id )
	{
		$will_send = get_post_meta($site_id, 'wp_juggler_fe_down', true);

		if(!$will_send){
			update_post_meta($site_id, 'wp_juggler_fe_down', true);
			$serialized_datetime = serialize( new DateTime(current_time('mysql'), new DateTimeZone(wp_timezone_string())) );
			update_post_meta($site_id, 'wp_juggler_fe_down_time', $serialized_datetime );
		} else {
			return;
		}
		
		$site = get_post($site_id);
		$site_name = $site->post_title;
		$site_url = get_post_meta($site_id, 'wp_juggler_server_site_url', true);
		$saved_email_alerts = get_post_meta($site_id, 'wp_juggler_email_alerts', true);

		$args = array(
			'site_name' => $site_name,
			'site_url' => $site_url,
			'site_time' => current_time('g:i A')
		);
		$subject_template = "Web site {{ site_name }} is down!!!";
		$subject = WPJS_Mailer::parse_template( $subject_template, $args );
		$message_template = "Web Site {{ site_name }} ( url: {{ site_url }} ) stopped responding at {{ site_time }}";
		$message = WPJS_Mailer::parse_template( $message_template, $args );

		$headers = array('Content-Type: text/html; charset=UTF-8');

		if (is_array($saved_email_alerts) && !empty($saved_email_alerts)) {
			foreach ($saved_email_alerts as $email_alert) {
				$to = $email_alert;
				wp_mail($to, $subject, $message, $headers);
			}
		}
	}

	public static function wpjs_send_fe_alert_end( $site_id )
	{
		if (! metadata_exists('post', $site_id, 'wp_juggler_fe_down')){
			return;
		}

		$will_send = get_post_meta($site_id, 'wp_juggler_fe_down', true);

		if($will_send){
			$serialized_datetime = get_post_meta($site_id, 'wp_juggler_fe_down_time', true);
			if ($serialized_datetime) {
				$prev_time = unserialize($serialized_datetime);
			}

			if ($prev_time instanceof DateTime) {
				$curr_time = new DateTime(current_time('mysql'), new DateTimeZone(wp_timezone_string()));
				$interval = $prev_time->diff($curr_time);
				$readable_interval = $interval->format('%d days, %h hours, %i minutes, %s seconds');

				update_post_meta($site_id, 'wp_juggler_fe_down', false);
				$serialized_datetime = serialize( new DateTime(current_time('mysql'), new DateTimeZone(wp_timezone_string())) );
				update_post_meta($site_id, 'wp_juggler_fe_down_time', $serialized_datetime );
			} else {
				return;
			}
			
		} else {
			return;
		}
		
		$site = get_post($site_id);
		$site_name = $site->post_title;
		$site_url = get_post_meta($site_id, 'wp_juggler_server_site_url', true);
		$saved_email_alerts = get_post_meta($site_id, 'wp_juggler_email_alerts', true);

		$args = array(
			'site_name' => $site_name,
			'site_url' => $site_url,
			'site_time' => current_time('g:i A'),
			'interval' => $readable_interval
		);
		$subject_template = "Web site {{ site_name }} is up!!!";
		$subject = WPJS_Mailer::parse_template( $subject_template, $args );
		$message_template = "Web Site {{ site_name }} ( url: {{ site_url }} ) started responding at {{ site_time }}. The site was down for {{ interval }}";
		$message = WPJS_Mailer::parse_template( $message_template, $args );

		$headers = array('Content-Type: text/html; charset=UTF-8');

		if (is_array($saved_email_alerts) && !empty($saved_email_alerts)) {
			foreach ($saved_email_alerts as $email_alert) {
				$to = $email_alert;
				wp_mail($to, $subject, $message, $headers);
			}
		}
	}

	public static function parse_template($template, $args) {
		return preg_replace_callback('/\{\{\s*(\w+)\s*\}\}/', function($matches) use ($args) {
			$key = $matches[1];
			return isset($args[$key]) ? $args[$key] : $matches[0];
		}, $template);
	}

	
}
