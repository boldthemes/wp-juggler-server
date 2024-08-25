<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Registers styles and scripts, adds the custom administration page,
 * and processes user input on the "search/replace" form.
 *
 * @link       https://wpjuggler.com
 * @since      1.0.0
 *
 * @package    WP_Juggler_Server
 * @subpackage WP_Juggler_Server/includes
 */

// Prevent direct access.
if (! defined('WPJS_PATH')) exit;

class WPJS_Plugins
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
	}

	public function serve_manifest()
	{

		function get_full_current_url() {
			$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
			$full_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			return $full_url;
		}

		$current_url = get_full_current_url();

		$parsed_url = wp_parse_url( $current_url );

		// TODO - flow vezan za generisanje temp url-a

		if( $parsed_url['path'] != '/wpjs-plugins/' ){
			return;
		}

		$plugin_slug = (isset($_GET['wpjs_plugin_slug'])) ? sanitize_text_field($_GET['wpjs_plugin_slug']) : false;

		if( !$plugin_slug ){

			$args = array(
				'post_type'  => 'wpjugglerplugins',
				'post_status' => 'publish'
			);
		
			$plugins = get_posts($args);

			if (empty($plugins)) {
				return;
			}

			$update = [];

			foreach( $plugins as $plugin ){
				$obj = $this->build_object($plugin);
				$update[ $obj['slug'] ] = $obj;
			}

		} else {

			$args = array(
				'post_type'  => 'wpjugglerplugins',
				'post_status' => 'publish',
				'meta_query' => array(
					array(
						'key'   => 'wp_juggler_plugin_slug',
						'value' => $plugin_slug,
						'compare' => '='
					),
				),
			);
		
			$plugins = get_posts($args);
	
			if (!empty($plugins)) {
				$plugin = $plugins[0];
			} else {
				return;
			}

			$update = $this->build_object($plugin);

		}
		
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'HTTP/1.1 200 OK' );
		echo json_encode( $update );
		die();
	}

	private function build_object( $plugin ){

		$wp_juggler_plugin_version = get_post_meta($plugin->ID, 'wp_juggler_plugin_version', true);
		$wp_juggler_plugin_slug = get_post_meta($plugin->ID, 'wp_juggler_plugin_slug', true);
		$wp_juggler_plugin_author = get_post_meta($plugin->ID, 'wp_juggler_plugin_author', true);
		$wp_juggler_plugin_author_profile = get_post_meta($plugin->ID, 'wp_juggler_plugin_author_profile', true);
		$wp_juggler_plugin_donate_link = get_post_meta($plugin->ID, 'wp_juggler_plugin_donate_link', true);
		$wp_juggler_plugin_homepage = get_post_meta($plugin->ID, 'wp_juggler_plugin_homepage', true);
		$wp_juggler_plugin_download_file = get_post_meta($plugin->ID, 'wp_juggler_plugin_download_file', true);
		$wp_juggler_plugin_requires_wp = get_post_meta($plugin->ID, 'wp_juggler_plugin_requires_wp', true);
		$wp_juggler_plugin_tested_wp = get_post_meta($plugin->ID, 'wp_juggler_plugin_tested_wp', true);
		$wp_juggler_plugin_requires_php = get_post_meta($plugin->ID, 'wp_juggler_plugin_requires_php', true);
		$wp_juggler_plugin_last_updated = get_post_meta($plugin->ID, 'wp_juggler_plugin_last_updated', true);
		$wp_juggler_plugin_description = get_post_meta($plugin->ID, 'wp_juggler_plugin_description', true);
		$wp_juggler_plugin_installation = get_post_meta($plugin->ID, 'wp_juggler_plugin_installation', true);
		$wp_juggler_plugin_changelog = get_post_meta($plugin->ID, 'wp_juggler_plugin_changelog', true);
		$wp_juggler_plugin_banner_low = get_post_meta($plugin->ID, 'wp_juggler_plugin_banner_low', true);
		$wp_juggler_plugin_banner_high = get_post_meta($plugin->ID, 'wp_juggler_plugin_banner_high', true);

		$package_url = $wp_juggler_plugin_download_file ? wp_get_attachment_url($wp_juggler_plugin_download_file) : '';
		$banner_low = $wp_juggler_plugin_banner_low ? wp_get_attachment_url($wp_juggler_plugin_banner_low ) : '';
		$banner_high = $wp_juggler_plugin_banner_high ? wp_get_attachment_url($wp_juggler_plugin_banner_high ) : '';

		$update = array(
			'name' => $plugin->post_title,
			'slug' => $wp_juggler_plugin_slug,
			'author' => $wp_juggler_plugin_author,
			'author_profile' => $wp_juggler_plugin_author_profile,
			'donate_link' => $wp_juggler_plugin_donate_link,
			'homepage' => $wp_juggler_plugin_homepage,
			'version' => $wp_juggler_plugin_version,
			'download_url' => $package_url,
			'requires' => $wp_juggler_plugin_requires_wp,
			'tested' => $wp_juggler_plugin_tested_wp,
			'requires_php' => $wp_juggler_plugin_requires_php,
			'last_updated' => date('Y-m-d H:i:s', strtotime($wp_juggler_plugin_last_updated)),
			'sections' => array(
				'description' => wpautop($wp_juggler_plugin_description),
				'installation' => wpautop($wp_juggler_plugin_installation),
				'changelog' => wpautop($wp_juggler_plugin_changelog)
 			),
			'banners' => array(
				'low' => $banner_low,
				'high' => $banner_high
			)
		);

		return $update;

	}
}
