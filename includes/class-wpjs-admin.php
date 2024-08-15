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
					'nonce' => $nonce
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
					'nonce' => $nonce
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
					'nonce' => $nonce
				)
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
		wp_nonce_field($this->plugin_name . '-user', 'wp_juggler_server_nonce');
		$site_url = get_post_meta($post->ID, 'wp_juggler_server_site_url', true);
		$api_key = get_post_meta($post->ID, 'wp_juggler_api_key', true);
		$automatic_login = get_post_meta($post->ID, 'wp_juggler_automatic_login', true);
		$login_username = get_post_meta($post->ID, 'wp_juggler_login_username', true);
		$activation_status = get_post_meta($post->ID, 'wp_juggler_site_activation', true);

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
		if (!isset($_POST['wp_juggler_server_nonce']) || !wp_verify_nonce($_POST['wp_juggler_server_nonce'], $this->plugin_name . '-user')) {
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
		}

		$activation_status = isset($_POST['wp_juggler_site_activation']) ? 'on' : 'off';
		update_post_meta($post_id, 'wp_juggler_site_activation', $activation_status);

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
