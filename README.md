# WP-SSO

WordPress ↔ IPS / Invision Community single sign-on bridge.

[![Latest Release](https://img.shields.io/github/v/release/drnecrotix/WP-SSO?display_name=tag&sort=semver)](https://github.com/drnecrotix/WP-SSO/releases/latest)
[![PHP Lint](https://github.com/drnecrotix/WP-SSO/actions/workflows/php-lint.yml/badge.svg)](https://github.com/drnecrotix/WP-SSO/actions/workflows/php-lint.yml)
[![CodeQL](https://github.com/drnecrotix/WP-SSO/actions/workflows/codeql.yml/badge.svg)](https://github.com/drnecrotix/WP-SSO/actions/workflows/codeql.yml)
[![WordPress Plugin Package](https://github.com/drnecrotix/WP-SSO/actions/workflows/package-plugin.yml/badge.svg)](https://github.com/drnecrotix/WP-SSO/actions/workflows/package-plugin.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

[![Download WP Plugin](https://img.shields.io/badge/Download-WP--SSO%20Plugin-blue?logo=wordpress&logoColor=white)](https://github.com/drnecrotix/WP-SSO/releases/latest/download/wp-sso.zip)
[![Download IPS Integration](https://img.shields.io/badge/Download-IPS%20Integration-5C2D91)](https://github.com/drnecrotix/WP-SSO/releases/latest/download/WordPress-SSO-IPS.xml)

WP-SSO lets an IPS / Invision Community installation use an existing WordPress authentication session and account data. The project includes a standard installable WordPress plugin, an optional generated compatibility API file, the original legacy endpoint for migration compatibility, and the IPS plugin definition.

> **Project status:** active modernization of a legacy integration. Test against your exact WordPress and IPS versions before production deployment.

## ✨ Features

- installable WordPress plugin under `wp-sso/`;
- guided setup under Settings → **WP-SSO Bridge**;
- automatically generated API secret on activation;
- optional **Generate & Download API file** tool for legacy IPS integrations;
- generated API compatibility file contains no API secret;
- API secret from WordPress settings, `WP_SSO_API_KEY` environment variable, or PHP constant;
- `X-WP-SSO-Key` authentication;
- `Authorization: Bearer` authentication;
- temporary legacy `api_key` query-string compatibility;
- WordPress login-cookie validation;
- authenticated user ID, display name, email, and roles;
- WordPress role discovery;
- login, registration, and logout URL generation;
- PHP syntax CI, CodeQL for Actions, Dependabot, automated plugin ZIP packaging, and tagged GitHub Releases.

## 📁 Repository layout

```text
.
├── wp-sso/
│   ├── wp-sso.php              # Installable WordPress plugin
│   └── readme.txt              # WordPress plugin metadata/instructions
├── wp_api.php                  # Legacy standalone endpoint
├── WordPress SSO.xml           # IPS / Invision Community plugin definition
├── .github/workflows/          # CI, CodeQL, packaging and release automation
├── SECURITY.md                 # Security policy
├── LICENSE                     # MIT license
└── README.md
```

## 🚀 Recommended installation

### 1. Download the latest release

Use the permanent latest-release links:

- **WordPress plugin:** [Download `wp-sso.zip`](https://github.com/drnecrotix/WP-SSO/releases/latest/download/wp-sso.zip)
- **IPS integration:** [Download `WordPress-SSO-IPS.xml`](https://github.com/drnecrotix/WP-SSO/releases/latest/download/WordPress-SSO-IPS.xml)
- **Release notes:** [View the latest GitHub Release](https://github.com/drnecrotix/WP-SSO/releases/latest)

The `wp-sso.zip` archive is ready to upload directly to WordPress.

### 2. Install in WordPress

Open:

**WordPress Admin → Plugins → Add Plugin → Upload Plugin**

Upload `wp-sso.zip`, install it, and activate **WP-SSO Bridge for IPS**.

### 3. Configure the bridge

Open:

**Settings → WP-SSO Bridge**

The plugin creates a random API secret on first activation. You may replace it with another long random secret.

For infrastructure-managed secrets, use either:

```text
WP_SSO_API_KEY=your-long-random-secret
```

or in `wp-config.php`:

```php
define('WP_SSO_API_KEY', 'your-long-random-secret');
```

Environment/constant values take precedence over the database setting.

### 4. Choose the endpoint

The recommended native endpoint does not require any additional PHP file:

```text
https://example.com/?wp_sso_api=1&type=test
```

If the IPS integration expects a physical PHP endpoint file, use **Settings → WP-SSO Bridge → Generate & Download API file**.

The plugin generates:

```text
wp-sso-api.php
```

The generated file contains **no API secret**. It only loads WordPress and passes the request to the installed WP-SSO plugin.

### 5. Upload the generated API file

Upload `wp-sso-api.php` to the **WordPress root directory**. This is the same directory that contains:

```text
wp-config.php
wp-load.php
wp-admin/
wp-content/
wp-includes/
```

Do **not** place it inside `wp-content/plugins/`, your theme directory, or the uploads directory.

After upload, the compatibility endpoint will look like:

```text
https://example.com/wp-sso-api.php?type=test
```

### 6. Install the IPS integration

Import `WordPress-SSO-IPS.xml` from the IPS / Invision Community plugin administration area and configure it with either the native plugin endpoint or the generated compatibility-file endpoint, plus the matching secret.

> The bundled IPS definition is legacy and may still depend on query-string authentication. Header-based authentication is preferred for modern integrations.

## 🔌 Supported endpoint types

| Type | Purpose |
| --- | --- |
| `userinfo` | Validate the current WordPress login cookie and return user information |
| `roles` | Return WordPress role names |
| `login` | Return a WordPress login URL |
| `register` | Return a WordPress registration URL |
| `logout` | Return a WordPress logout URL |
| `test` | Return plain-text `OK` |

## 🔐 Authentication

Preferred header authentication:

```bash
curl -H "X-WP-SSO-Key: YOUR_SECRET" \
  "https://example.com/?wp_sso_api=1&type=test"
```

Bearer authentication:

```bash
curl -H "Authorization: Bearer YOUR_SECRET" \
  "https://example.com/?wp_sso_api=1&type=test"
```

Legacy query-string compatibility:

```text
https://example.com/?wp_sso_api=1&api_key=YOUR_SECRET&type=test
```

The legacy query-string form is deprecated because URLs can appear in access logs, browser history, reverse-proxy logs, and monitoring systems.

## 🧭 Legacy standalone endpoint

`wp_api.php` remains in the repository for existing installations that copied the endpoint into the WordPress root.

New installations should use the standard WordPress plugin. If a physical PHP endpoint is still required, use the new generated `wp-sso-api.php` bootstrap instead of copying a secret-bearing legacy file.

## 🍪 Shared cookie domain

If WordPress and IPS need browser cookies across subdomains of the same parent domain, configure WordPress cookies carefully in `wp-config.php` when required by your deployment:

```php
define('COOKIE_DOMAIN', '.example.com');
```

Do not define the constant twice, and do not widen the cookie domain unless your SSO architecture actually requires it.

## 🛡️ Security

The maintained implementation includes:

- constant-time API-secret comparison with `hash_equals()`;
- header/Bearer authentication;
- generated secrets instead of a committed default credential;
- generated compatibility API files with no embedded secret;
- nonce and administrator-capability checks before API-file generation;
- request-type allowlisting;
- HTTP/HTTPS redirect sanitization;
- no-cache API responses;
- `X-Content-Type-Options: nosniff`;
- explicit authorization for WordPress settings management.

Use HTTPS for both applications, keep WordPress/IPS/PHP current, and never commit real production credentials.

See [SECURITY.md](SECURITY.md) for vulnerability reporting guidance.

## ✅ Automation

The repository includes:

- PHP syntax validation across PHP 7.4, 8.1, 8.2 and 8.3;
- CodeQL analysis for GitHub Actions;
- Dependabot for GitHub Actions dependencies;
- automated `wp-sso.zip` packaging as a GitHub Actions artifact;
- automated tagged GitHub Releases containing `wp-sso.zip` and the IPS XML.

## ⚠️ Compatibility notes

- The WordPress plugin requires PHP 7.4+.
- The generated API compatibility file requires the plugin to remain installed and active.
- The generated API file must be in the same directory as `wp-load.php`.
- The legacy IPS XML has not yet been fully rewritten around header-only authentication.
- `userinfo` depends on the browser request carrying a valid WordPress logged-in cookie.
- Full integration tests against current WordPress and IPS releases are still planned.

## 🗺️ Roadmap

- modernize the IPS-side integration to send header authentication;
- add automated endpoint/authentication tests with a WordPress test environment;
- document verified WordPress and IPS compatibility versions;
- remove query-string API-key support in a future breaking release.

## 🤝 Contributing

Bug reports, compatibility findings, documentation improvements, and security-conscious refactors are welcome. Keep pull requests focused and include the WordPress, IPS, and PHP versions used during testing.

## 📄 License

WP-SSO is released under the [MIT License](LICENSE).
