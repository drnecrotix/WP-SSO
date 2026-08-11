# WP-SSO

WordPress ↔ IPS / Invision Community single sign-on bridge.

[![PHP Lint](https://github.com/drnecrotix/WP-SSO/actions/workflows/php-lint.yml/badge.svg)](https://github.com/drnecrotix/WP-SSO/actions/workflows/php-lint.yml)
[![CodeQL](https://github.com/drnecrotix/WP-SSO/actions/workflows/codeql.yml/badge.svg)](https://github.com/drnecrotix/WP-SSO/actions/workflows/codeql.yml)
[![WordPress Plugin Package](https://github.com/drnecrotix/WP-SSO/actions/workflows/package-plugin.yml/badge.svg)](https://github.com/drnecrotix/WP-SSO/actions/workflows/package-plugin.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

WP-SSO lets an IPS / Invision Community installation use an existing WordPress authentication session and account data. The project now includes a standard installable WordPress plugin, the original legacy endpoint for migration compatibility, and the IPS plugin definition.

> **Project status:** active modernization of a legacy integration. Test against your exact WordPress and IPS versions before production deployment.

## ✨ Features

- installable WordPress plugin under `wp-sso/`;
- Settings → **WP-SSO Bridge** configuration page;
- automatically generated API secret on activation;
- API secret from WordPress settings, `WP_SSO_API_KEY` environment variable, or PHP constant;
- `X-WP-SSO-Key` authentication;
- `Authorization: Bearer` authentication;
- temporary legacy `api_key` query-string compatibility;
- WordPress login-cookie validation;
- authenticated user ID, display name, email, and roles;
- WordPress role discovery;
- login, registration, and logout URL generation;
- PHP syntax CI, CodeQL for Actions, Dependabot, and automated plugin ZIP packaging.

## 📁 Repository layout

```text
.
├── wp-sso/
│   ├── wp-sso.php              # Installable WordPress plugin
│   └── readme.txt              # WordPress plugin metadata/instructions
├── wp_api.php                  # Legacy standalone endpoint
├── WordPress SSO.xml           # IPS / Invision Community plugin definition
├── .github/workflows/          # CI, CodeQL and packaging automation
├── SECURITY.md                 # Security policy
├── LICENSE                     # MIT license
└── README.md
```

## 🚀 Recommended installation

### 1. Download the plugin package

Run or download the artifact from the **Package WordPress Plugin** GitHub Actions workflow. The generated file is:

```text
wp-sso.zip
```

You can also create the ZIP yourself by archiving the `wp-sso/` directory while keeping `wp-sso` as the top-level folder.

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

### 4. Install the IPS integration

Import `WordPress SSO.xml` from the IPS / Invision Community plugin administration area and configure it with the WordPress endpoint and matching secret.

> The bundled IPS definition is legacy and may still depend on query-string authentication. Header-based authentication is preferred for modern integrations.

## 🔌 Plugin endpoint

The standard plugin endpoint is:

```text
https://example.com/?wp_sso_api=1&type=test
```

Supported `type` values:

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

New installations should use the standard WordPress plugin. Existing installations can migrate gradually by enabling `wp-sso`, configuring the same secret, changing the IPS endpoint, verifying `type=test`, and then removing the standalone file after successful validation.

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
- automated `wp-sso.zip` packaging as a GitHub Actions artifact.

## ⚠️ Compatibility notes

- The WordPress plugin requires PHP 7.4+.
- The legacy IPS XML has not yet been fully rewritten around header-only authentication.
- `userinfo` depends on the browser request carrying a valid WordPress logged-in cookie.
- Full integration tests against current WordPress and IPS releases are still planned.

## 🗺️ Roadmap

- modernize the IPS-side integration to send header authentication;
- add automated endpoint/authentication tests with a WordPress test environment;
- add tagged GitHub Releases containing `wp-sso.zip` and the IPS XML;
- document verified WordPress and IPS compatibility versions;
- remove query-string API-key support in a future breaking release.

## 🤝 Contributing

Bug reports, compatibility findings, documentation improvements, and security-conscious refactors are welcome. Keep pull requests focused and include the WordPress, IPS, and PHP versions used during testing.

## 📄 License

WP-SSO is released under the [MIT License](LICENSE).
