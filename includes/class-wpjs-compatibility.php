<?php

/**
 * Processes compatibility functionality.
 * @since      1.0
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/includes
 */

// Prevent direct access.
if ( ! defined( 'WPJS_PATH' ) ) exit;

class WPJS_Compatibility {

	/**
	 * Returns the system info.
	 * @access public
	 * @return string
	 */
	public static function get_sysinfo() {

		global $wpdb;

		$return = '### Begin System Info ###' . "\n\n<br>";

		// Basic site info
		$return .= '-- WordPress Configuration' . "\n\n<br>";
		$return .= 'Site URL:                 ' . site_url() . "\n<br>";
		$return .= 'Home URL:                 ' . home_url() . "\n<br>";
		$return .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n<br>";
		$return .= 'Version:                  ' . get_bloginfo( 'version' ) . "\n<br>";
		$return .= 'Language:                 ' . get_locale() . "\n<br>";
		$return .= 'Table Prefix:             ' . 'Length: ' . strlen( $wpdb->prefix ) . "\n<br>";
		$return .= 'WP_DEBUG:                 ' . ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n<br>";
		$return .= 'Memory Limit:             ' . WP_MEMORY_LIMIT . "\n<br>";

		// Plugin Configuration
		$return .= "\n<br>" . '-- Better Search Replace Configuration' . "\n\n<br>";
		$return .= 'Plugin Version:           ' . WPJS_VERSION . "\n<br>";
		$db      = new WPJS_DB();
		$return .= 'Max Page Size:            ' . $db->get_page_size() . "\n<br>";

		// Server Configuration
		$return .= "\n<br>" . '-- Server Configuration' . "\n\n<br>";
		$os = self::get_os();
		$return .= 'Operating System:         ' . $os['name'] . "\n<br>";
		$return .= 'PHP Version:              ' . PHP_VERSION . "\n<br>";
		$return .= 'MySQL Version:            ' . $wpdb->db_version() . "\n<br>";

		$return .= 'Server Software:          ' . $_SERVER['SERVER_SOFTWARE'] . "\n<br>";

		// PHP configs... now we're getting to the important stuff
		$return .= "\n<br>" . '-- PHP Configuration' . "\n\n<br>";
		$return .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n<br>";
		$return .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n<br>";
		$return .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n<br>";
		$return .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n<br>";
		$return .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n<br>";
		$return .= 'Display Errors:           ' . ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n<br>";

		// WordPress active plugins
		$return .= "\n<br>" . '-- WordPress Active Plugins' . "\n\n<br>";
		$plugins = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );
		foreach( $plugins as $plugin_path => $plugin ) {
			if( !in_array( $plugin_path, $active_plugins ) )
				continue;
			$return .= $plugin['Name'] . ': ' . $plugin['Version'] . "\n<br>";
		}

		// WordPress inactive plugins
		$return .= "\n<br>" . '-- WordPress Inactive Plugins' . "\n\n<br>";
		foreach( $plugins as $plugin_path => $plugin ) {
			if( in_array( $plugin_path, $active_plugins ) )
				continue;
			$return .= $plugin['Name'] . ': ' . $plugin['Version'] . "\n<br>";
		}

		if( is_multisite() ) {
			// WordPress Multisite active plugins
			$return .= "\n<br>" . '-- Network Active Plugins' . "\n\n<br>";
			$plugins = wp_get_active_network_plugins();
			$active_plugins = get_site_option( 'active_sitewide_plugins', array() );
			foreach( $plugins as $plugin_path ) {
				$plugin_base = plugin_basename( $plugin_path );
				if( !array_key_exists( $plugin_base, $active_plugins ) )
					continue;
				$plugin  = get_plugin_data( $plugin_path );
				$return .= $plugin['Name'] . ': ' . $plugin['Version'] . "\n<br>";
			}
		}

		$return .= "\n<br>" . '### End System Info ###';
		return $return;
	}

	/**
	 * Determines the current operating system.
	 * @access public
	 * @return array
	 */
	public static function get_os() {
		$os 		= array();
		$uname 		= php_uname( 's' );
		$os['code'] = strtoupper( substr( $uname, 0, 3 ) );
		$os['name'] = $uname;
		return $os;
	}

}
