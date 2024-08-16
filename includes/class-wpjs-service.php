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
	
}
