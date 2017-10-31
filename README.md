ssnepenthe/soter-command
========================

Easily check your plugins, themes and core against the WPScan API from the command line.

## Installation

Installing this package requires WP-CLI v1.1 or greater. Update to the latest stable release with `wp cli update`.

Once you've done so, you can install this package with:

    wp package install git@github.com:ssnepenthe/soter-command.git

## Usage

The following commands are available:

```
wp soter check-plugin <slug> [<version>] [--format=<format>] [--fields=<fields>]
wp soter check-plugins [--format=<format>] [--fields=<fields>] [--ignore=<ignore>]

wp soter check-site [--format=<format>] [--fields=<fields>] [--ignore=<ignore>]

wp soter check-theme <slug> [<version>] [--format=<format>] [--fields=<fields>]
wp soter check-themes [--format=<format>] [--fields=<fields>] [--ignore=<ignore>]

wp soter check-wordpress <version> [--format=<format>] [--fields=<fields>]
wp soter check-wordpresses [--format=<format>] [--fields=<fields>] [--ignore=<ignore>]
```

`<slug>` is the plugin or theme slug.

`<version>` is the version string you wish to check.

`<format>` can be any of `count`, `csv`, `ids`, `json`, `table` or `yaml` - default is `table`. If set to `ids`, it will print a space-separated list of vulnerability IDs as given by the WPScan API.

`<fields>` should be a comma-separated list of fields. Valid fields are `package_slug`, `package_type`, `package_version`, `id`, `title`, `created_at`, `updated_at`, `published_date`, `vuln_type`, `fixed_in` - default is `package_type,package_slug,title,vuln_type,fixed_in`.

`<ignore>` should be a comma-separated list of installed package slugs that should not be checked.

## Examples

**Full site check formatted as a table**

```
vagrant@vvv:/srv/www/wordpress-default/public_html$ wp soter check-site

Checking 24 packages  100% [==============================================================================] 0:00 / 0:00
+--------------+----------------+---------------------------------------------------------------+------------+---------------+
| package_type | package_slug   | title                                                         | vuln_type  | fixed_in      |
+--------------+----------------+---------------------------------------------------------------+------------+---------------+
| plugin       | contact-form-7 | Contact Form 7 <= 3.7.1 - Security Bypass                     | AUTHBYPASS | 3.7.2         |
| plugin       | contact-form-7 | Contact Form 7 <= 3.5.2 - File Upload Remote Code Execution   | UPLOAD     | 3.5.3         |
| theme        | twentyfifteen  | Twenty Fifteen Theme <= 1.1 - DOM Cross-Site Scripting (XSS)  | XSS        | 1.2           |
| wordpress    | 475            | WordPress 2.3-4.7.5 - Host Header Injection in Password Reset | UNKNOWN    | NOT FIXED YET |
+--------------+----------------+---------------------------------------------------------------+------------+---------------+
```

**Plugin check: All versions of Contact Form 7 formatted as CSV**

```
vagrant@vvv:/srv/www/wordpress-default/public_html$ wp soter check-plugin contact-form-7 --format=csv
package_type,package_slug,title,vuln_type,fixed_in
plugin,contact-form-7,"Contact Form 7 <= 3.7.1 - Security Bypass",AUTHBYPASS,3.7.2
plugin,contact-form-7,"Contact Form 7 <= 3.5.2 - File Upload Remote Code Execution",UPLOAD,3.5.3
```

**Theme check: Version 1.1 of twentyfifteen, formatted as JSON, display title, vulnerability type and fixed in version**

```
vagrant@vvv:/srv/www/wordpress-default/public_html$ wp soter check-theme twentyfifteen 1.1 --format=json --fields=title,vuln_type,fixed_in
[{"title":"Twenty Fifteen Theme <= 1.1 - DOM Cross-Site Scripting (XSS)","vuln_type":"XSS","fixed_in":"1.2"}]
```

**WordPress check: Version 4.7.5, format as YAML, display id, title and fixed in version**

```
vagrant@vvv:/srv/www/wordpress-default/public_html$ wp soter check-wordpress 4.7.5 --format=yaml --fields=id,title,fixed_in
---
-
  id: 8807
  title: 'WordPress 2.3-4.7.5 - Host Header Injection in Password Reset'
  fixed_in: null
```

## Extending

A number of actions are available which allow plugins to implement custom behavior in response to individual checks (logging, notifications, etc.).

`soter_command_package_check_complete`: This action is triggered after every individual package has been checked. Callbacks receive a `Soter_Core\Vulnerabilities` object as the first param and a `Soter_Core\Response` object as the second.

`soter_command_{$command}_results`: This action is triggered within each command before the results are displayed. `$command` is the name of the command (check-plugin, check-themes, etc.) with `-` replaced with `_` (e.g. `soter_command_check_site_results`). Callbacks receive a `Soter_Core\Vulnerabilities` object.

## API Errors

It is recommended to occasionally run a full site check with `--debug=soter-command`.

This will notify you if you have received any error responses from the API:

```
vagrant@vvv:/srv/www/wordpress-default/public_html$ wp soter check-site --debug=soter-command
Checking 28 packages  3  % [===>                                                                                              ] 0:00 / 0:00
Debug: Error checking plugin recaptcha-for-wp: Non-200 status code received [HTTP 404] (1.279s)
Debug: Error checking plugin terms-archive: Non-200 status code received [HTTP 404] (1.282s)
Debug: Error checking plugin wp-hashids: Non-200 status code received [HTTP 404] (1.283s)
Debug: Error checking theme tf-child: Non-200 status code received [HTTP 404] (1.285s)
Checking 28 packages  100% [==================================================================================================] 0:00 / 0:00
+--------------+--------------+---------------------------------------------------------------+-----------+---------------+
| package_type | package_slug | title                                                         | vuln_type | fixed_in      |
+--------------+--------------+---------------------------------------------------------------+-----------+---------------+
| wordpress    | 482          | WordPress 2.3-4.8.2 - Host Header Injection in Password Reset | UNKNOWN   | NOT FIXED YET |
+--------------+--------------+---------------------------------------------------------------+-----------+---------------+
```

Possible errors include a non-200 status code, a non-JSON response body, an invalid JSON response body and a response that does not match the requested package.

Non-200 status codes tend to pop up for (but are not limited to) custom plugins and themes. When that is the case, they should be ignored via the `ignore` option via either the command line (e.g. `--ignore=comma,separated,slugs`) or by overriding the command defaults in `wp-cli.yml`.

It is unlikely that you will ever see any of the other errors, but if you do, please report them to the [WPScan team](https://wpvulndb.com/contact).
