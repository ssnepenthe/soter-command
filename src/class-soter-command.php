<?php
/**
 * Soter_Command class.
 *
 * @package soter-command
 */

namespace Soter_Command;

use WP_CLI;
use WP_CLI_Command;
use Soter_Core\Checker;
use Soter_Core\Package;
use Soter_Core\Vulnerabilities;

/**
 * Check a site or individual packages against the WPScan API.
 */
class Soter_Command extends WP_CLI_Command {
	/**
	 * Checker instance.
	 *
	 * @var Checker
	 */
	protected $checker;

	/**
	 * Progress bar instance.
	 *
	 * @var null|\cli\progress\Bar
	 */
	protected $progress_bar = null;

	/**
	 * Class constructor.
	 *
	 * @param Checker $checker Checker instance.
	 */
	public function __construct( Checker $checker ) {
		// Handle progress bar ticks.
		$checker->add_post_check_callback( function() {
			if ( null === $this->progress_bar ) {
				return;
			}

			$this->progress_bar->tick();
		} );

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
	 * : Comma separated list of fields to show. Valid fields include package_slug, package_type, package_version, id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 * ---
	 * default: package_type,package_slug,title,vuln_type,fixed_in
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
			// May need to revisit - $version is cast to string so null becomes ''.
			$plugin = new Package( $slug, Package::TYPE_PLUGIN, $version );

			$vulnerabilities = $this->checker->check_package( $plugin );

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
	 * : Comma separated list of fields to show. Valid fields include package_slug, package_type, package_version, id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 * ---
	 * default: package_type,package_slug,title,vuln_type,fixed_in
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
			$this->start_progress_bar( $assoc_args, $this->checker->get_plugin_count() );
			$ignored = $this->get_ignored_slugs( $assoc_args );

			$vulnerabilities = $this->checker->check_plugins( $ignored );

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
	 * : Comma separated list of fields to show. Valid fields include package_slug, package_type, package_version, id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 * ---
	 * default: package_type,package_slug,title,vuln_type,fixed_in
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
			$theme = new Package( $slug, Package::TYPE_THEME, $version );

			$vulnerabilities = $this->checker->check_package( $theme );

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
	 * : Comma separated list of fields to show. Valid fields include package_slug, package_type, package_version, id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 * ---
	 * default: package_type,package_slug,title,vuln_type,fixed_in
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
			$this->start_progress_bar( $assoc_args, $this->checker->get_theme_count() );
			$ignored = $this->get_ignored_slugs( $assoc_args );

			$vulnerabilities = $this->checker->check_themes( $ignored );

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
	 * : Comma separated list of fields to show. Valid fields include package_slug, package_type, package_version, id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 * ---
	 * default: package_type,package_slug,title,vuln_type,fixed_in
	 *
	 * @alias check-wp
	 * @subcommand check-wordpress
	 *
	 * @param  array $args       Positional args.
	 * @param  array $assoc_args Associative args.
	 */
	public function check_wordpress( array $args, array $assoc_args ) {
		try {
			$version = $args[0];
			$slug = str_replace( '.', '', $version );
			$wordpress = new Package( $slug, Package::TYPE_WORDPRESS, $args[0] );

			$vulnerabilities = $this->checker->check_package( $wordpress );

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
	 * : Comma separated list of fields to show. Valid fields include package_slug, package_type, package_version, id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 * ---
	 * default: package_type,package_slug,title,vuln_type,fixed_in
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
			$this->start_progress_bar( $assoc_args, $this->checker->get_wordpress_count() );
			$ignored = $this->get_ignored_slugs( $assoc_args );

			$vulnerabilities = $this->checker->check_wordpress( $ignored );

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
	 * : Comma separated list of fields to show. Valid fields include package_slug, package_type, package_version, id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 * ---
	 * default: package_type,package_slug,title,vuln_type,fixed_in
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
			$this->start_progress_bar( $assoc_args, $this->checker->get_package_count() );
			$ignored = $this->get_ignored_slugs( $assoc_args );

			$vulnerabilities = $this->checker->check_site( $ignored );

			$this->finish_progress_bar();
			$this->display_results( $vulnerabilities, $assoc_args );
		} catch ( \RuntimeException $e ) {
			WP_CLI::error( $e->getMessage() );
		}
	}

	/**
	 * Display the results of an individual check.
	 *
	 * @param  Vulnerabilities $vulnerabilities List of vulnerabilities.
	 * @param  array           $assoc_args      Associative args.
	 */
	protected function display_results( Vulnerabilities $vulnerabilities, array $assoc_args ) {
		// Get format and fields. WP-CLI handles validation for us.
		$format = WP_CLI\Utils\get_flag_value( $assoc_args, 'format' );
		$fields = WP_CLI\Utils\get_flag_value( $assoc_args, 'fields' );

		if ( 'table' === $format && $vulnerabilities->is_empty() ) {
			WP_CLI::success( 'No vulnerabilities found!' );
			exit( 0 );
		}

		// Convert vulns to arrays.
		$for_display = $vulnerabilities->each( function( $vulnerability ) {
			$package = $vulnerability->get_package();

			return array_merge( [
				'package_slug' => $package->get_slug(),
				'package_type' => $package->get_type(),
				'package_version' => $package->get_version(),
			], $vulnerability->get_data() );
		} );

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
							$vuln[ $timestamp ] = $vuln[ $timestamp ]->format( 'd F Y' );
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
		} // End switch().

		WP_CLI\Utils\format_items( $format, $for_display, $fields );
	}

	/**
	 * Create and prepare a progress bar instance if appropriate for a command.
	 *
	 * @param  array   $assoc_args    Associative args received by the command.
	 * @param  integer $package_count Count of packages being checked by the command.
	 *
	 * @return void
	 */
	protected function start_progress_bar( array $assoc_args, $package_count ) {
		if ( ! is_null( $this->progress_bar ) ) {
			// @todo
			WP_CLI::error( 'Too much progress for one request!' );
		}

		if ( isset( $assoc_args['format'] ) && 'table' !== $assoc_args['format'] ) {
			// We don't need a progress bar for machine readable formats.
			return;
		}

		$this->progress_bar = WP_CLI\Utils\make_progress_bar(
			sprintf( 'Checking %s packages', $package_count ),
			$package_count
		);
		$progress_bar = $this->progress_bar;
	}

	/**
	 * Finish and unset the progress bar instance.
	 *
	 * @return void
	 */
	protected function finish_progress_bar() {
		if ( is_null( $this->progress_bar ) ) {
			return;
		}

		$this->progress_bar->finish();
		$this->progress_bar = null;
	}

	/**
	 * Get the list of ignored package slugs from the current command call.
	 *
	 * @param  array $assoc_args Associative args received by the command.
	 *
	 * @return string[]
	 */
	protected function get_ignored_slugs( array $assoc_args ) {
		$ignored = WP_CLI\Utils\get_flag_value( $assoc_args, 'ignore' );

		if ( ! $ignored ) {
			return [];
		}

		return array_map( 'trim', explode( ',', $ignored ) );
	}
}
