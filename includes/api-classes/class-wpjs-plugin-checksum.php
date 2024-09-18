<?php

if (! defined('WPJS_PATH')) exit;

require_once WPJS_PATH . 'includes/api-classes/class-wpjs-wp-org-api.php';

include_once(ABSPATH . 'wp-admin/includes/plugin.php');
include_once(ABSPATH . 'wp-admin/includes/file.php');
include_once(ABSPATH . 'wp-admin/includes/misc.php');
include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
include_once(ABSPATH . 'wp-admin/includes/plugin-install.php');

class WPJSPluginChecksum {

    // https://github.com/wp-cli/checksum-command/blob/main/src/Checksum_Plugin_Command.php

    private $url_template = 'https://downloads.wordpress.org/plugin-checksums/{slug}/{version}.json';
    
    private $plugins_data;

	private $errors = array();
    
    public static function normalize_directory_separators( $path ) {
		return str_replace( '\\', '/', $path );
	}

	public function wpjs_plugin_checksum( $plugins ){

		if ( empty( $plugins ) ) {
			return false;
		}

		foreach ( $plugins as $plugin => $plugin_info ) {

			$files = $plugin_info['ChecksumFiles'];

			if( $files ){

				$version = $plugin_info['Version'];

				$wp_org_api = new WPJSWpOrgApi();

				try {
					$checksums = $wp_org_api->get_plugin_checksums( $plugin_info['Slug'], $version );
				} catch ( Exception $exception ) {
					$checksums = false;
				}

				if ( false === $checksums ) {
					continue;
				}


				foreach ( $checksums as $file => $checksum_array ) {
					if ( ! array_key_exists( $file, $files ) ) {
						$this->add_error( $plugin_info['Slug'], $file, 'File is missing' );
					}
				}

				foreach ( $files as $file => $signatures) {
					if ( ! array_key_exists( $file, $checksums ) ) {
						$this->add_error( $plugin_info['Slug'], $file, 'File was added' );
						continue;
					}

					if ( $this->is_soft_change_file( $file ) ) {
						continue;
					}
					$result = $this->compare_hash( $signatures, $checksums[ $file ] );
					if ( true !== $result ) {
						$this->add_error( $plugin_info['Slug'], $file, is_string( $result ) ? $result : 'Checksum does not match' );
					}
				}
				
			}

		}

		/* if ( ! empty( $this->errors ) ) {
			$formatter = new Formatter(
				$assoc_args,
				array( 'plugin_name', 'file', 'message' )
			);
			$formatter->display_items( $this->errors );
		} */

		$total     = count( $plugins );
		$failures  = count( array_unique( array_column( $this->errors, 'plugin_name' ) ) );
		$successes = $total - $failures;

		return([
			'total' => $total,
			'failures' => $failures,
			'failures_list' => array_unique( array_column( $this->errors, 'plugin_name' ) ),
			'failures_details' => $this->errors,
			'successes' => $successes
		]);

		/* Utils\report_batch_operation_results(
			'plugin',
			'verify',
			$total,
			$successes,
			$failures,
			$skips
		); */
		
	}

	private function compare_hash($real_hash, $wporg_hash){
		if (isset($real_hash['sha256']) && isset($wporg_hash['sha256'])) {
			return $real_hash['sha256'] === $wporg_hash['sha256'];
		} elseif (isset($real_hash['md5']) && isset($wporg_hash['md5'])) {
			return $real_hash['md5'] === $wporg_hash['md5'];
		} else {
			return true;
		} 
	}

    public function get_plugin_checksum(){
        
		$fetcher     = new UnfilteredPlugin();

		// $all         = (bool) Utils\get_flag_value( $assoc_args, 'all', false );
		// $strict      = (bool) Utils\get_flag_value( $assoc_args, 'strict', false );
		$strict = false;
		// $insecure    = (bool) Utils\get_flag_value( $assoc_args, 'insecure', false );
		
		$plugins     = $fetcher->get_many( $this->get_all_plugin_names() );
		$exclude = '';
		//$exclude     = Utils\get_flag_value( $assoc_args, 'exclude', '' );
		//$version_arg = isset( $assoc_args['version'] ) ? $assoc_args['version'] : '';

		if ( empty( $plugins ) && ! $all ) {
		  //WP_CLI::error( 'You need to specify either one or more plugin slugs to check or use the --all flag to check all plugins.' );
		}

		$exclude_list = explode( ',', $exclude );

		$skips = 0;

		$assoc_args = [];

		foreach ( $plugins as $plugin ) {
			$version = empty( $version_arg ) ? $this->get_plugin_version( $plugin->file ) : $version_arg;

			if ( in_array( $plugin->name, $exclude_list, true ) ) {
				++$skips;
				continue;
			}

			if ( 'hello' === $plugin->name ) {
				$this->verify_hello_dolly_from_core( $assoc_args );
				continue;
			}

			if ( false === $version ) {
				//WP_CLI::warning( "Could not retrieve the version for plugin {$plugin->name}, skipping." );
				++$skips;
				continue;
			}

			//$wp_org_api = new WpOrgApi( [ 'insecure' => $insecure ] );
			$wp_org_api = new WPJCWpOrgApi();

			try {
				$checksums = $wp_org_api->get_plugin_checksums( $plugin->name, $version );
			} catch ( Exception $exception ) {
				// WP_CLI::warning( $exception->getMessage() );
				$checksums = false;
			}

			if ( false === $checksums ) {
				// WP_CLI::warning( "Could not retrieve the checksums for version {$version} of plugin {$plugin->name}, skipping." );
				++$skips;
				continue;
			}

			$files = $this->get_plugin_files( $plugin->file );

			foreach ( $checksums as $file => $checksum_array ) {
				if ( ! in_array( $file, $files, true ) ) {
					$this->add_error( $plugin->name, $file, 'File is missing' );
				}
			}

			foreach ( $files as $file ) {
				if ( ! array_key_exists( $file, $checksums ) ) {
					$this->add_error( $plugin->name, $file, 'File was added' );
					continue;
				}

				if ( ! $strict && $this->is_soft_change_file( $file ) ) {
					continue;
				}
				$result = $this->check_file_checksum( dirname( $plugin->file ) . '/' . $file, $checksums[ $file ] );
				if ( true !== $result ) {
					$this->add_error( $plugin->name, $file, is_string( $result ) ? $result : 'Checksum does not match' );
				}
			}
		}

		/* if ( ! empty( $this->errors ) ) {
			$formatter = new Formatter(
				$assoc_args,
				array( 'plugin_name', 'file', 'message' )
			);
			$formatter->display_items( $this->errors );
		} */

		$total     = count( $plugins );
		$failures  = count( array_unique( array_column( $this->errors, 'plugin_name' ) ) );
		$successes = $total - $failures - $skips;

		return([
			'total' => $total,
			'failures' => $failures,
			'failures_list' => array_unique( array_column( $this->errors, 'plugin_name' ) ),
			'successes' => $successes
		]);

		/* Utils\report_batch_operation_results(
			'plugin',
			'verify',
			$total,
			$successes,
			$failures,
			$skips
		); */
	}

	private function verify_hello_dolly_from_core( $assoc_args ) {
		$file       = 'hello.php';
		$wp_version = get_bloginfo( 'version', 'display' );
		
		//$insecure   = (bool) Utils\get_flag_value( $assoc_args, 'insecure', false );
		//$wp_org_api = new WpOrgApi( [ 'insecure' => $insecure ] );
		$wp_org_api = new WPJCWpOrgApi();

		$locale     = '';

		try {
			$checksums = $wp_org_api->get_core_checksums( $wp_version, empty( $locale ) ? 'en_US' : $locale );
		} catch ( Exception $exception ) {
			//WP_CLI::error( $exception );
		}

		if ( ! is_array( $checksums ) || ! isset( $checksums['wp-content/plugins/hello.php'] ) ) {
			//WP_CLI::error( "Couldn't get hello.php checksum from WordPress.org." );
		}

		$md5_file = md5_file( $this->get_absolute_path( '/' ) . $file );
		if ( $md5_file !== $checksums['wp-content/plugins/hello.php'] ) {
			$this->add_error( 'hello', $file, 'Checksum does not match' );
		}
	}

	private function add_error( $plugin_name, $file, $message ) {
		$error['plugin_name'] = $plugin_name;
		$error['file']        = $file;
		$error['message']     = $message;
		$this->errors[]       = $error;
	}

	private function get_plugin_version( $path ) {
		if ( ! isset( $this->plugins_data ) ) {
			$this->plugins_data = get_plugins();
		}

		if ( ! array_key_exists( $path, $this->plugins_data ) ) {
			return false;
		}

		return $this->plugins_data[ $path ]['Version'];
	}

	private function get_all_plugin_names() {
		$names = array();
		foreach ( get_plugins() as $file => $details ) {
			//TODO
			$names[] = $this->get_plugin_name( $file );
		}

		return $names;
	}

	private function get_plugin_name( $basename ) {
		if ( false === strpos( $basename, '/' ) ) {
			$name = basename( $basename, '.php' );
		} else {
			$name = dirname( $basename );
		}
	
		return $name;
	}

	public function get_plugin_files( $path ) {
		$folder = dirname( $this->get_absolute_path( $path ) );

		// Return single file plugins immediately, to avoid iterating over the
		// entire plugins folder.
		if ( WP_PLUGIN_DIR === $folder ) {
			return (array) $path;
		}

		return $this->get_files( trailingslashit( $folder ) );
	}

	private function check_file_checksum( $path, $checksums ) {
		if ( $this->supports_sha256()
			&& array_key_exists( 'sha256', $checksums )
		) {
			$sha256 = $this->get_sha256( $this->get_absolute_path( $path ) );
			return in_array( $sha256, (array) $checksums['sha256'], true );
		}

		if ( ! array_key_exists( 'md5', $checksums ) ) {
			return 'No matching checksum algorithm found';
		}

		$md5 = $this->get_md5( $this->get_absolute_path( $path ) );

		return in_array( $md5, (array) $checksums['md5'], true );
	}

	public function return_checksum( $path ){
		$checksums = [];
		if ( $this->supports_sha256() ){
			$checksums['sha256'] = $this->get_sha256( $this->get_absolute_path( $path ) );
		} 
		
		$checksums['md5'] = $this->get_md5( $this->get_absolute_path( $path ) );

		return($checksums);
		
	}

	private function supports_sha256() {
		return true;
	}

	private function get_sha256( $filepath ) {
		return hash_file( 'sha256', $filepath );
	}

	private function get_md5( $filepath ) {
		return hash_file( 'md5', $filepath );
	}

	private function get_absolute_path( $path ) {
		return WP_PLUGIN_DIR . '/' . $path;
	}

	private function get_soft_change_files() {
		static $files = array(
			'readme.txt',
			'readme.md',
		);

		return $files;
	}

	private function is_soft_change_file( $file ) {
		return in_array( strtolower( $file ), $this->get_soft_change_files(), true );
	}


    protected function filter_file( $filepath ) {
		return true;
	}

	protected function get_files( $path ) {
		$filtered_files = array();
		try {
			$files = new RecursiveIteratorIterator(
				new RecursiveCallbackFilterIterator(
					new RecursiveDirectoryIterator(
						$path,
						RecursiveDirectoryIterator::SKIP_DOTS
					),
					function ( $current ) use ( $path ) {
						return $this->filter_file( self::normalize_directory_separators( substr( $current->getPathname(), strlen( $path ) ) ) );
					}
				),
				RecursiveIteratorIterator::CHILD_FIRST
			);
			foreach ( $files as $file_info ) {
				if ( $file_info->isFile() ) {
					$filtered_files[] = self::normalize_directory_separators( substr( $file_info->getPathname(), strlen( $path ) ) );
				}
			}
		} catch ( Exception $e ) {
			WP_CLI::error( $e->getMessage() );
		}

		return $filtered_files;
	}

}

class UnfilteredPlugin {

	/**
	 * @var string $msg Error message to use when invalid data is provided
	 */
	protected $msg = "The '%s' plugin could not be found.";

	/**
	 * Get a plugin object by name.
	 *
	 * @param string $name
	 *
	 * @return object|false
	 */
	public function get( $name ) {
		foreach ( get_plugins() as $file => $_ ) {
			if ( "{$name}.php" === $file ||
				( $name && $file === $name ) ||
				( dirname( $file ) === $name && '.' !== $name ) ) {
				return (object) compact( 'name', 'file' );
			}
		}

		return false;
	}

	public function get_check( $arg ) {
		$item = $this->get( $arg );

		if ( ! $item ) {
			//WP_CLI::error( sprintf( $this->msg, $arg ) );
		}

		return $item;
	}

	public function get_many( $args ) {
		$items = [];

		foreach ( $args as $arg ) {
			$item = $this->get( $arg );

			if ( $item ) {
				$items[] = $item;
			} else {
				//WP_CLI::warning( sprintf( $this->msg, $arg ) );
			}
		}

		return $items;
	}
}