<?php

namespace Soter_Command;

use WP_CLI;
use Soter_Core\Checker_Interface;

class Soter_Command {
	protected $checker;
	protected $progress_bar = null;

	public function __construct( Checker_Interface $checker ) {
		$this->checker = $checker;
	}

	/**
	 * Check a plugin for vulnerabilities.
	 *
	 * ## OPTIONS
	 *
	 * <slug>
	 * : The plugin slug to check.
	 *
	 * [<version>]
	 * : The plugin version to check.
	 * ---
	 * default: null
	 * ---
	 *
	 * [--format=<format>]
	 * : Results output format.
	 * ---
	 * default: table
	 * options:
	 *   - count
	 *   - csv
	 *   - ids
	 *   - json
	 *   - table
	 *   - yaml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 * ---
	 * default: title,vuln_type,fixed_in
	 *
	 * @subcommand check-plugin
	 *
	 * @param  array $args       Positional args.
	 * @param  array $assoc_args Associative args.
	 */
	public function check_plugin( array $args, array $assoc_args ) {
		try {
			$slug = $args[0];
			$version = isset( $args[1] ) ? $args[1] : null;

			$response = $this->checker->get_client()->plugins( $slug );
			$vulnerabilities = $response->get_vulnerabilities_by_version( $version );

			$this->display_results( $vulnerabilities, $assoc_args );
		} catch ( \RuntimeException $e ) {
			WP_CLI::error( $e->getMessage() );
		}
	}

	/**
	 * Check all currently installed plugins for vulnerabilities.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Results output format.
	 * ---
	 * default: table
	 * options:
	 *   - count
	 *   - csv
	 *   - ids
	 *   - json
	 *   - table
	 *   - yaml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 * ---
	 * default: title,vuln_type,fixed_in
	 *
	 * [--ignore=<ignore>]
	 * : Comma separated list of plugin slugs to ignore.
	 *
	 * @subcommand check-plugins
	 *
	 * @param  array $_          Unused positional args.
	 * @param  array $assoc_args Associative args.
	 */
	public function check_plugins( array $_, array $assoc_args ) {
		try {
			$this->start_progress_bar(
				$assoc_args,
				$this->checker->get_plugin_count()
			);

			$vulnerabilities = $this->checker->check_plugins(
				$this->get_ignored_slugs( $assoc_args )
			);

			$this->finish_progress_bar();
			$this->display_results( $vulnerabilities, $assoc_args );
		} catch ( \RuntimeException $e ) {
			WP_CLI::error( $e->getMessage() );
		}
	}

	/**
	 * Check a theme for vulnerabilities.
	 *
	 * ## OPTIONS
	 *
	 * <slug>
	 * : The theme slug to check.
	 *
	 * [<version>]
	 * : The theme version to check.
	 * ---
	 * default: null
	 * ---
	 *
	 * [--format=<format>]
	 * : Results output format.
	 * ---
	 * default: table
	 * options:
	 *   - count
	 *   - csv
	 *   - ids
	 *   - json
	 *   - table
	 *   - yaml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 * ---
	 * default: title,vuln_type,fixed_in
	 *
	 * @subcommand check-theme
	 *
	 * @param  array $args       Positional args.
	 * @param  array $assoc_args Associative args.
	 */
	public function check_theme( array $args, array $assoc_args ) {
		try {
			$slug = $args[0];
			$version = isset( $args[1] ) ? $args[1] : null;

			$response = $this->checker->get_client()->themes( $slug );
			$vulnerabilities = $response->get_vulnerabilities_by_version( $version );

			$this->display_results( $vulnerabilities, $assoc_args );
		} catch ( \RuntimeException $e ) {
			WP_CLI::error( $e->getMessage() );
		}
	}

	/**
	 * Check all currently installed themes for vulnerabilities.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Results output format.
	 * ---
	 * default: table
	 * options:
	 *   - count
	 *   - csv
	 *   - ids
	 *   - json
	 *   - table
	 *   - yaml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 * ---
	 * default: title,vuln_type,fixed_in
	 *
	 * [--ignore=<ignore>]
	 * : Comma separated list of theme slugs to ignore.
	 *
	 * @subcommand check-themes
	 *
	 * @param  array $_          Unused positional args.
	 * @param  array $assoc_args Associative args.
	 */
	public function check_themes( array $_, array $assoc_args ) {
		try {
			$this->start_progress_bar(
				$assoc_args,
				$this->checker->get_theme_count()
			);

			$vulnerabilities = $this->checker->check_themes(
				$this->get_ignored_slugs( $assoc_args )
			);

			$this->finish_progress_bar();
			$this->display_results( $vulnerabilities, $assoc_args );
		} catch ( \RuntimeException $e ) {
			WP_CLI::error( $e->getMessage() );
		}
	}

	/**
	 * Check a version of WordPress for vulnerabilities.
	 *
	 * ## OPTIONS
	 *
	 * <version>
	 * : The version of WordPress to check.
	 *
	 * [--format=<format>]
	 * : Results output format.
	 * ---
	 * default: table
	 * options:
	 *   - count
	 *   - csv
	 *   - ids
	 *   - json
	 *   - table
	 *   - yaml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 * ---
	 * default: title,vuln_type,fixed_in
	 *
	 * @alias check-wp
	 * @subcommand check-wordpress
	 *
	 * @param  array $args       Positional args.
	 * @param  array $assoc_args Associative args.
	 */
	public function check_wordpress( array $args, array $assoc_args ) {
		$slug = str_replace( '.', '', $args[0] );

		try {
			$response = $this->checker->get_client()->wordpresses( $slug );
			$vulnerabilities = $response->get_vulnerabilities();

			$this->display_results( $vulnerabilities, $assoc_args );
		} catch ( \RuntimeException $e ) {
			WP_CLI::error( $e->getMessage() );
		}
	}

	/**
	 * Check the currently installed version of WordPress for vulnerabilities.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Results output format.
	 * ---
	 * default: table
	 * options:
	 *   - count
	 *   - csv
	 *   - ids
	 *   - json
	 *   - table
	 *   - yaml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 * ---
	 * default: title,vuln_type,fixed_in
	 *
	 * [--ignore=<ignore>]
	 * : Comma separated list of WordPress slugs to ignore - WordPress slugs are equivalent to the version stripped of any non-numeric characters (e.g. 4.7.4 becomes 474).
	 *
	 * @subcommand check-wordpresses
	 *
	 * @param  array $_          Unused positional args.
	 * @param  array $assoc_args Associative args.
	 */
	public function check_wordpresses( array $_, array $assoc_args ) {
		try {
			$this->start_progress_bar(
				$assoc_args,
				$this->checker->get_wordpress_count()
			);

			$vulnerabilities = $this->checker->check_wordpress(
				$this->get_ignored_slugs( $assoc_args )
			);

			$this->finish_progress_bar();
			$this->display_results( $vulnerabilities, $assoc_args );
		} catch ( \RuntimeException $e ) {
			WP_CLI::error( $e->getMessage() );
		}
	}

	/**
	 * Check a full site for vulnerabilities.
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Results output format.
	 * ---
	 * default: table
	 * options:
	 *   - count
	 *   - csv
	 *   - ids
	 *   - json
	 *   - table
	 *   - yaml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 * ---
	 * default: title,vuln_type,fixed_in
	 *
	 * [--ignore=<ignore>]
	 * : Comma separated list of WordPress slugs to ignore. Note that WordPress slugs are equivalent to the version stripped of any non-numeric characters (e.g. 4.7.4 becomes 474).
	 *
	 * @subcommand check-site
	 *
	 * @param  array $_          Unused positional args.
	 * @param  array $assoc_args Associative args.
	 */
	public function check_site( array $_, array $assoc_args ) {
		try {
			$this->start_progress_bar(
				$assoc_args,
				$this->checker->get_package_count()
			);

			$vulnerabilities = $this->checker->check_site(
				$this->get_ignored_slugs( $assoc_args )
			);

			$this->finish_progress_bar();
			$this->display_results( $vulnerabilities, $assoc_args );
		} catch ( \RuntimeException $e ) {
			WP_CLI::error( $e->getMessage() );
		}
	}

	/**
	 * Display the results of an individual check.
	 *
	 * @param  Soter_Core\Vulnerability_Interface[] $vulnerabilities List of vulnerabilities.
	 * @param  array                                $assoc_args      Associative args.
	 */
	protected function display_results( array $vulnerabilities, array $assoc_args ) {
		// Get format and fields. WP-CLI handles validation for us.
		$format = WP_CLI\Utils\get_flag_value( $assoc_args, 'format' );
		$fields = array_map(
			'trim',
			explode( ',', WP_CLI\Utils\get_flag_value( $assoc_args, 'fields' ) )
		);

		// Convert vulns to arrays.
		$for_display = array_map( function( $vuln ) {
			return $vuln->get_data();
		}, $vulnerabilities );

		// Timestamps may need to be re-formatted based on requested output method.
		$timestamps = [ 'created_at', 'updated_at', 'published_date' ];

		// @todo Gross.
		switch ( $format ) {
			case 'table':
				$for_display = array_map( function( $vuln ) use ( $timestamps ) {
					foreach ( $timestamps as $timestamp ) {
						if (
							isset( $vuln[ $timestamp ] )
							&& $vuln[ $timestamp ] instanceof \DateTime
						) {
							$vuln[ $timestamp ] = $vuln[ $timestamp ]->format(
								'd F Y'
							);
						} else {
							$vuln[ $timestamp ] = 'UNKNOWN';
						}
					}

					// Key should always exist, may be null.
					if ( ! isset( $vuln['fixed_in'] ) ) {
						$vuln['fixed_in'] = 'NOT FIXED YET';
					}

					return $vuln;
				}, $for_display );
				break;
			case 'ids':
				$for_display = array_map( function( $vuln ) {
					return isset( $vuln['id'] ) ? $vuln['id'] : 0;
				}, $for_display );
				break;
			default:
				$for_display = array_map( function( $vuln ) use ( $timestamps ) {
					foreach ( $timestamps as $timestamp ) {
						if (
							isset( $vuln[ $timestamp ] )
							&& $vuln[ $timestamp ] instanceof \DateTime
						) {
							$vuln[ $timestamp ] = $vuln[ $timestamp ]->getTimestamp();
						}
					}

					return $vuln;
				}, $for_display );
		}

		WP_CLI\Utils\format_items( $format, $for_display, $fields );
	}

	protected function start_progress_bar( array $assoc_args, $package_count ) {
		if ( ! is_null( $this->progress_bar ) ) {
			// @todo
			WP_CLI::error( 'Too much progress for one request!' );
		}

		if (
			isset( $assoc_args['format'] )
			&& 'table' !== $assoc_args['format']
		) {
			// We don't need a progress bar for machine readable formats.
			return;
		}

		$this->progress_bar = $progress_bar = WP_CLI\Utils\make_progress_bar(
			sprintf( 'Checking %s packages', $package_count ),
			$package_count
		);

		add_action(
			'soter_core_check_package_complete',
			function() use ( $progress_bar) {
				$progress_bar->tick();
			}
		);
	}

	protected function finish_progress_bar() {
		if ( is_null( $this->progress_bar ) ) {
			return;
		}

		$this->progress_bar->finish();
	}

	protected function get_ignored_slugs( array $assoc_args ) {
		$ignored = WP_CLI\Utils\get_flag_value( $assoc_args, 'ignore' );

		if ( ! $ignored ) {
			return [];
		}

		return array_map( 'trim', explode( ',', $ignored ) );
	}
}
