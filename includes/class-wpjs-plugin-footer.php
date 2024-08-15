<?php
/**
* Plugin footer functionality for the plugin
*
 * @link       https://wpjuggler.com
 * @since      1.0.0
 *
 * @package    WP_Juggler_Server
 * @subpackage WP_Juggler_Server/includes
*/
// Prevent direct access.
if ( ! defined( 'WPJS_PATH' ) ) exit;

class WPJS_Plugin_Footer {

    /**
     * Filter admin footer text for WPJS pages
     *
     * @param string $text
     * @return string
     * @handles admin_footer_text
     **/
    public function admin_footer_text( $text ) {
        if ( ! WPJS_Utils::is_WPJS_screen() ) {
            return $text;
        }

        $product_link = WPJS_Utils::external_link(
			WPJS_Utils::WPJS_url(
				'',
				[
                    'utm_source'   => 'WPJS_free',
                    'utm_medium'   => 'insideplugin',
                    'utm_campaign' => 'plugin_footer',
                    'utm_content'  => 'footer_colophon'
                ]
			),
			WPJS_NAME
		);
        $wpe_link = WPJS_Utils::external_link(
            WPJS_Utils::wpe_url(
                '',
                [
                    'utm_source'  => 'WPJS_plugin',
                    'utm_content' => 'WPJS_free_plugin_footer_text'
                ]
            ), 
            'WP Engine'
        );
        return sprintf(
            /* translators: %1$s is a link to BSR's website, and %2$s is a link to WP Engine's website. */
            __( '%1$s is developed and maintained by %2$s.', 'better-search-replace' ),
            $product_link,
            $wpe_link
        );
    }

    /**
     * Filter update footer text for BSR pages
     *
     * @param string $content
     * @return string
     * @handles update_footer
     **/
    public function update_footer( $content ) {
        if ( ! WPJS_Utils::is_WPJS_screen() ) {
            return $content;
        }
        $utm_params = [
            'utm_source'   => 'WPJS_free',
            'utm_campaign' => 'plugin_footer',
            'utm_content'  => 'footer_navigation'
        ];

        $links[] = WPJS_Utils::external_link(
			WPJS_Utils::WPJS_url(
				'/docs/',
				$utm_params
			),
			__( 'Documentation', 'better-search-replace' )
		);

		$links[] = '<a href="' . WPJS_Utils::plugin_page_url() . '&tab=WPJS_help">' . __( 'Support', 'better-search-replace' ) . '</a>';

		$links[] = WPJS_Utils::external_link(
			WPJS_Utils::WPJS_url(
				'/feedback/',
				$utm_params
			),
			__( 'Feedback', 'better-search-replace' )
		);
        if ( defined( 'WPJS_NAME' ) && defined( 'WPJS_VERSION' ) ) {
            $links[] = WPJS_NAME . ' ' . WPJS_VERSION;
        }
		
        return join( ' &#8729; ', $links );
    }
}