<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0
 * @package    WP_Juggler_Server
 * @subpackage WP_Juggler_Server/includes
 */

// Prevent direct access.
if ( ! defined( 'WPJS_PATH' ) ) exit;

class WP_Juggler_Server {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0
	 * @access   protected
	 * @var      WPJS_Loader   $loader   Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
	 * the public-facing side of the site.
	 *
	 * @since    1.0
	 */
	public function __construct() {
		$this->plugin_name 	= 'wp-juggler-server';
		$this->version 		= WPJS_VERSION;
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once WPJS_PATH . 'vendor/autoload.php';
		require_once WPJS_PATH . 'includes/class-wpjs-wrapper.php';
		require_once WPJS_PATH . 'includes/class-wpjs-loader.php';
		require_once WPJS_PATH . 'includes/class-wpjs-i18n.php';
		require_once WPJS_PATH . 'includes/class-wpjs-admin.php';
		require_once WPJS_PATH . 'includes/class-wpjs-front-end.php';
		require_once WPJS_PATH . 'includes/class-wpjs-ajax.php';
		require_once WPJS_PATH . 'includes/class-wpjs-service.php';
		require_once WPJS_PATH . 'includes/class-wpjs-cron.php';
		require_once WPJS_PATH . 'includes/class-wpjs-api.php';
		require_once WPJS_PATH . 'includes/class-wpjs-background-process.php';
		require_once WPJS_PATH . 'includes/class-wpjs-plugins.php';
		require_once WPJS_PATH . 'includes/class-wpjs-github-updater.php';
		
		$this->loader = new WPJS_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WPJS_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new WPJS_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );
	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 * of the plugin.
	 *
	 * @since    1.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		// Initialize the admin class.
		$plugin_admin  = new WPJS_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_cron  = new WPJS_Cron( $this->get_plugin_name(), $this->get_version() );
		$plugin_ajax  = new WPJS_AJAX( $this->get_plugin_name(), $this->get_version(), $plugin_cron );
		$plugin_fe  = new WPJS_Front_End( $this->get_plugin_name(), $this->get_version() );
		$plugin_service  = new WPJS_Service( $this->get_plugin_name(), $this->get_version() );
		$plugin_api  = new WPJS_Api( $this->get_plugin_name(), $this->get_version(), $plugin_cron );
		$plugin_plugins  = new WPJS_Plugins( $this->get_plugin_name(), $this->get_version() );
		$plugin_github_updater  = new WPJS_Github_Updater( $this->get_plugin_name(), $this->get_version() );
		
		/// Register the admin pages and scripts.
		
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_menu_page', 9 );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_menu_page_end' );
		
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_plugin_assets' );

		//$this->loader->add_action( 'wp_enqueue_scripts', $plugin_admin, 'enqueue_control_panel_assets' );
		
		$this->loader->add_action( 'init', $plugin_admin, 'wpjs_cpt', 5 );

		$this->loader->add_filter( 'query_vars', $plugin_admin, 'wpjs_cp_query_vars' );
		$this->loader->add_action( 'init', $plugin_admin, 'wpjs_cp_rewrite_rule');
		$this->loader->add_action( 'template_redirect', $plugin_admin, 'wpjs_cp_template_redirect');
		//$this->loader->add_action( 'admin_init', $plugin_admin, 'wpjs_cp_redirect');

		$this->loader->add_action( 'add_meta_boxes_wpjugglersites', $plugin_admin, 'wpjs_sites_metaboxes' );
		$this->loader->add_action( 'add_meta_boxes_wpjugglerplugins', $plugin_admin, 'wpjs_plugins_metaboxes' );
		$this->loader->add_action( 'add_meta_boxes_wpjugglertools', $plugin_admin, 'wpjs_tools_metaboxes' );

		
		$this->loader->add_action( 'save_post_wpjugglersites', $plugin_admin, 'wpjs_save_sites_meta_boxes' );
		$this->loader->add_action( 'save_post_wpjugglerplugins', $plugin_admin, 'wpjs_save_plugins_meta_boxes' );
		$this->loader->add_action( 'save_post_wpjugglertools', $plugin_admin, 'wpjs_save_tools_meta_boxes' );

		$this->loader->add_filter( 'admin_menu', $plugin_admin, 'cp_hide_admin_menus', 9999 );

		$this->loader->add_filter( 'manage_wpjugglersites_posts_columns', $plugin_admin, 'wpjs_add_custom_column' );
		$this->loader->add_action( 'manage_wpjugglersites_posts_custom_column', $plugin_admin, 'wpjs_display_custom_column', 10, 2);

		//Ajax actions
		$this->loader->add_action( 'wp_ajax_juggler_user_search', $plugin_ajax, 'wpjs_user_search' );

		$this->loader->add_action( 'wp_ajax_wpjs_get_dashboard', $plugin_ajax, 'ajax_get_dashboard' );
		$this->loader->add_action( 'wp_ajax_wpjs-get-dashboard-history', $plugin_ajax, 'ajax_get_dashboard_history' );

		$this->loader->add_action( 'wp_ajax_wpjs_get_settings', $plugin_ajax, 'ajax_get_settings' );
		$this->loader->add_action( 'wp_ajax_wpjs_save_settings', $plugin_ajax, 'ajax_save_settings' );

		$this->loader->add_action( 'wp_ajax_wpjs_get_control_panel', $plugin_ajax, 'ajax_get_control_panel' );
		
		$this->loader->add_action( 'wp_ajax_wpjs-get-health-panel', $plugin_ajax, 'ajax_get_health_panel' );
		
		$this->loader->add_action( 'wp_ajax_wpjs-get-plugins-panel', $plugin_ajax, 'ajax_get_plugins_panel' );

		$this->loader->add_action( 'wp_ajax_wpjs-get-notices-panel', $plugin_ajax, 'ajax_get_latest_notices' );
		$this->loader->add_action( 'wp_ajax_wpjs-get-notices-history', $plugin_ajax, 'ajax_get_notices_history' );

		$this->loader->add_action( 'wp_ajax_wpjs-get-uptime-panel', $plugin_ajax, 'ajax_get_uptime_panel' );
		$this->loader->add_action( 'wp_ajax_wpjs-get-uptime-history', $plugin_ajax, 'ajax_get_uptime_history' );

		$this->loader->add_action( 'wp_ajax_wpjs-refresh-health', $plugin_ajax, 'ajax_refresh_health' );
		$this->loader->add_action( 'wp_ajax_wpjs-refresh-debug', $plugin_ajax, 'ajax_refresh_debug' );
		$this->loader->add_action( 'wp_ajax_wpjs-refresh-core-checksum', $plugin_ajax, 'ajax_refresh_core_checksum' );
		$this->loader->add_action( 'wp_ajax_wpjs-refresh-plugins', $plugin_ajax, 'ajax_refresh_plugins' );
		$this->loader->add_action( 'wp_ajax_wpjs-refresh-plugins-checksum', $plugin_ajax, 'ajax_refresh_plugins_checksum' );
		$this->loader->add_action( 'wp_ajax_wpjs-refresh-notices', $plugin_ajax, 'ajax_refresh_notices' );

		$this->loader->add_action( 'wp_ajax_wpjs-update-plugin', $plugin_ajax, 'ajax_update_plugin' );
		$this->loader->add_action( 'wp_ajax_wpjs-deactivate-plugin', $plugin_ajax, 'ajax_deactivate_plugin' );
		$this->loader->add_action( 'wp_ajax_wpjs-activate-plugin', $plugin_ajax, 'ajax_activate_plugin' );

		$this->loader->add_action( 'wp_ajax_wpjs-update-theme', $plugin_ajax, 'ajax_update_theme' );

		$this->loader->add_action( 'wp_ajax_wpjs-start-cron', $plugin_ajax, 'ajax_start_cron' );

		// Github updater

		$this->loader->add_filter( 'plugins_api', $plugin_github_updater, 'github_info', 20, 3 );
		$this->loader->add_filter( 'site_transient_update_plugins', $plugin_github_updater, 'github_update');
		$this->loader->add_filter( 'upgrader_process_complete', $plugin_github_updater, 'purge', 10, 2 );
		

		register_activation_hook( WP_PLUGIN_DIR . '/wp-juggler-server/wp-juggler-server.php' , array($plugin_admin, 'wpjs_plugin_activation') );
		register_deactivation_hook( WP_PLUGIN_DIR . '/wp-juggler-server/wp-juggler-server.php' , array($plugin_admin, 'wpjs_plugin_deactivation') );

		//Cron actions
		register_activation_hook( WP_PLUGIN_DIR . '/wp-juggler-server/wp-juggler-server.php' , array('WPJS_Cron_Log', 'create_database_tables') );

		$this->loader->add_filter( 'init', $plugin_cron, 'wpjs_init_scheduler' );

		//Api actions
		$this->loader->add_action( 'rest_api_init', $plugin_api, 'api_register_routes' );

		//Plugins actions
		$this->loader->add_action( 'template_redirect', $plugin_plugins, 'serve_manifest' );


		//FE actions
		//$this->loader->add_action( 'init', $plugin_fe, 'wpjs_empty_template' );
		//$this->loader->add_filter( 'pre_handle_404', $plugin_admin, 'wpjs_pre_404_filter', 5, 2 );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0
	 * @return    Better_Search_Replace_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
