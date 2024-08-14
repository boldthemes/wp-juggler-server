<?php

// Prevent direct access.
if ( ! defined( 'WPJS_PATH' ) ) exit;

class WPJS_Templates_Helper {
    /**
     * Returns a fully qualified path for the given active tab name
     * if the file name is not supported, the default template path is returned.
     *
     * @param string $active_tab
     * @return string
     */
    public static function get_tab_template($active_tab) {
        switch($active_tab) {
            case 'wpjs_settings':
                return WPJS_PATH . 'templates/wpjs-settings.php';
            case 'wpjs_help':
                return WPJS_PATH . 'templates/wpjs-help.php';
            default:
                return WPJS_PATH . 'templates/wpjs-search-replace.php';
        }
    }
}