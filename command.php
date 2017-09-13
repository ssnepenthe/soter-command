<?php
/**
 * Registers the Soter command with WP-CLI.
 *
 * @package soter-command
 */

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

// Need function_exists() check because this file is loaded twice in earlier version of WP-CLI.
if ( ! function_exists( '_soter_command_init' ) ) {
	/**
	 * Create a Soter_Command instance and register it with WP-CLI.
	 *
	 * @return void
	 */
	function _soter_command_init() {
		// This file is being loaded twice so let's keep track of initialization.
		static $initialized = false;

		if ( $initialized ) {
			return;
		}

		$ua = sprintf(
			'%s (%s) | Soter CLI | v0.1.0 | https://github.com/ssnepenthe/soter-command',
			get_bloginfo( 'name' ),
			get_home_url()
		);

		$http = new Soter_Core\Cached_Http_Client(
			new Soter_Core\WP_Http_Client( $ua ),
			new Soter_Core\WP_Transient_Cache( 'soter', HOUR_IN_SECONDS )
		);

		$checker = new Soter_Core\Checker(
			new Soter_Core\Api_Client( $http ),
			new Soter_Core\WP_Package_Manager
		);

		$command = new Soter_Command\Soter_Command( $checker );

		WP_CLI::add_command( 'soter', $command );

		$initialized = true;
	}

	// Delay command registration so we can use get_bloginfo(), get_home_url().
	WP_CLI::add_hook( 'after_wp_load', '_soter_command_init' );
}
