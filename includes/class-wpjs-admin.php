<?php

/**
 * wp-admin specific functionality of the plugin.
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

	/**
	 * Register any CSS and JS used by the plugin.
	 * @since    1.0.0
	 * @access 	 public
	 * @param    string $hook Used for determining which page(s) to load our scripts.
	 */
	public function enqueue_plugin_assets($suffix)
	{
		global $pagenow;
		global $typenow;

		if (str_ends_with($suffix, 'wpjs-dashboard')) {
			wp_enqueue_script(
				$this->plugin_name . '-dashboard',
				plugin_dir_url(__DIR__) . 'assets/dashboard/wpjs-dashboard.js',
				array('jquery'),
				'',
				[
					'in_footer' => true,
				]
			);

			wp_enqueue_style(
				$this->plugin_name . '-dashboard',
				plugin_dir_url(__DIR__) . 'assets/dashboard/wpjs-dashboard.css',
				[],
				''
			);

			$nonce = wp_create_nonce($this->plugin_name . '-dashboard');

			wp_localize_script(
				$this->plugin_name . '-dashboard',
				$this->plugin_name . '_dashboard_object',
				array(
					'ajaxurl' => admin_url('admin-ajax.php'),
					'nonce' => $nonce,
					'adminurl' => admin_url()
				)
			);
		}

		if (str_ends_with($suffix, 'wpjs-settings')) {
			wp_enqueue_script(
				$this->plugin_name . '-settings',
				plugin_dir_url(__DIR__) . 'assets/settings/wpjs-settings.js',
				array('jquery'),
				'',
				[
					'in_footer' => true,
				]
			);

			wp_enqueue_style(
				$this->plugin_name . '-settings',
				plugin_dir_url(__DIR__) . 'assets/settings/wpjs-settings.css',
				[],
				''
			);

			$nonce = wp_create_nonce($this->plugin_name . '-settings');

			wp_localize_script(
				$this->plugin_name . '-settings',
				$this->plugin_name . '_settings_object',
				array(
					'ajaxurl' => admin_url('admin-ajax.php'),
					'nonce' => $nonce,
					'adminurl' => admin_url()
				)
			);
		}

		if (str_ends_with($suffix, 'wpjs-control-panel')) {
			wp_enqueue_script(
				$this->plugin_name . '-control-panel',
				plugin_dir_url(__DIR__) . 'assets/control-panel/wpjs-control-panel.js',
				array('jquery'),
				'',
				[
					'in_footer' => true,
				]
			);

			wp_enqueue_style(
				$this->plugin_name . '-control-panel',
				plugin_dir_url(__DIR__) . 'assets/control-panel/wpjs-control-panel.css',
				[],
				''
			);

			$nonce = wp_create_nonce($this->plugin_name . '-control-panel');

			wp_localize_script(
				$this->plugin_name . '-control-panel',
				$this->plugin_name . '_control_panel_object',
				array(
					'ajaxurl' => admin_url('admin-ajax.php'),
					'nonce' => $nonce,
					'adminurl' => admin_url()
				)
			);
		}

		if (in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) && 'wpjugglerplugins' == $typenow){
			wp_enqueue_media();
			wp_enqueue_script(
				$this->plugin_name . '-cpt',
				plugin_dir_url(__DIR__) . 'assets/cpt/wpjs-cpt.js',
				array('jquery'),
				null,
				true
			);
		}

	}

	public function register_menu_page()
	{

		$cap = apply_filters('wpjs_capability', 'manage_options');

		add_menu_page(
			__('WP Juggler', 'wp-juggler-server'),
			__('WP Juggler', 'wp-juggler-server'),
			$cap,
			"wpjs-dashboard",
			[$this, 'render_admin_page'],
			"",
			30
		);


		add_submenu_page(
			'wpjs-dashboard',
			__('Dashboard', 'wp-juggler-server'),
			__('Dashboard', 'wp-juggler-server'),
			$cap,
			"wpjs-dashboard"
		);
	}

	public function register_menu_page_end()
	{

		$cap = apply_filters('wpjs_capability', 'manage_options');

		add_submenu_page(
			'wpjs-dashboard',
			__('Settings', 'wp-juggler-server'),
			__('Settings', 'wp-juggler-server'),
			$cap,
			'wpjs-settings',
			[$this, 'render_admin_page']
		);

		add_submenu_page(
			'wpjs-dashboard',
			__('Control Panel', 'wp-juggler-server'),
			__('Control Panel', 'wp-juggler-server'),
			$cap,
			'wpjs-control-panel',
			[$this, 'render_admin_page']
		);
	}


	public function wpjs_cpt()
	{

		$labels = array(
			'name'                => __('Sites', 'wp-juggler-server'),
			'singular_name'       => __('Site',  'wp-juggler-server'),
			'menu_name'           => __('WP Juggler', 'wp-juggler-server'),
			'all_items'           => __('Sites', 'wp-juggler-server'),
			'view_item'           => __('View Site', 'wp-juggler-server'),
			'add_new_item'        => __('Add New Site', 'wp-juggler-server'),
			'add_new'             => __('Add New', 'wp-juggler-server'),
			'edit_item'           => __('Edit Site', 'wp-juggler-server'),
			'update_item'         => __('Update Site', 'wp-juggler-server'),
			'search_items'        => __('Search Sites', 'wp-juggler-server'),
			'not_found'           => __('Not Found', 'wp-juggler-server'),
			'not_found_in_trash'  => __('Not found in Trash', 'wp-juggler-server'),
		);

		$args = array(
			'label'               => __('sites', 'wp-juggler-server'),
			'description'         => __('Sites', 'wp-juggler-server'),
			'labels'              => $labels,
			'supports'            => array('title'),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'wpjs-dashboard',
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

		$labels = array(
			'name'                => __('Plugins', 'wp-juggler-server'),
			'singular_name'       => __('Plugin',  'wp-juggler-server'),
			'menu_name'           => __('WP Juggler', 'wp-juggler-server'),
			'all_items'           => __('Plugins', 'wp-juggler-server'),
			'view_item'           => __('View Plugin', 'wp-juggler-server'),
			'add_new_item'        => __('Add New Plugin', 'wp-juggler-server'),
			'add_new'             => __('Add New', 'wp-juggler-server'),
			'edit_item'           => __('Edit Plugin', 'wp-juggler-server'),
			'update_item'         => __('Update Plugin', 'wp-juggler-server'),
			'search_items'        => __('Search Plugins', 'wp-juggler-server'),
			'not_found'           => __('Not Found', 'wp-juggler-server'),
			'not_found_in_trash'  => __('Not found in Trash', 'wp-juggler-server'),
		);

		$args = array(
			'label'               => __('plugins', 'wp-juggler-server'),
			'description'         => __('Plugins', 'wp-juggler-server'),
			'labels'              => $labels,
			'supports'            => array('title'),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'wpjs-dashboard',
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

		register_post_type('wpjugglerplugins', $args);
	}

	public function render_admin_page()
	{
?>
		<div id="app"></div>
	<?php
	}

	public function wpjs_sites_metaboxes()
	{
		add_meta_box(
			'wpjs_site_details',
			__('Site Details', 'wp-juggler-server'),
			array($this, 'render_juggler_sites_meta_box'),
			'wpjugglersites',
			'normal',
			'high'
		);

		remove_meta_box('submitdiv', 'wpjugglersites', 'side');

		add_meta_box(
			'submitdiv',
			__('Save', 'wp-juggler-server'),
			array($this, 'render_juggler_sites_publish_meta_box'),
			'wpjugglersites',
			'side',
			'high'
		);

		add_meta_box(
			'wpjs_users', // ID
			__('Assign Users', 'wp-juggler-server'), // Title
			array($this, 'render_juggler_users_meta_box'),
			'wpjugglersites', // Post type
			'normal', // Context
			'low' // Priority
		);
	}

	public function wpjs_plugins_metaboxes()
	{
		add_meta_box(
			'wpjs_plugin_details',
			__('Plugin Details', 'wp-juggler-server'),
			array($this, 'render_juggler_plugins_meta_box'),
			'wpjugglerplugins',
			'normal',
			'high'
		);

		remove_meta_box('submitdiv', 'wpjugglerplugins', 'side');

		add_meta_box(
			'submitdiv',
			__('Save', 'wp-juggler-server'),
			array($this, 'render_juggler_sites_publish_meta_box'),
			'wpjugglerplugins',
			'side',
			'high'
		);



		/* add_meta_box(
			'wpjs_users', // ID
			__('Assign Users', 'wp-juggler-server'), // Title
			array($this, 'render_juggler_users_meta_box'),
			'wpjugglersites', // Post type
			'normal', // Context
			'low' // Priority
		); */
	}

	function cp_hide_admin_menus($context)
	{

		global $pagenow;
		$current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';

		if ($pagenow == 'admin.php' && $current_page == 'wpjs-control-panel') {

			echo '<style>
			#adminmenumain, #wpadminbar, #wpfooter {
				display: none;
			}
			#wpcontent, #wpfooter  {
				margin-left: 0px !important;
			}
			</style>';
			
			global $menu;
			$menu = array();
		}
	}

	public function render_juggler_sites_meta_box($post)
	{
		wp_nonce_field($this->plugin_name . '-site', 'wp_juggler_server_nonce');
		$site_url = get_post_meta($post->ID, 'wp_juggler_server_site_url', true);
		$api_key = get_post_meta($post->ID, 'wp_juggler_api_key', true);
		$automatic_login = get_post_meta($post->ID, 'wp_juggler_automatic_login', true);
		$login_username = get_post_meta($post->ID, 'wp_juggler_login_username', true);
		$activation_status = get_post_meta($post->ID, 'wp_juggler_site_activation', true);
		$frontend_ping_url = get_post_meta($post->ID, 'wp_juggler_frontend_ping_url', true);
		$frontend_ping_string = get_post_meta($post->ID, 'wp_juggler_frontend_ping_string', true);
	?>

		<p>
			<label for="wp_juggler_server_site_url">Site Url</label><br>
			<input type="text" name="wp_juggler_server_site_url" id="wp_juggler_server_site_url" value="<?php echo esc_attr($site_url); ?>" size="60" />
		</p>
		<p>
			<label for="wp_juggler_api_key">API Key</label><br>
			<?php if (!empty($api_key)): ?>
				<input type="text" name="wp_juggler_api_key" id="wp_juggler_api_key" value="<?php echo esc_attr($api_key); ?>" size="60" readonly />
			<?php else: ?>
				<input type="text" name="wp_juggler_api_key" id="wp_juggler_api_key" value="<?php echo wp_generate_uuid4(); ?>" size="60" readonly />
			<?php endif; ?>
		</p>
		<p>
			<label for="wp_juggler_automatic_login">Automatic Login</label><br>
			<input type="checkbox" name="wp_juggler_automatic_login" id="wp_juggler_automatic_login" <?php checked($automatic_login, 'on'); ?> />
		</p>
		<p id="wp_juggler_login_username_paragraph">
			<label for="wp_juggler_login_username">Remote Login Username</label><br>
			<input type="text" name="wp_juggler_login_username" id="wp_juggler_login_username" value="<?php echo esc_attr($login_username); ?>" size="60" />
		</p>
		<p>
			<label for="wp_juggler_site_activation">Activation Status</label><br>
			<input type="checkbox" name="wp_juggler_site_activation" id="wp_juggler_site_activation" <?php checked($activation_status, 'on'); ?> />
		</p>
		<p>
			<label for="wp_juggler_frontend_ping_url">Frontend url to ping (if any) - by default frontpage will be used</label><br>
			<input type="text" name="wp_juggler_frontend_ping_url" id="wp_juggler_frontend_ping_url" value="<?php echo esc_attr($frontend_ping_url); ?>" size="60" />
		</p>
		<p>
			<label for="wp_juggler_frontend_ping_string">String to look for on the page(if any) - by default only response code will be checked (200 or 201)</label><br>
			<input type="text" name="wp_juggler_frontend_ping_string" id="wp_juggler_frontend_ping_string" value="<?php echo esc_attr($frontend_ping_string); ?>" size="60" />
		</p>

		<style>
			#wp_juggler_login_username_paragraph {
				display: <?php echo $automatic_login === 'on' ? 'block' : 'none'; ?>;
			}
		</style>
		<script>
			document.getElementById('wp_juggler_automatic_login').addEventListener('change', function() {
				document.getElementById('wp_juggler_login_username_paragraph').style.display = this.checked ? 'block' : 'none';
			});
		</script>

	<?php
	}

	public function render_juggler_plugins_meta_box($post)
	{
		wp_nonce_field($this->plugin_name . '-plugin', 'wp_juggler_server_nonce');
		$wp_juggler_plugin_version = get_post_meta($post->ID, 'wp_juggler_plugin_version', true);
		$wp_juggler_plugin_slug = get_post_meta($post->ID, 'wp_juggler_plugin_slug', true);
		$wp_juggler_plugin_author = get_post_meta($post->ID, 'wp_juggler_plugin_author', true);
		$wp_juggler_plugin_author_profile = get_post_meta($post->ID, 'wp_juggler_plugin_author_profile', true);
		$wp_juggler_plugin_download_file = get_post_meta($post->ID, 'wp_juggler_plugin_download_file', true);
		$wp_juggler_plugin_requires_wp = get_post_meta($post->ID, 'wp_juggler_plugin_requires_wp', true);
		$wp_juggler_plugin_tested_wp = get_post_meta($post->ID, 'wp_juggler_plugin_tested_wp', true);
		$wp_juggler_plugin_requires_php = get_post_meta($post->ID, 'wp_juggler_plugin_requires_php', true);
		$wp_juggler_plugin_last_updated = get_post_meta($post->ID, 'wp_juggler_plugin_last_updated', true);
		$wp_juggler_plugin_description = get_post_meta($post->ID, 'wp_juggler_plugin_description', true);
		$wp_juggler_plugin_installation = get_post_meta($post->ID, 'wp_juggler_plugin_installation', true);
		$wp_juggler_plugin_changelog = get_post_meta($post->ID, 'wp_juggler_plugin_changelog', true);
		$wp_juggler_plugin_banner_low = get_post_meta($post->ID, 'wp_juggler_plugin_banner_low', true);
		$wp_juggler_plugin_banner_high = get_post_meta($post->ID, 'wp_juggler_plugin_banner_high', true);
	?>

		<h3>Release data</h3>

		<p>
			<label for="wp_juggler_plugin_version">Plugin Version</label><br>
			<input type="text" name="wp_juggler_plugin_version" id="wp_juggler_plugin_version" value="<?php echo esc_attr($wp_juggler_plugin_version); ?>" size="60" />
		</p>
	
	<?php
    	$package_url = $wp_juggler_plugin_download_file ? wp_get_attachment_url($wp_juggler_plugin_download_file) : '';
    ?>

		<p>
			<input type="hidden" id="wp_juggler_plugin_download_file" name="wp_juggler_plugin_download_file" value="<?php echo esc_attr($wp_juggler_plugin_download_file); ?>" />
			<label for="wp_juggler_plugin_download_file-preview">Plugin Package Url:</label><br>
			<input type="text" id="wp_juggler_plugin_download_file-preview" value="<?php echo esc_attr($package_url); ?>" size="120" readonly/>
			<br>
			<p>
				<button type="button" class="button" id="upload-wp_juggler_plugin_download_file-button"><?php _e('Choose Plugin Package'); ?></button>
				<button type="button" class="button" id="remove-wp_juggler_plugin_download_file-button"><?php _e('Remove Plugin Package'); ?></button>
			</p>
		</p>

		<p>
			<label for="wp_juggler_plugin_last_updated">Plugin Last Updated</label><br>
			<input type="datetime-local" name="wp_juggler_plugin_last_updated" id="wp_juggler_plugin_last_updated" value="<?php echo esc_attr($wp_juggler_plugin_last_updated); ?>" size="25" />
		</p>

		<p>
			<label for="wp_juggler_plugin_changelog">Plugin Changelog</label><br>
			
		<?php
			wp_editor($wp_juggler_plugin_changelog, 'wp_juggler_plugin_changelog', array(
        		'textarea_name' => 'wp_juggler_plugin_changelog',
        		'textarea_rows' => 8,
        		'editor_css' => '<style>.wp-editor-container textarea.wp-editor-area{ width: 100%; max-width: 80ch; }</style>',
				'media_buttons' => false,
				'tinymce' => array(
					'toolbar1' => 'bold,italic,underline,bullist,numlist,link,unlink,undo,redo,removeformat,charmap',
					'block_formats' => 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6',
				),
				'quicktags' => array(
					'buttons' => 'strong,em,ul,ol,li,link,close,dfw'
				)
			));
		?>

		</p>

		<p>
			<label for="wp_juggler_plugin_requires_wp">Required WP Version</label><br>
			<input type="text" name="wp_juggler_plugin_requires_wp" id="wp_juggler_plugin_requires_wp" value="<?php echo esc_attr($wp_juggler_plugin_requires_wp); ?>" size="60" />
		</p>

		<p>
			<label for="wp_juggler_plugin_tested_wp">Tested up to WP Version</label><br>
			<input type="text" name="wp_juggler_plugin_tested_wp" id="wp_juggler_plugin_tested_wp" value="<?php echo esc_attr($wp_juggler_plugin_tested_wp); ?>" size="60" />
		</p>

		<p>
			<label for="wp_juggler_plugin_requires_php">Plugin PHP Version</label><br>
			<input type="text" name="wp_juggler_plugin_requires_php" id="wp_juggler_plugin_requires_php" value="<?php echo esc_attr($wp_juggler_plugin_requires_php); ?>" size="60" />
		</p>

		<h3>General plugin data</h3>

		<p>
			<label for="wp_juggler_plugin_slug">Plugin Slug</label><br>
			<input type="text" name="wp_juggler_plugin_slug" id="wp_juggler_plugin_slug" value="<?php echo esc_attr($wp_juggler_plugin_slug); ?>" size="60" />
		</p>

		<p>
			<label for="wp_juggler_plugin_author">Plugin Author</label><br>
			<input type="text" name="wp_juggler_plugin_author" id="wp_juggler_plugin_author" value="<?php echo esc_attr($wp_juggler_plugin_author); ?>" size="60" />
		</p>

		<p>
			<label for="wp_juggler_plugin_author_profile">Plugin Author Profile</label><br>
			<input type="text" name="wp_juggler_plugin_author_profile" id="wp_juggler_plugin_author_profile" value="<?php echo esc_attr($wp_juggler_plugin_author_profile); ?>" size="60" />
		</p>

		<p>
			<label for="wp_juggler_plugin_description">Plugin Description</label><br>
			<?php
				wp_editor($wp_juggler_plugin_description, 'wp_juggler_plugin_description', array(
					'textarea_name' => 'wp_juggler_plugin_description',
					'textarea_rows' => 8,
					'editor_css' => '<style>.wp-editor-container textarea.wp-editor-area{ width: 100%; max-width: 80ch; }</style>',
					'media_buttons' => false,
					'tinymce' => array(
						'toolbar1' => 'bold,italic,underline,bullist,numlist,link,unlink,undo,redo,removeformat,charmap',
						'block_formats' => 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6',
					),
					'quicktags' => array(
						'buttons' => 'strong,em,ul,ol,li,link,close,dfw'
					)
				));
			?>
		</p>

		<p>
			<label for="wp_juggler_plugin_installation">Plugin Installation</label><br>
			<?php
				wp_editor($wp_juggler_plugin_installation, 'wp_juggler_plugin_installation', array(
					'textarea_name' => 'wp_juggler_plugin_installation',
					'textarea_rows' => 8,
					'editor_css' => '<style>.wp-editor-container textarea.wp-editor-area{ width: 100%; max-width: 80ch; }</style>',
					'media_buttons' => false,
					'tinymce' => array(
						'toolbar1' => 'bold,italic,underline,bullist,numlist,link,unlink,undo,redo,removeformat,charmap',
						'block_formats' => 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6',
					),
					'quicktags' => array(
						'buttons' => 'strong,em,ul,ol,li,link,close,dfw'
					)
				));
			?>
		</p>

		<?php
    		$banner_low = $wp_juggler_plugin_banner_low ? wp_get_attachment_image_src($wp_juggler_plugin_banner_low, 'thumbnail')[0] : '';
    	?>

		<p>
			<input type="hidden" id="wp_juggler_plugin_banner_low" name="wp_juggler_plugin_banner_low" value="<?php echo esc_attr($wp_juggler_plugin_banner_low); ?>" />
			<label for="wp_juggler_plugin_banner_low-preview">Plugin Low Resolution Banner:</label><br>
			<img id="wp_juggler_plugin_banner_low-preview" name="wp_juggler_plugin_banner_low-preview" src="<?php echo esc_attr($banner_low); ?>" style="max-height:100px; max-width:200px" />
			<br>
			<p>
				<button type="button" class="button" id="upload-wp_juggler_plugin_banner_low-button"><?php _e('Choose Banner'); ?></button>
				<button type="button" class="button" id="remove-wp_juggler_plugin_banner_low-button"><?php _e('Remove Banner'); ?></button>
			</p>
		</p>

		<?php
    		$banner_high = $wp_juggler_plugin_banner_high ? wp_get_attachment_image_src($wp_juggler_plugin_banner_high, 'thumbnail')[0] : '';
    	?>

		<p>
			<input type="hidden" id="wp_juggler_plugin_banner_high" name="wp_juggler_plugin_banner_high" value="<?php echo esc_attr($wp_juggler_plugin_banner_high); ?>" />
			<label for="wp_juggler_plugin_banner_high-preview">Plugin High Resolution Banner:</label><br>
			<img id="wp_juggler_plugin_banner_high-preview" name="wp_juggler_plugin_banner_high-preview" src="<?php echo esc_attr($banner_high); ?>" style="max-height:100px; max-width:200px"/>
			<br>
			<p>
				<button type="button" class="button" id="upload-wp_juggler_plugin_banner_high-button"><?php _e('Choose Banner'); ?></button>
				<button type="button" class="button" id="remove-wp_juggler_plugin_banner_high-button"><?php _e('Remove Banner'); ?></button>
			</p>
		</p>
		

	<?php
	}

	public function render_juggler_users_meta_box($post)
	{
		$saved_users = get_post_meta($post->ID, 'wp_juggler_login_users', true);

	?>
		<div id="juggler-users-container">
			<?php
			if (is_array($saved_users) && !empty($saved_users)) {
				foreach ($saved_users as $user_id) {
					$user = get_userdata($user_id);
					echo '<div class="juggler-user-field">';
					echo '<input type="text" class="regular-text juggler-user-autocomplete" name="juggler_users[]" value="' . esc_attr($user->user_login) . '" />';
					echo '<button type="button" class="button juggler-remove-user">Remove</button>';
					echo '</div>';
				}
			}
			?>
		</div>
		<button type="button" class="button" id="juggler-add-user">Add User</button>

		<script>
			jQuery(document).ready(function($) {
				function bindAutocomplete() {
					$('.juggler-user-autocomplete').autocomplete({
						source: function(request, response) {
							var chosenValues = [];
							$('.juggler-user-autocomplete').each(function() {
								chosenValues.push($(this).val());
							});
							chosenValues.pop();
							$.ajax({
								url: ajaxurl,
								data: {
									action: 'juggler_user_search',
									term: request.term,
									wp_juggler_server_nonce: $('#wp_juggler_server_nonce').val()
								},
								success: function(data) {
									var filteredData = $.grep(data, function(item) {
										return $.inArray(item.value, chosenValues) === -1;
									});
									response(filteredData);
								}
							});
						},
						minLength: 2
					});
				}
				bindAutocomplete();

				$('#juggler-add-user').click(function() {
					$('#juggler-users-container').append(
						'<div class="juggler-user-field">' +
						'<input type="text" class="regular-text juggler-user-autocomplete" name="juggler_users[]" value="" />' +
						'<button type="button" class="button juggler-remove-user">Remove</button>' +
						'</div>'
					);
					bindAutocomplete();
				});

				$(document).on('click', '.juggler-remove-user', function() {
					$(this).closest('.juggler-user-field').remove();
				});
			});
		</script>
	<?php
	}

	public function render_juggler_sites_publish_meta_box($post)
	{
		global $action;

	?>
		<div class="submitbox" id="submitpost">
			<div id="minor-publishing">
				<div id="misc-publishing-actions">


					<div class="misc-pub-section curtime misc-pub-curtime">
						<span id="timestamp">
							Publish on:
							<b><?php echo get_the_date(); ?> at <?php echo get_the_time(); ?></b>
						</span>
					</div>
				</div>
			</div>

			<div id="major-publishing-actions">
				<?php
				if (current_user_can("delete_post", $post->ID)) {
					if (!EMPTY_TRASH_DAYS) : ?>
						<div id="delete-action">
							<a class="submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>">Move to Trash</a>
						</div>
					<?php else : ?>
						<div id="delete-action">
							<a class="submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>">Move to Trash</a>
						</div>
				<?php endif;
				} ?>
				<div id="publishing-action">
					<span class="spinner"></span>
					<input name="original_publish" type="hidden" id="original_publish" value="Publish">
					<?php submit_button(__('Save'), 'primary', 'publish', false); ?>
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<?php
	}

	public function wpjs_save_sites_meta_boxes($post_id)
	{
		if (!isset($_POST['wp_juggler_server_nonce']) || !wp_verify_nonce($_POST['wp_juggler_server_nonce'], $this->plugin_name . '-site')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		if (isset($_POST['wp_juggler_server_site_url'])) {
			update_post_meta($post_id, 'wp_juggler_server_site_url', sanitize_text_field($_POST['wp_juggler_server_site_url']));
		} else {
			delete_post_meta($post_id, 'wp_juggler_server_site_url');
		}

		if (isset($_POST['wp_juggler_api_key']) && !empty($_POST['wp_juggler_api_key'])) {
			update_post_meta($post_id, 'wp_juggler_api_key', sanitize_text_field($_POST['wp_juggler_api_key']));
		} else {
			update_post_meta($post_id, 'wp_juggler_api_key', wp_generate_uuid4());
		}

		$automatic_login = isset($_POST['wp_juggler_automatic_login']) ? 'on' : 'off';
		update_post_meta($post_id, 'wp_juggler_automatic_login', $automatic_login);

		if (isset($_POST['wp_juggler_login_username'])) {
			update_post_meta($post_id, 'wp_juggler_login_username', sanitize_text_field($_POST['wp_juggler_login_username']));
		} else {
			delete_post_meta($post_id, 'wp_juggler_login_username');
		}

		$activation_status = isset($_POST['wp_juggler_site_activation']) ? 'on' : 'off';
		update_post_meta($post_id, 'wp_juggler_site_activation', $activation_status);

		if (isset($_POST['wp_juggler_frontend_ping_url'])) {
			update_post_meta($post_id, 'wp_juggler_frontend_ping_url', sanitize_text_field($_POST['wp_juggler_frontend_ping_url']));
		} else {
			delete_post_meta($post_id, 'wp_juggler_frontend_ping_url');
		}

		if (isset($_POST['wp_juggler_frontend_ping_string'])) {
			update_post_meta($post_id, 'wp_juggler_frontend_ping_string', sanitize_text_field($_POST['wp_juggler_frontend_ping_string']));
		} else {
			delete_post_meta($post_id, 'wp_juggler_frontend_ping_string');
		}

		$users = array();
		if (isset($_POST['juggler_users'])) {
			foreach ($_POST['juggler_users'] as $user_login) {
				$user = get_user_by('login', sanitize_text_field($user_login));
				if ($user) {
					$users[] = $user->ID;
				}
			}
		}

		update_post_meta($post_id, 'wp_juggler_login_users', $users);
	}

	public function wpjs_save_plugins_meta_boxes($post_id)
	{
		if (!isset($_POST['wp_juggler_server_nonce']) || !wp_verify_nonce($_POST['wp_juggler_server_nonce'], $this->plugin_name . '-plugin')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		if (isset($_POST['wp_juggler_plugin_version'])) {
			update_post_meta($post_id, 'wp_juggler_plugin_version', sanitize_text_field($_POST['wp_juggler_plugin_version']));
		} else {
			delete_post_meta($post_id, 'wp_juggler_plugin_version');
		}

		if (isset($_POST['wp_juggler_plugin_slug'])) {
			update_post_meta($post_id, 'wp_juggler_plugin_slug', sanitize_text_field($_POST['wp_juggler_plugin_slug']));
		} else {
			delete_post_meta($post_id, 'wp_juggler_plugin_slug');
		}

		if (isset($_POST['wp_juggler_plugin_author'])) {
			update_post_meta($post_id, 'wp_juggler_plugin_author', sanitize_text_field($_POST['wp_juggler_plugin_author']));
		} else {
			delete_post_meta($post_id, 'wp_juggler_plugin_author');
		}

		if (isset($_POST['wp_juggler_plugin_author_profile'])) {
			update_post_meta($post_id, 'wp_juggler_plugin_author_profile', sanitize_text_field($_POST['wp_juggler_plugin_author_profile']));
		} else {
			delete_post_meta($post_id, 'wp_juggler_plugin_author_profile');
		}

		if (isset($_POST['wp_juggler_plugin_download_file'])) {
			update_post_meta($post_id, 'wp_juggler_plugin_download_file', sanitize_text_field($_POST['wp_juggler_plugin_download_file']));
		} else {
			delete_post_meta($post_id, 'wp_juggler_plugin_download_file');
		}

		if (isset($_POST['wp_juggler_plugin_requires_wp'])) {
			update_post_meta($post_id, 'wp_juggler_plugin_requires_wp', sanitize_text_field($_POST['wp_juggler_plugin_requires_wp']));
		} else {
			delete_post_meta($post_id, 'wp_juggler_plugin_requires_wp');
		}

		if (isset($_POST['wp_juggler_plugin_tested_wp'])) {
			update_post_meta($post_id, 'wp_juggler_plugin_tested_wp', sanitize_text_field($_POST['wp_juggler_plugin_tested_wp']));
		} else {
			delete_post_meta($post_id, 'wp_juggler_plugin_tested_wp');
		}

		if (isset($_POST['wp_juggler_plugin_requires_php'])) {
			update_post_meta($post_id, 'wp_juggler_plugin_requires_php', sanitize_text_field($_POST['wp_juggler_plugin_requires_php']));
		} else {
			delete_post_meta($post_id, 'wp_juggler_plugin_requires_php');
		}

		if (isset($_POST['wp_juggler_plugin_last_updated'])) {
			update_post_meta($post_id, 'wp_juggler_plugin_last_updated', sanitize_text_field($_POST['wp_juggler_plugin_last_updated']));
		} else {
			delete_post_meta($post_id, 'wp_juggler_plugin_last_updated');
		}

		if (isset($_POST['wp_juggler_plugin_banner_low'])) {
			update_post_meta($post_id, 'wp_juggler_plugin_banner_low', sanitize_text_field($_POST['wp_juggler_plugin_banner_low']));
		} else {
			delete_post_meta($post_id, 'wp_juggler_plugin_banner_low');
		}

		if (isset($_POST['wp_juggler_plugin_banner_high'])) {
			update_post_meta($post_id, 'wp_juggler_plugin_banner_high', sanitize_text_field($_POST['wp_juggler_plugin_banner_high']));
		} else {
			delete_post_meta($post_id, 'wp_juggler_plugin_banner_high');
		}

		if (isset($_POST['wp_juggler_plugin_description'])) {
			update_post_meta($post_id, 'wp_juggler_plugin_description', wp_kses_post($_POST['wp_juggler_plugin_description']));
		} else {
			delete_post_meta($post_id, 'wp_juggler_plugin_description');
		}

		if (isset($_POST['wp_juggler_plugin_installation'])) {
			update_post_meta($post_id, 'wp_juggler_plugin_installation', wp_kses_post($_POST['wp_juggler_plugin_installation']));
		} else {
			delete_post_meta($post_id, 'wp_juggler_plugin_installation');
		}

		if (isset($_POST['wp_juggler_plugin_changelog'])) {
			update_post_meta($post_id, 'wp_juggler_plugin_changelog', wp_kses_post($_POST['wp_juggler_plugin_changelog']));
		} else {
			delete_post_meta($post_id, 'wp_juggler_plugin_changelog');
		}
	}

	public function wpjs_add_custom_column($columns)
	{
		return array_merge(
			array_slice($columns, 0, 2, true),
			array('server_site_url' => 'Site URL'),
			array_slice($columns, 2, null, true)
		);
	}

	function wpjs_display_custom_column($column, $post_id)
	{
		if ($column == 'server_site_url') {
			echo esc_html(get_post_meta($post_id, 'wp_juggler_server_site_url', true));
		}
	}
	
}
