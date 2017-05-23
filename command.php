<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

// Temporary - needed until soter-core is ready to go up on Packagist.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// Need function_exists() check because this file is being loaded twice.
if ( ! function_exists( '_soter_command_init' ) ) {
	function _soter_command_init() {
		// This file is being loaded twice so let's keep track of initialization.
		static $soter_command_initialized = false;

		if ( ! $soter_command_initialized ) {
			$ua = implode( ' | ', [
				sprintf( '%s (%s)', get_bloginfo( 'name' ), get_home_url() ),
				'Soter CLI',
				'v0.1.0',
				'https://github.com/ssnepenthe/soter-command',
			] );

			$http = new Soter_Core\Cached_Http_Client(
				new Soter_Core\WP_Http_Client( $ua ),
				new Soter_Core\WP_Transient_Cache( $GLOBALS['wpdb'], 'soter_command' )
			);

			$checker = new Soter_Core\Checker(
				new Soter_Core\Api_Client( $http ),
				new Soter_Core\WP_Package_Manager
			);

			$command = new Soter_Command\Soter_Command( $checker );

			WP_CLI::add_command( 'soter', $command );

			$soter_command_initialized = true;
		}
	}

	// Delay command registration so we can use get_bloginfo(), get_home_url().
	WP_CLI::add_hook( 'after_wp_load', '_soter_command_init' );
}
