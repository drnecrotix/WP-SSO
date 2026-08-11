# WP-SSO

WordPress ↔ IPS Community Suite single sign-on bridge.

WP-SSO provides a lightweight integration layer that lets an IPS Community Suite installation use an existing WordPress login session and account data. The repository contains a small WordPress-side API endpoint and an IPS plugin definition.

> **Project status:** legacy / maintenance. Review the security notes before deploying this integration on a production site.

## What it does

The WordPress endpoint can:

- verify the configured API key;
- validate the current WordPress logged-in cookie;
- return the authenticated WordPress user's ID, display name, email, and roles;
- return the available WordPress roles;
- generate WordPress login, registration, and logout URLs with redirects;
- expose a simple connectivity test endpoint.

The IPS plugin can then use that endpoint as the bridge between the two applications.

## Repository contents

```text
.
├── wp_api.php          # WordPress-side API endpoint
├── WordPress SSO.xml   # IPS Community Suite plugin definition
├── README.md           # Project documentation
├── SECURITY.md         # Security policy and reporting guidance
└── .gitignore
```

## Requirements

- WordPress
- IPS / Invision Community installed on the same environment or otherwise able to reach the WordPress endpoint
- PHP 7.4 or newer
- access to the WordPress filesystem and IPS plugin administration

For new deployments, prefer a currently supported PHP release and test the integration against the exact WordPress and IPS versions you use.

## Installation

### 1. Install the WordPress endpoint

Copy `wp_api.php` into the WordPress installation root, next to `wp-load.php`.

The preferred configuration is to provide the secret through an environment variable:

```text
WP_SSO_API_KEY=your-long-random-secret
```

For legacy environments where an environment variable is not practical, the placeholder near the top of `wp_api.php` can still be replaced manually. Never commit the real secret to the repository.

### 2. Configure the shared cookie domain when required

If WordPress and IPS are installed on different subdomains of the same parent domain, configure WordPress cookies appropriately in `wp-config.php`.

Example:

```php
define( 'COOKIE_DOMAIN', '.example.com' );
```

Do not define `COOKIE_DOMAIN` twice. Use the value that matches your actual deployment.

### 3. Install the IPS plugin

Import `WordPress SSO.xml` manually from the IPS plugin administration area.

Open the WP-SSO plugin settings and configure the WordPress API endpoint and the same API secret used by `wp_api.php`.

### 4. Configure login flow

If desired, point the IPS login flow to the WordPress login page so authentication happens through WordPress first.

## Endpoint behavior

The WordPress endpoint supports these `type` values:

| Type | Purpose |
| --- | --- |
| `userinfo` | Validate the current WordPress login cookie and return user information |
| `roles` | Return WordPress role names |
| `login` | Return a WordPress login URL |
| `register` | Return a WordPress registration URL |
| `logout` | Return a WordPress logout URL |
| `test` | Return `OK` to confirm connectivity |

### Authentication

New clients should send the API secret in the `X-WP-SSO-Key` header:

```bash
curl -H "X-WP-SSO-Key: YOUR_SECRET" \
  "https://example.com/wp_api.php?type=test"
```

Bearer authentication is also accepted:

```bash
curl -H "Authorization: Bearer YOUR_SECRET" \
  "https://example.com/wp_api.php?type=test"
```

For compatibility with the existing IPS plugin definition, the legacy `api_key` query parameter remains temporarily supported:

```text
https://example.com/wp_api.php?api_key=YOUR_SECRET&type=test
```

Requests using the query-string secret receive deprecation headers. New integrations should not rely on this form because URLs may be captured by access logs, browser history, reverse proxies, or monitoring tools.

## Security improvements

The maintained endpoint now includes several defensive measures:

- constant-time secret comparison through `hash_equals()`;
- header-based authentication support;
- optional `WP_SSO_API_KEY` environment-variable configuration;
- explicit request-type allowlisting;
- HTTP/HTTPS-only redirect validation;
- JSON content type and `no-store` headers for API responses;
- `X-Content-Type-Options: nosniff` for JSON responses;
- explicit failure when the default placeholder secret has not been replaced;
- safer WordPress bootstrap resolution through `__DIR__`.

The plain-text `OK` response for `type=test` is intentionally retained for compatibility.

## Security notes

This repository contains a legacy integration and should still be reviewed before production use.

- Never commit a real API key to GitHub.
- Use HTTPS for both WordPress and IPS.
- Generate a long random API secret.
- Prefer `X-WP-SSO-Key` or Bearer authentication over query-string authentication.
- Restrict access to the endpoint where possible.
- Do not expose debug output or PHP errors publicly.
- Keep WordPress, IPS, PHP, and all related plugins up to date.

See [SECURITY.md](SECURITY.md) for vulnerability reporting guidance.

## Known limitations

- The current project is not a packaged WordPress plugin; `wp_api.php` is deployed manually.
- Authentication still relies on a shared static secret.
- The legacy IPS plugin may continue using the deprecated query-string API key until its request layer is modernized.
- Compatibility with modern WordPress / IPS releases has not been continuously verified in this repository.
- There is no integration test suite yet; current automation performs PHP syntax validation.

## Roadmap

Potential modernization work includes:

- convert the WordPress side into a standard WordPress plugin;
- update the IPS integration to use header-based authentication exclusively;
- add automated endpoint and authentication tests;
- document supported WordPress, IPS, and PHP versions;
- add release packaging and migration documentation;
- remove query-string API-key compatibility in a future breaking release.

## Contributing

Bug reports, compatibility findings, documentation improvements, and security-conscious refactors are welcome. Keep pull requests focused and describe the WordPress, IPS, and PHP versions used for testing.

For security-sensitive reports, avoid publishing real credentials or production endpoint details in public issues.

## License

No explicit license file is currently included in this repository. Until a license is added, normal copyright restrictions apply.
