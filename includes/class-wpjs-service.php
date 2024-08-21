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
			// TODO SHOULD BE REMOVED FOR PRODUCTION !!!!!!!
			'sslverify'   => false
		));

		$response_code = wp_remote_retrieve_response_code($response);

		if ($response_code != '200' && $response_code != '201') {

			$body = wp_remote_retrieve_body($response);

			$data = json_decode($body, true);

			if ( json_last_error() === JSON_ERROR_NONE ) {

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

		$response = wp_remote_get($url, [ 'sslverify'   => false ]);

		if (is_wp_error($response)) {
			return $response;
		}

		if (!in_array(wp_remote_retrieve_response_code($response), [200, 201])){
			return new WP_Error('Front End Check Failed', 'Error retreiving the page');
		}

		$content = wp_remote_retrieve_body($response);

		if ($string){
			$normalized_content = preg_replace('/\s+/', ' ', $content);
			$normalized_string = preg_replace('/\s+/', ' ', wp_specialchars_decode($string));
			if ( strpos($normalized_content, $normalized_string) === false) {
				return new WP_Error('Front End Check Failed', 'String not found');
			}
		}
		
		// Step 4: Return false if string was not found
		return true;
	}
}
