<?php

namespace Soter_Command;

use WP_CLI;
use Soter_Core\Checker_Interface;

class Soter_Command {
	protected $checker;

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
	 * default: standard
	 * options:
	 *   - count
	 *   - csv
	 *   - ids
	 *   - json
	 *   - standard
	 *   - table
	 *   - yaml
	 *   - yml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 *
	 * @subcommand check-plugin
	 *
	 * @param  array $args       Positional args.
	 * @param  array $assoc_args Associative args.
	 */
	public function check_plugin( $args, $assoc_args ) {
		try {
			$plugin = $args[0];
			$version = isset( $args[1] ) ? $args[1] : null;

			$response = $this->checker->get_client()->plugins( $plugin );
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
	 * default: standard
	 * options:
	 *   - count
	 *   - csv
	 *   - ids
	 *   - json
	 *   - standard
	 *   - table
	 *   - yaml
	 *   - yml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 *
	 * @subcommand check-plugins
	 *
	 * @param  array $_          Unused positional args.
	 * @param  array $assoc_args Associative args.
	 */
	public function check_plugins( $_, $assoc_args ) {
		try {
			$this->start_progress_bar(
				$assoc_args,
				$this->checker->get_plugin_count()
			);

			$vulnerabilities = $this->checker->check_plugins();

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
	 * default: standard
	 * options:
	 *   - count
	 *   - csv
	 *   - ids
	 *   - json
	 *   - standard
	 *   - table
	 *   - yaml
	 *   - yml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 *
	 * @subcommand check-theme
	 *
	 * @param  array $args       Positional args.
	 * @param  array $assoc_args Associative args.
	 */
	public function check_theme( $args, $assoc_args ) {
		try {
			$theme = $args[0];
			$version = isset( $args[1] ) ? $args[1] : null;

			$response = $this->checker->get_client()->themes( $theme );
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
	 * default: standard
	 * options:
	 *   - count
	 *   - csv
	 *   - ids
	 *   - json
	 *   - standard
	 *   - table
	 *   - yaml
	 *   - yml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 *
	 * @subcommand check-themes
	 *
	 * @param  array $_          Unused positional args.
	 * @param  array $assoc_args Associative args.
	 */
	public function check_themes( $_, $assoc_args ) {
		try {
			$this->start_progress_bar(
				$assoc_args,
				$this->checker->get_theme_count()
			);

			$vulnerabilities = $this->checker->check_themes();

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
	 * default: standard
	 * options:
	 *   - count
	 *   - csv
	 *   - ids
	 *   - json
	 *   - standard
	 *   - table
	 *   - yaml
	 *   - yml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 *
	 * @alias check-wp
	 * @subcommand check-wordpress
	 *
	 * @param  array $args       Positional args.
	 * @param  array $assoc_args Associative args.
	 */
	public function check_wordpress( $args, $assoc_args ) {
		$slug = str_replace( '.', '', $args[0] );

		try {
			$response = $this->checker->get_client()->wordpresses( $slug );
			$vulnerabilities = $response->get_vulnerabilities_by_version();

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
	 * default: standard
	 * options:
	 *   - count
	 *   - csv
	 *   - ids
	 *   - json
	 *   - standard
	 *   - table
	 *   - yaml
	 *   - yml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
	 *
	 * @subcommand check-wordpresses
	 *
	 * @param  array $_          Unused positional args.
	 * @param  array $assoc_args Associative args.
	 */
	public function check_wordpresses( $_, $assoc_args ) {
		try {
			$this->start_progress_bar(
				$assoc_args,
				$this->checker->get_wordpress_count()
			);

			$vulnerabilities = $this->checker->check_wordpress();

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
	 * default: standard
	 * options:
	 *   - count
	 *   - csv
	 *   - ids
	 *   - json
	 *   - standard
	 *   - table
	 *   - yaml
	 *   - yml
	 * ---
	 *
	 * [--fields=<fields>]
	 * : Comma separated list of fields to show. Valid fields include id, title, created_at, updated_at, published_date, vuln_type, fixed_in.
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

			$vulnerabilities = $this->checker->check_site();

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
		$format = isset( $assoc_args['format'] ) ?
			$assoc_args['format'] :
			'standard';

		if ( 'yml' === $format ) {
			$format = 'yaml';
		}

		$fields = isset( $assoc_args['fields'] ) ?
			$assoc_args['fields'] :
			'title,published_date,fixed_in';

		if ( 'standard' === $format ) {
			$this->display_standard_results( $vulnerabilities );
		} else {
			if ( 'ids' === $format ) {
				$vulnerabilities = array_map( function( $vuln ) {
					return $vuln->id;
				}, $vulnerabilities );
			} else {
				$vulnerabilities = array_map( function( $vuln ) {
					return $vuln->get_data();
				}, $vulnerabilities );
			}

			$fields = explode( ',', $fields );

			$allowed = [
				'id',
				'title',
				'created_at',
				'updated_at',
				'published_date',
				'vuln_type',
				'fixed_in',
			];

			$invalid = array_values( array_diff( $fields, $allowed ) );

			// Mimics WP-CLI default behavior for invalid params.
			if ( ! empty( $invalid ) ) {
				$message = 'Parameter errors:' . "\n";

				if ( 1 === count( $invalid ) ) {
					$message .= $invalid[0] . ' is not a valid fields value';
				} else {
					$message .= implode( ', ', $invalid ) . ' are not valid fields values';
				}

				WP_CLI::error( $message );
			}

			WP_CLI\Utils\format_items( $format, $vulnerabilities, $fields );
		}
	}

	/**
	 * Display the results when --format=standard.
	 *
	 * @param  Soter_Core\Vulnerability_Interface[] $vulnerabilities List of vulnerabilities.
	 */
	protected function display_standard_results( array $vulnerabilities ) {
		if ( empty( $vulnerabilities ) ) {
			WP_CLI::log( $this->success( $this->banner(
				'No vulnerabilities detected!',
				'SUCCESS'
			) ) );

			return;
		}

		$count = count( $vulnerabilities );

		WP_CLI::log( $this->warning( $this->banner( sprintf(
			'%s %s detected',
			$count,
			1 < $count ? 'vulnerabilities' : 'vulnerability'
		), 'WARNING' ) ) );

		foreach ( $vulnerabilities as $vulnerability ) {
			WP_CLI::log( $this->title( $vulnerability->title ) );

			if ( ! is_null( $vulnerability->published_date ) ) {
				WP_CLI::log( sprintf(
					'Published %s',
					$vulnerability->published_date->format( 'd F Y' )
				) );
			}

			if ( isset( $vulnerability->references->url ) ) {
				foreach ( $vulnerability->references->url as $url ) {
					WP_CLI::log( $url );
				}
			}

			WP_CLI::log( sprintf(
				'https://wpvulndb.com/vulnerabilities/%s',
				$vulnerability->id
			) );

			if ( is_null( $vulnerability->fixed_in ) ) {
				WP_CLI::log( $this->warning( 'Not fixed yet' ) );
			} else {
				WP_CLI::log( sprintf(
					'Fixed in v%s',
					$vulnerability->fixed_in
				) );
			}

			WP_CLI::log( '' );
		}
	}

	/**
	 * Creates a multi-line banner for given string.
	 *
	 * @param  string $text   String to bannerify.
	 * @param  string $prefix Text to prefix to $text.
	 *
	 * @return string
	 */
	protected function banner( $text, $prefix = null ) {
		if ( ! is_null( $prefix ) ) {
			$text = sprintf( '%s: %s', $prefix, $text );
		}

		return sprintf( "\n\n  %s\n", $text );
	}

	/**
	 * Colorize a string with a green background.
	 *
	 * @param  string $text Text to colorize.
	 *
	 * @return string
	 */
	protected function success( $text ) {
		return WP_CLI::colorize(
			'%2' . $text . '%n' . "\n"
		);
	}

	/**
	 * Colorize a string blue.
	 *
	 * @param  string $text Text to colorize.
	 *
	 * @return string
	 */
	protected function title( $text ) {
		return WP_CLI::colorize( '%B' . $text . '%n' );
	}

	/**
	 * Colorize a string with a red background.
	 *
	 * @param  string $text Text to colorize.
	 *
	 * @return string
	 */
	protected function warning( $text ) {
		return WP_CLI::colorize( '%1' . $text . '%n' . "\n" );
	}

	protected $progress_bar = null;
	protected $progress_ticker = null;

	protected function start_progress_bar( $assoc_args, $package_count ) {
		if (
			! is_null( $this->progress_bar )
			|| ! is_null( $this->progress_ticker )
		) {
			// @todo
			WP_CLI::error( 'Too much progress for one request!' );
		}

		if (
			isset( $assoc_args['format'] )
			&& 'standard' !== $assoc_args['format']
		) {
			return;
		}

		$this->progress_bar = $progress_bar = WP_CLI\Utils\make_progress_bar(
			sprintf( 'Checking %s packages', $package_count ),
			$package_count
		);

		$this->progress_ticker = function() use ( $progress_bar ) {
			$progress_bar->tick();
		};

		add_action( 'soter_core_check_package_complete', $this->progress_ticker );
	}

	protected function finish_progress_bar() {
		if ( is_null( $this->progress_bar ) || is_null( $this->progress_ticker ) ) {
			return;
		}

		// @todo Probably not necessary to remove action...
		remove_action( 'soter_core_check_package_complete', $this->progress_ticker );

		$this->progress_bar->finish();
	}
}
