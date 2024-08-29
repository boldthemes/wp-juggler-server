<?php
/**
 * Displays the "System Info" tab.
 *
 * @link       https://wpjuggler.com
 * @since      1.0.0
 *
 * @package    WP_Juggler_Server
 * @subpackage WP_Juggler_Server/includes
 */

// Prevent direct access.
if ( ! defined( 'WPJS_PATH' ) ) exit;

if (!current_user_can('manage_options')) exit;

?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

	<script>
		const wpjs_control_panel_object = {
			
			'ajaxurl': '<?php echo admin_url("admin-ajax.php") ?>',
			'nonce': '<?php echo wp_create_nonce("wpjs-control-panel") ?>',
			'adminurl': '<?php echo admin_url() ?>'

		}
	</script>

	<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url(__DIR__) . 'assets/control-panel/wpjs-control-panel.css' ?>" /> 
</head>
<body <?php body_class(); ?>>
	<div id="app">
	</div>
	<script src="<?php echo plugin_dir_url(__DIR__) . 'assets/control-panel/wpjs-control-panel.js'?>" ></script>
</body>
</html>

