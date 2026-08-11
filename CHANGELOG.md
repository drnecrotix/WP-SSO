# Changelog

All notable changes to WP-SSO are documented here.

The project follows semantic versioning for packaged releases.

## [Unreleased]

### Planned

- Modernize the IPS-side request layer to use header-based authentication.
- Add automated WordPress endpoint and authentication tests.
- Expand verified WordPress / IPS compatibility documentation.

## [1.0.0] - 2026-08-11

### Added

- Standard installable WordPress plugin package under `wp-sso/`.
- WordPress **Settings → WP-SSO Bridge** administration page.
- Automatically generated API secret on first activation.
- `X-WP-SSO-Key` and `Authorization: Bearer` authentication support.
- Optional `WP_SSO_API_KEY` environment variable and PHP constant configuration.
- Automated `wp-sso.zip` packaging.
- GitHub Release automation with WordPress and IPS assets.
- PHP syntax CI, Dependabot, CodeQL, MIT license, and security policy.

### Changed

- New deployments now use the packaged WordPress plugin instead of manually copying `wp_api.php` into the WordPress root.
- Documentation was rewritten around plugin installation and migration.

### Deprecated

- Query-string `api_key` authentication. It remains temporarily available for legacy IPS compatibility.

### Compatibility

- The standalone `wp_api.php` endpoint remains available for gradual migration of existing installations.

[Unreleased]: https://github.com/drnecrotix/WP-SSO/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/drnecrotix/WP-SSO/releases/tag/v1.0.0