# WP-SSO v1.0.0

First packaged release of the modernized WP-SSO bridge.

## Highlights

- Standard installable WordPress plugin package (`wp-sso.zip`).
- WordPress **Settings → WP-SSO Bridge** configuration page.
- Automatically generated API secret on first activation.
- Support for `X-WP-SSO-Key` and `Authorization: Bearer` authentication.
- Optional `WP_SSO_API_KEY` environment variable or PHP constant configuration.
- Legacy query-string `api_key` compatibility retained temporarily for older IPS integrations.
- Authenticated user, role, login, registration, logout, and connectivity endpoints.
- PHP syntax CI across PHP 7.4, 8.1, 8.2, and 8.3.
- Dependabot and CodeQL automation.
- MIT license and security policy.

## Release assets

- `wp-sso.zip` — install directly from **WordPress Admin → Plugins → Add Plugin → Upload Plugin**.
- `WordPress-SSO-IPS.xml` — IPS / Invision Community plugin definition for the bridge.

## Migration note

Existing installations that use the standalone `wp_api.php` endpoint can continue using it while migrating. New installations should use the packaged WordPress plugin.

## Security note

Prefer header or Bearer authentication. Query-string API keys are deprecated because URLs may be written to logs, browser history, proxies, or monitoring systems.
