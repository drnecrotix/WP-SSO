=== WP-SSO Bridge for IPS ===
Contributors: drnecrotix
Tags: sso, ips, invision, authentication, wordpress
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: MIT
License URI: https://opensource.org/licenses/MIT

WordPress-side SSO bridge for IPS / Invision Community.

== Description ==

WP-SSO Bridge exposes an authenticated WordPress endpoint that can be used by an IPS / Invision Community integration to validate the active WordPress login, retrieve account data and roles, and generate login, registration, and logout URLs.

The preferred authentication methods are the `X-WP-SSO-Key` request header or an `Authorization: Bearer` token. Legacy `api_key` query-string authentication is temporarily supported for compatibility with older IPS integrations.

== Installation ==

1. Upload the `wp-sso` directory to `/wp-content/plugins/`, or upload the packaged ZIP from WordPress Admin > Plugins > Add Plugin > Upload Plugin.
2. Activate **WP-SSO Bridge for IPS**.
3. Open Settings > WP-SSO Bridge.
4. Copy the generated API secret or replace it with your own long random secret.
5. Configure the IPS integration to use the displayed endpoint and matching secret.
6. Prefer `X-WP-SSO-Key` or Bearer authentication when your IPS-side integration supports request headers.

You may also define the secret outside the database with either the `WP_SSO_API_KEY` environment variable or a `WP_SSO_API_KEY` PHP constant. These values take precedence over the saved WordPress option.

== Endpoint ==

The plugin endpoint is based on your WordPress home URL:

`https://example.com/?wp_sso_api=1&type=test`

Supported `type` values are:

* `userinfo`
* `roles`
* `login`
* `register`
* `logout`
* `test`

== Security ==

Use HTTPS and a long random secret. Do not publish production credentials. Query-string API key authentication is deprecated because URLs can be captured in logs and browser history.

== Changelog ==

= 1.0.0 =
* Added installable WordPress plugin packaging.
* Added WordPress Settings page and generated API secret.
* Added header and Bearer authentication.
* Retained temporary legacy query-string authentication compatibility.
