<?php

/**
 * AJAX-specific functionality for the plugin.
 *
 * @link       https://wpjuggler.com
 * @since      1.0.0
 *
 * @package    WP_Juggler_Server
 * @subpackage WP_Juggler_Server/includes
 */

// Prevent direct access.
if (! defined('WPJS_PATH')) exit;

if ( ! class_exists( 'WP_Async_Request' ) ) {
	require_once WPJS_PATH . 'vendor/wp-async-request.php';;
}

if ( ! class_exists( 'WP_Background_Process' ) ) {
	require_once WPJS_PATH . 'vendor/wp-background-process.php';;
}

class WPJS_Background_Process extends WP_Background_Process
{

	protected $action = 'wpjs_process';

	protected function task( $item ) {

		$site_id = $item['site_id'];
		$endpoint = $item['endpoint'];
		$data = $item['data'];
		
		$response = WPJS_Service::call_client_api( $site_id, $endpoint, $data );

		if ( is_wp_error( $response ) ) {

			$log_entry = array(
				'wpjugglersites_id' => $site_id,
				'log_type' => $endpoint,
				'log_result' => 'fail',
				'log_value' =>  $response->get_error_message()
			);
		
		} else {

			// TODO Will never be called because it gets converted to WP Error in WPJS Service Class

			$response_code = wp_remote_retrieve_response_code($response);
		
			switch ($response_code) {
				case 0:
					
					$log_entry = array(
						'wpjugglersites_id' => $site_id,
						'log_type' => $endpoint,
						'log_result' => 'error',
						'log_value' =>  'Remote client is unresponsive'
					);

					break;
				case 401:
					
					$log_entry = array(
						'wpjugglersites_id' => $site_id,
						'log_type' => $endpoint,
						'log_result' => 'error',
						'log_value' =>  '401 - You should check API key'
					);

					break;
				case 500:

					$log_entry = array(
						'wpjugglersites_id' => $site_id,
						'log_type' => $endpoint,
						'log_result' => 'error',
						'log_value' =>  '500 - Internal Server Error on remote client'
					);

					break;
				default:
					if ($response_code >= 400) {

						$log_entry = array(
							'wpjugglersites_id' => $site_id,
							'log_type' => $endpoint,
							'log_result' => 'error',
							'log_value' =>  $response_code . ' - Client error occurred'
						);
			
					} elseif ($response_code >= 500) {

						$log_entry = array(
							'wpjugglersites_id' => $site_id,
							'log_type' => $endpoint,
							'log_result' => 'error',
							'log_value' =>  $response_code . ' - Server error occurred'
						);

					} else {

						$log_entry = array(
							'wpjugglersites_id' => $site_id,
							'log_type' => $endpoint,
							'log_result' => 'succ', 
						);
					}
					break;
			}
		}

		WPJS_Cron_Log::insert_log($log_entry);

		return false;

	}

	protected function complete() {
		parent::complete();
	}
	
}
