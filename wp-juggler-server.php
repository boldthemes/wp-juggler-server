<?php
/**
 * WP Juggler Server
 *
 * This plugin improves upon the database search/replace functionality offered
 * by some other plugins- offering serialization support, the ability to
 * select specific tables, and the ability to run a dry run.
 *
 * @since             1.0.0
 * @package           WP_Juggler_Server
 *
 * @wordpress-plugin
 * Plugin Name:       WP Juggler Server
 * Plugin URI:        https://wpjuggler.com
 * Description:       WP Juggler Server
 * Version:           1.1.4
 * Author:            BoldThemes
 * Author URI:        https://wpjuggler.com
 * License:           GPL-3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       wp-juggler-server
 * Domain Path:       /languages
 * Network:			  true
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// If this file was called directly, abort.
if (! defined('WPINC')) {
    die;
}

if ( PHP_VERSION_ID < 70000 ) {
	exit;
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wp_juggler_server()
{
    // Allows for overriding the capability required to run the plugin.
    
    
    // Defines the path to the main plugin file.
    define('WPJS_FILE', __FILE__);

    // Defines the path to be used for includes.
    define('WPJS_PATH', plugin_dir_path(WPJS_FILE));

    // Defines the URL to the plugin.
    define('WPJS_URL', plugin_dir_url(WPJS_FILE));

    // Defines the current version of the plugin.
    define('WPJS_VERSION', '1.0.0');

    // Defines the name of the plugin.
    define('WPJS_NAME', 'WP Juggler Server');

    require WPJS_PATH . 'includes/class-wpjs-main.php';
    $plugin = new WP_Juggler_Server();
    $plugin->run();
}
run_wp_juggler_server();
