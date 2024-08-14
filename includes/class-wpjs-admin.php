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

class WPJS_Admin
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

	/**
	 * Register any CSS and JS used by the plugin.
	 * @since    1.0.0
	 * @access 	 public
	 * @param    string $hook Used for determining which page(s) to load our scripts.
	 */
	public function enqueue_scripts($hook)
	{
		if ('tools_page_wp-juggler-server' === $hook) {
			$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_style('wp-juggler-server', WPJS_URL . "assets/css/wp-juggler-server$min.css", array(), $this->version, 'all');
			wp_enqueue_style('jquery-style', WPJS_URL . 'assets/css/jquery-ui.min.css', array(), $this->version, 'all');
			wp_enqueue_script('jquery-ui-slider');
			wp_enqueue_script('wp-juggler-server', WPJS_URL . "assets/js/wp-juggler-server$min.js", array('jquery'), $this->version, true);
			wp_enqueue_style('thickbox');
			wp_enqueue_script('thickbox');

			wp_localize_script('wp-juggler-server', 'wpjs_object_vars', array(
				'page_size' 	=> get_option('wpjs_page_size') ? absint(get_option('wpjs_page_size')) : 20000,
				'endpoint' 		=> WPJS_AJAX::get_endpoint(),
				'ajax_nonce' 	=> wp_create_nonce('wpjs_ajax_nonce'),
				'no_search' 	=> __('No search string was defined, please enter a URL or string to search for.', 'wp-juggler-server'),
				'no_tables' 	=> __('Please select the tables that you want to update.', 'wp-juggler-server'),
				'unknown' 		=> __('An error occurred processing your request. Try decreasing the "Max Page Size", or contact support.', 'wp-juggler-server'),
				'processing'	=> __('Processing...', 'wp-juggler-server')
			));
		}
	}

	/**
	 * Register any menu pages used by the plugin.
	 * @since  1.0.0
	 * @access public
	 */
	public function wpjs_menu_pages()
	{
		$cap = apply_filters('wpjs_capability', 'manage_options');

		add_menu_page(
			__('WP Juggler', 'wp-juggler-server'),
			__('WP Juggler', 'wp-juggler-server'),
			$cap,
			"wp-juggler-server",
			[$this, 'wpjs_menu_pages_callback'],
			"",
			30
		);

		add_submenu_page(
			'wp-juggler-server',
			__('Settings', 'wp-juggler-server'),
			__('Settings', 'wp-juggler-server'),
			$cap,
			"wp-juggler-server"
		);

		add_submenu_page(
			'wp-juggler-server',
			__('Control Panel', 'wp-juggler-server'),
			__('Control Panel', 'wp-juggler-server'),
			$cap,
			'wp-juggler-server-settings',
			[$this, 'render_admin_page']
		);

		//add_submenu_page('tools.php', __('Better Search Replace', 'wp-juggler-server'), __('Better Search Replace', 'wp-juggler-server'), $cap, 'wp-juggler-server', array($this, 'wpjs_menu_pages_callback'));
	}

	public function wpjs_cpt()
	{

		$labels = array(
			'name'                => __('WP Sites', 'direktt'),
			'singular_name'       => __('WP Site',  'direktt'),
			'menu_name'           => __('WP Juggler', 'direktt'),
			'all_items'           => __('WP Sites', 'direktt'),
			'view_item'           => __('View WP Site', 'direktt'),
			'add_new_item'        => __('Add New WP Site', 'direktt'),
			'add_new'             => __('Add New', 'direktt'),
			'edit_item'           => __('Edit WP Site', 'direktt'),
			'update_item'         => __('Update WP Site', 'direktt'),
			'search_items'        => __('Search WP Sites', 'direktt'),
			'not_found'           => __('Not Found', 'direktt'),
			'not_found_in_trash'  => __('Not found in Trash', 'direktt'),
		);

		$args = array(
			'label'               => __('sites', 'direktt'),
			'description'         => __('WP Sites', 'direktt'),
			'labels'              => $labels,
			'supports'            => array('title', 'editor'),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'wp-juggler-server',
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => false,
			'capability_type'     => 'post',
			'capabilities'          => array(
				// todo Srediti prava za new i edit ako ikako moze, ako ne, ostaviti new
				//'create_posts' => 'do_not_allow', 
				//'edit_posts' => 'allow' 
			),
			'show_in_rest'	=> false,
		);

		register_post_type('wpjugglersites', $args);
	}

	/**
	 * The callback for creating a new submenu page under the "Tools" menu.
	 * @access public
	 */
	public function wpjs_menu_pages_callback()
	{
		require_once WPJS_PATH . 'includes/class-wpjs-templates-helper.php';
		require_once WPJS_PATH . 'templates/wpjs-dashboard.php';
	}

	/**
	 * Renders the result or error onto the wp-juggler-server admin page.
	 * @access public
	 */
	public static function render_result()
	{

		if (isset($_GET['result']) && $result = get_transient('wpjs_results')) {

			if (isset($result['dry_run']) && $result['dry_run'] === 'on') {
				$msg = sprintf(
					__('<p><strong>DRY RUN:</strong> <strong>%d</strong> tables were searched, <strong>%d</strong> cells were found that need to be updated, and <strong>%d</strong> changes were made.</p><p><a href="%s" class="thickbox" title="Dry Run Details">Click here</a> for more details, or use the form below to run the search/replace.</p>', 'wp-juggler-server'),
					$result['tables'],
					$result['change'],
					$result['updates'],
					get_admin_url() . 'admin-post.php?action=wpjs_view_details&TB_iframe=true&width=800&height=500'
				);
			} else {
				$msg = sprintf(
					__('<p>During the search/replace, <strong>%d</strong> tables were searched, with <strong>%d</strong> cells changed in <strong>%d</strong> updates.</p><p><a href="%s" class="thickbox" title="Search/Replace Details">Click here</a> for more details.</p>', 'wp-juggler-server'),
					$result['tables'],
					$result['change'],
					$result['updates'],
					get_admin_url() . 'admin-post.php?action=wpjs_view_details&TB_iframe=true&width=800&height=500'
				);
			}

			echo '<div class="updated bsr-updated" style="display: none;">' . $msg . '</div>';
		}
	}

	/**
	 * Prefills the given value on the search/replace page (dry run, live run, from profile).
	 * @access public
	 * @param  string $value The value to check for.
	 * @param  string $type  The type of the value we're filling.
	 */
	public static function prefill_value($value, $type = 'text')
	{

		// Grab the correct data to prefill.
		if (isset($_GET['result']) && get_transient('wpjs_results')) {
			$values = get_transient('wpjs_results');
		} else {
			$values = array();
		}

		// Prefill the value.
		if (isset($values[$value])) {

			if ('checkbox' === $type && 'on' === $values[$value]) {
				echo 'checked';
			} else {
				echo str_replace('#WPJS_BACKSLASH#', '\\', esc_attr(htmlentities($values[$value])));
			}
		}
	}

	/**
	 * Loads the tables available to run a search replace, prefilling if already
	 * selected the tables.
	 * @access public
	 */
	public static function load_tables()
	{

		// Get the tables and their sizes.
		$tables 	= WPJS_DB::get_tables();
		$sizes 		= WPJS_DB::get_sizes();

		echo '<select id="bsr-table-select" name="select_tables[]" multiple="multiple" style="">';

		foreach ($tables as $table) {

			// Try to get the size for this specific table.
			$table_size = isset($sizes[$table]) ? $sizes[$table] : '';

			if (isset($_GET['result']) && get_transient('wpjs_results')) {

				$result = get_transient('wpjs_results');

				if (isset($result['table_reports'][$table])) {
					echo "<option value='$table' selected>$table $table_size</option>";
				} else {
					echo "<option value='$table'>$table $table_size</option>";
				}
			} else {
				echo "<option value='$table'>$table $table_size</option>";
			}
		}

		echo '</select>';
	}

	/**
	 * Loads the result details (via Thickbox).
	 * @access public
	 */
	public function load_details()
	{

		if (get_transient('wpjs_results')) {
			$results 		= get_transient('wpjs_results');
			$min 			= (defined('SCRIPT_DEBUG') && true === SCRIPT_DEBUG) ? '' : '.min';
			$wpjs_styles 	= WPJS_URL . 'assets/css/wp-juggler-server.css?v=' . WPJS_VERSION;

?>
			<link href="<?php echo esc_url(get_admin_url(null, '/css/common' . $min . '.css')); ?>" rel="stylesheet" type="text/css" />
			<link href="<?php echo esc_url($wpjs_styles); ?>" rel="stylesheet" type="text/css">

			<div style="padding: 32px; background-color: var(--color-white); min-height: 100%;">
				<table id="bsr-results-table" class="widefat">
					<thead>
						<tr>
							<th class="bsr-first"><?php _e('Table', 'wp-juggler-server'); ?></th>
							<th class="bsr-second"><?php _e('Changes Found', 'wp-juggler-server'); ?></th>
							<th class="bsr-third"><?php _e('Rows Updated', 'wp-juggler-server'); ?></th>
							<th class="bsr-fourth"><?php _e('Time', 'wp-juggler-server'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($results['table_reports'] as $table_name => $report) {
							$time = $report['end'] - $report['start'];

							if ($report['change'] != 0) {
								$report['change'] = '<a class="tooltip">' . esc_html($report['change']) . '</a>';

								$upgrade_link = sprintf(
									__('<a href="%s" target="_blank">UPGRADE</a> to view details on the exact changes that will be made.', 'wp-juggler-server'),
									'https://deliciousbrains.com/wp-juggler-server/upgrade/?utm_source=insideplugin&utm_medium=web&utm_content=tooltip&utm_campaign=bsr-to-migrate'
								);

								$report['change'] .= '<span class="helper-message right">' . $upgrade_link . '</span>';
							}

							if ($report['updates'] != 0) {
								$report['updates'] = '<strong>' . esc_html($report['updates']) . '</strong>';
							}

							echo '<tr><td class="bsr-first">' . esc_html($table_name) . '</td><td class="bsr-second">' . $report['change'] . '</td><td class="bsr-third">' . $report['updates'] . '</td><td class="bsr-fourth">' . round($time, 3) . __(' seconds', 'wp-juggler-server') . '</td></tr>';
						}
						?>
					</tbody>
				</table>
			</div>
<?php
		}
	}

	/**
	 * Registers our settings in the options table.
	 * @access public
	 */
	public function register_option()
	{
		register_setting('wpjs_settings_fields', 'wpjs_page_size', 'absint');
	}

	/**
	 * Downloads the system info file for support.
	 * @access public
	 */
	public function download_sysinfo()
	{
		check_admin_referer('wpjs_download_sysinfo', 'wpjs_sysinfo_nonce');

		$cap = apply_filters('wpjs_capability', 'manage_options');
		if (! current_user_can($cap)) {
			return;
		}

		nocache_headers();

		header('Content-Type: text/plain');
		header('Content-Disposition: attachment; filename="bsr-system-info.txt"');

		echo wp_strip_all_tags($_POST['bsr-sysinfo']);
		die();
	}

	/**
	 * Displays the link to upgrade to BSR Pro
	 * @access public
	 * @param array $links The links assigned to the plugin.
	 */
	public function meta_upgrade_link($links, $file)
	{
		$plugin = plugin_basename(WPJS_FILE);

		if ($file == $plugin) {
			return array_merge(
				$links,
				array('<a href="https://bettersearchreplace.com/?utm_source=insideplugin&utm_medium=web&utm_content=plugins-page&utm_campaign=pro-upsell">' . __('Upgrade to Pro', 'wp-juggler-server') . '</a>')
			);
		}

		return $links;
	}
}
