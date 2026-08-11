=== WP-SSO Bridge for IPS ===
Contributors: drnecrotix
Tags: sso, ips, invision, authentication, wordpress
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.2.0
License: MIT
License URI: https://opensource.org/licenses/MIT

WordPress-side SSO bridge for IPS / Invision Community.

== Description ==

WP-SSO Bridge exposes an authenticated WordPress endpoint that can be used by an IPS / Invision Community integration to validate the active WordPress login, retrieve account data and roles, and generate login, registration, and logout URLs.

The preferred authentication methods are the `X-WP-SSO-Key` request header or an `Authorization: Bearer` token. Legacy `api_key` query-string authentication is temporarily supported for compatibility with older IPS integrations.

Version 1.1.0 adds a guided setup screen and an optional API compatibility-file generator for IPS integrations that require a physical PHP endpoint file.

== Installation ==

1. Upload the `wp-sso` directory to `/wp-content/plugins/`, or upload the packaged ZIP from WordPress Admin > Plugins > Add Plugin > Upload Plugin.
2. Activate **WP-SSO Bridge for IPS**.
3. Open Settings > WP-SSO Bridge.
4. Save the generated API secret or replace it with your own long random secret.
5. Use the native WordPress endpoint shown by the plugin whenever possible.
6. If your IPS integration requires a physical PHP endpoint file, click **Generate & Download API file**.
7. Upload the generated `wp-sso-api.php` to the WordPress root directory, next to `wp-config.php` and `wp-load.php`.
8. Configure IPS with the selected endpoint and the same API secret.
9. Prefer `X-WP-SSO-Key` or Bearer authentication when your IPS-side integration supports request headers.

You may also define the secret outside the database with either the `WP_SSO_API_KEY` environment variable or a `WP_SSO_API_KEY` PHP constant. These values take precedence over the saved WordPress option.

== Endpoint ==

The recommended native plugin endpoint is based on your WordPress home URL:

`https://example.com/?wp_sso_api=1&type=test`

If the generated compatibility file is installed in the WordPress root, its endpoint is:

`https://example.com/wp-sso-api.php?type=test`

The generated file contains no API secret. It loads WordPress from `wp-load.php` and forwards the request to the installed WP-SSO Bridge plugin.

Supported `type` values are:

* `userinfo`
* `roles`
* `login`
* `register`
* `logout`
* `test`

== API compatibility file ==

The API file is optional. Use it only when your IPS integration expects a physical PHP endpoint.

After generating `wp-sso-api.php`, upload it to the WordPress root directory. This is the directory containing:

* `wp-config.php`
* `wp-load.php`
* `wp-admin/`
* `wp-content/`
* `wp-includes/`

Do not place the generated file inside the plugin directory, a theme directory, or the uploads directory.

== Security ==

Use HTTPS and a long random secret. The generated compatibility file intentionally contains no secret. Do not publish production credentials. Query-string API key authentication is deprecated because URLs can be captured in logs and browser history.

== Changelog ==

= 1.1.0 =
* Added step-by-step usage instructions to the WordPress settings screen.
* Added an optional `wp-sso-api.php` compatibility-file generator.
* Added explicit instructions showing where the generated API file must be uploaded.
* Kept API credentials out of the generated file.

= 1.0.0 =
* Added installable WordPress plugin packaging.
* Added WordPress Settings page and generated API secret.
* Added header and Bearer authentication.
* Retained temporary legacy query-string authentication compatibility.
