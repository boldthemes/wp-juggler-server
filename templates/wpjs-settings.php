<?php
/**
 * Displays the main "Settings" tab.
 *
 * @link       https://wpjuggler.com
 * @since      1.0.0
 *
 * @package    WP_Juggler_Server
 * @subpackage WP_Juggler_Server/includes
 */

// Prevent direct/unauthorized access.
if ( ! defined( 'WPJS_PATH' ) ) exit;

// Other settings.
$page_size 	= get_option( 'WPJS_page_size' ) ? absint( get_option( 'WPJS_page_size' ) ) : 20000;

 ?>

<?php settings_fields( 'WPJS_settings_fields' ); ?>

<div class="ui-sidebar-wrapper">

  <div class="inside">

	<!--Settings Panel-->
	<div class="panel">

		<div class="panel-header">
			 <h3><?php _e( 'Settings', 'better-search-replace' ); ?></h3>
		</div>

		<div class="panel-content settings">

			<!--Max Page Size-->
			<div class="row last-row">
				<div class="input-text">
					<div class="settings-header">
						<label><strong><?php _e( 'Max Page Size', 'better-search-replace' ); ?></strong></label>
						<span id="bsr-page-size-value"><?php echo absint( $page_size ); ?></span>
					</div>
					<input id="WPJS_page_size" type="hidden" name="WPJS_page_size" value="<?php echo $page_size; ?>" />
					<p class="description"><?php _e( 'If you notice timeouts or are unable to backup/import the database, try decreasing this value.', 'better-search-replace' ); ?></p>
					<div class="slider-wrapper">
						<div id="bsr-page-size-slider" class="bsr-slider"></div>
					</div>
				</div>
			</div>

			<!--Submit Button-->
			<div class="row panel-footer">
				<?php submit_button(); ?>
			</div>

			</div>
		</div>
	</div>

	<?php
	if ( file_exists( WPJS_PATH . 'templates/sidebar.php' ) ) {
		include_once WPJS_PATH . 'templates/sidebar.php';
	}
	?>
  
</div>
