<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// Need function_exists() check because this file is being loaded twice.
if ( ! function_exists( '_soter_command_init' ) ) {
	function _soter_command_init() {
		// This file is being loaded twice so let's keep track of initialization.
		static $soter_command_initialized = null;

		if ( ! $soter_command_initialized ) {
			$http = new Soter_Core\WP_Http_Client( '@todo' );
			$cache = new Soter_Core\WP_Transient_Cache( 'soter_command' );
			$client = new Soter_Core\Api_Client( $http, $cache );
			$checker = new Soter_Core\Checker( $client );

			WP_CLI::add_command(
				'soter',
				new Soter_Command\Soter_Command( $checker )
			);

			$soter_command_initialized = true;
		}
	}
}

_soter_command_init();
