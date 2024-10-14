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
		$args = array(
			'site_name' => 'Moj site',
			'site_url' => 'mojsajt.com',
			'site_time' => current_time('g:i A')
		);
		$subject_template = "Web site {{ site_name }} is down!!!";
		$subject = WPJS_Mailer::parse_template( $subject_template, $args );
		$message_template = "Web Site {{ site_name }} ( url: {{ site_url }} ) stopped responding at {{ site_time }}";
		$message = WPJS_Mailer::parse_template( $message_template, $args );

		$headers = array('Content-Type: text/html; charset=UTF-8');

		$to = 'recipient@example.com';
		wp_mail($to, $subject, $message, $headers);
	}

	public static function parse_template($template, $args) {
		return preg_replace_callback('/\{\{\s*(\w+)\s*\}\}/', function($matches) use ($args) {
			$key = $matches[1];
			return isset($args[$key]) ? $args[$key] : $matches[0];
		}, $template);
	}

	
}
