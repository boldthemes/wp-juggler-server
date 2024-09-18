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

if (! class_exists('WP_Async_Request')) {
	require_once WPJS_PATH . 'vendor/wp-async-request.php';;
}

if (! class_exists('WP_Background_Process')) {
	require_once WPJS_PATH . 'vendor/wp-background-process.php';;
}

class WPJS_Background_Process extends WP_Background_Process
{

	protected $prefix = 'wpjs';

	protected $action = 'wpjs_process';

	protected function task($item)
	{

		$site_id = $item['siteId'];
		$endpoint = $item['endpoint'];
		$data = $item['data'];

		if ($endpoint == 'confirmClientApi') {

			$response = WPJS_Service::call_client_api($site_id, $endpoint, $data);

			if (is_wp_error($response)) {

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
							'log_result' => 'fail',
							'log_value' =>  'Remote client is unresponsive'
						);

						break;
					case 401:

						$log_entry = array(
							'wpjugglersites_id' => $site_id,
							'log_type' => $endpoint,
							'log_result' => 'fail',
							'log_value' =>  '401 - You should check API key'
						);

						break;
					case 500:

						$log_entry = array(
							'wpjugglersites_id' => $site_id,
							'log_type' => $endpoint,
							'log_result' => 'fail',
							'log_value' =>  '500 - Internal Server Error on remote client'
						);

						break;
					default:
						if ($response_code >= 400) {

							$log_entry = array(
								'wpjugglersites_id' => $site_id,
								'log_type' => $endpoint,
								'log_result' => 'fail',
								'log_value' =>  $response_code . ' - Client error occurred'
							);
						} elseif ($response_code >= 500) {

							$log_entry = array(
								'wpjugglersites_id' => $site_id,
								'log_type' => $endpoint,
								'log_result' => 'fail',
								'log_value' =>  $response_code . ' - Server error occurred'
							);
						} else {
							
							$body = json_decode(wp_remote_retrieve_body($response), true);

							if( $body['data']['multisite'] ){
								update_post_meta($site_id, 'wp_juggler_multisite', 'on');
							} else {
								delete_post_meta($site_id, 'wp_juggler_multisite');
							}

							update_post_meta($site_id, 'wp_juggler_wordpress_version', $body['data']['wp_version']);

							// Ne upisujemo uspesne checkove

							/* $log_entry = array(
								'wpjugglersites_id' => $site_id,
								'log_type' => $endpoint,
								'log_result' => 'succ',
								'log_data' => json_encode($body['data'])
							); */
							
							$log_entry = false;

						}
						break;
				}
			}

			WPJS_Cron_Log::insert_log($log_entry);
		}

		if ($endpoint == 'confirmFrontEnd') {

			$frontend_ping_url = get_post_meta($site_id, 'wp_juggler_frontend_ping_url', true);
			$frontend_ping_string = get_post_meta($site_id, 'wp_juggler_frontend_ping_string', true);
			$site_url = get_post_meta($site_id, 'wp_juggler_server_site_url', true);

			$url_final = $frontend_ping_string ? $frontend_ping_url : $site_url;

			if ($frontend_ping_string && preg_replace('/\s+/', ' ', $frontend_ping_string) == '') {
				$frontend_ping_string = false;
			}

			$front_end_check = WPJS_Service::check_front_end($url_final, $site_url, $frontend_ping_string);

			if (!is_wp_error($front_end_check)) {

				//Ne upisujemo uspesne checkove

				/*$log_entry = array(
					'wpjugglersites_id' => $site_id,
					'log_type' => $endpoint,
					'log_result' => 'succ',
				);*/

				$log_entry = false;

			} else {

				$log_entry = array(
					'wpjugglersites_id' => $site_id,
					'log_type' => $endpoint,
					'log_result' => 'error',
					'log_value' =>  $front_end_check->get_error_message()
				);
			}

			WPJS_Cron_Log::insert_log($log_entry);
		}

		if ($endpoint == 'initiateTask') {

			$log_entry = array(
				'wpjugglersites_id' => $site_id,
				'log_type' => $data['taskType'],
				'log_result' => 'init'
			);

			$task_id = WPJS_Cron_Log::insert_log($log_entry);

			$data['taskId'] = $task_id;

			$response = WPJS_Service::call_client_api($site_id, $endpoint, $data);

			if (is_wp_error($response)) {

				$log_entry = array(
					'ID' => $task_id,
					'log_result' => 'fail',
					'log_value' =>  $response->get_error_message()
				);

				$task_id = WPJS_Cron_Log::update_log($log_entry);
			} else {

				$body = json_decode(wp_remote_retrieve_body($response), true);

				if ($data['taskType'] == 'checkPlugins') {
					
					$plugins = $body['data']['plugins_data'];
					$themes = $body['data']['themes_data'];

					foreach ($plugins as $plugin => $plugininfo) {
						if( $plugininfo['ChecksumFiles'] ){
							$cf = base64_decode($plugininfo['ChecksumFiles']);
							$unzipped = gzuncompress($cf);
							$plugins[$plugin]['ChecksumFiles'] = json_decode( $unzipped, true );
						} else {
							$plugins[$plugin]['ChecksumFiles'] = false;
						}
					}
		
					$data_checksum = WPJS_Service::$plugin_checksum->wpjs_plugin_checksum( $plugins );
		
					foreach ($plugins as $plugin => $plugininfo) {
						$plugins[$plugin]['ChecksumDetails'] = [];
						unset($plugins[$plugin]['ChecksumFiles']);
						if( in_array( $plugininfo['Slug'], $data_checksum['failures_list'] )){
							$plugins[$plugin]['Checksum'] = false;
						} else {
							$plugins[$plugin]['Checksum'] = true;
						}
					}
		
					foreach ($data_checksum['failures_details'] as $failure){
						$plugin_file = WPJS_Service::findElementByAttribute($plugins, 'Slug', $failure['plugin_name']);
						$plugins[$plugin_file]['ChecksumDetails'][] = $failure;
					}

					foreach ($plugins as $plugin => $plugininfo) {
						$plugin_vulnerabilities = WPJS_Service::get_plugin_vulnerabilities( $plugininfo['Slug'], $plugininfo['Version']);
						$plugins[$plugin]['Vulnerabilities'] = $plugin_vulnerabilities;
					}

					$log_entry = array(
						'ID' => $task_id,
						'log_result' => 'succ',
						'log_value' =>  null,
						'log_data' => json_encode(
							array(
								'plugins_data' => $plugins,
								'themes_data' => $themes
							)
						)		
					);
	
					WPJS_Cron_Log::update_log($log_entry);

				} else {

					$log_entry = array(
						'ID' => $task_id,
						'log_result' => 'succ',
						'log_value' =>  null,
						'log_data' => json_encode($body['data'])
					);
	
					WPJS_Cron_Log::update_log($log_entry);

				}

			}
		}

		return false;
	}

	protected function complete()
	{
		parent::complete();
	}
}
