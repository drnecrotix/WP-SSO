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
└── .gitignore
```

## Requirements

- WordPress
- IPS / Invision Community installed on the same environment or otherwise able to reach the WordPress endpoint
- PHP 7.4 or newer for the existing implementation
- access to the WordPress filesystem and IPS plugin administration

For new deployments, prefer a currently supported PHP release and test the integration against the exact WordPress and IPS versions you use.

## Installation

### 1. Install the WordPress endpoint

Copy `wp_api.php` into the WordPress installation root, next to `wp-load.php`.

Edit this line:

```php
$apiKey = 'YOUR-API-HERE';
```

Replace the placeholder with a long random secret and keep it private.

### 2. Configure the shared cookie domain when required

If WordPress and IPS are installed on different subdomains of the same parent domain, configure WordPress cookies appropriately in `wp-config.php`.

Example:

```php
define( 'COOKIE_DOMAIN', '.example.com' );
```

Do not define `COOKIE_DOMAIN` twice. Use the value that matches your actual deployment.

### 3. Install the IPS plugin

Import `WordPress SSO.xml` manually from the IPS plugin administration area.

Open the WP-SSO plugin settings and configure the WordPress API endpoint and the same API secret used in `wp_api.php`.

### 4. Configure login flow

If desired, point the IPS login flow to the WordPress login page so authentication happens through WordPress first.

## Endpoint behavior

The WordPress endpoint currently supports these `type` values:

| Type | Purpose |
| --- | --- |
| `userinfo` | Validate the current WordPress login cookie and return user information |
| `roles` | Return WordPress role names |
| `login` | Return a WordPress login URL |
| `register` | Return a WordPress registration URL |
| `logout` | Return a WordPress logout URL |
| `test` | Return `OK` to confirm connectivity |

All requests require the configured `api_key` query parameter.

Example connectivity request:

```text
https://example.com/wp_api.php?api_key=YOUR_SECRET&type=test
```

## Security notes

This repository contains a legacy integration and should be reviewed before production use.

- Never commit a real API key to GitHub.
- Use HTTPS for both WordPress and IPS.
- Generate a long random API secret.
- Restrict access to the endpoint where possible.
- Do not expose debug output or PHP errors publicly.
- Test redirect handling carefully.
- Keep WordPress, IPS, PHP, and all related plugins up to date.

The API secret is currently supplied as a query parameter. Query-string secrets can appear in access logs, browser history, reverse-proxy logs, and monitoring systems. A future version should move authentication to a safer request mechanism such as an HTTP header.

## Known limitations

- The current project is not a packaged WordPress plugin; `wp_api.php` is deployed manually.
- The API uses a shared static secret.
- The implementation assumes `wp-load.php` is in the same directory as `wp_api.php`.
- Compatibility with modern WordPress / IPS releases has not been continuously verified in this repository.
- There is no automated test suite yet.

## Roadmap

Potential modernization work includes:

- convert the WordPress side into a standard WordPress plugin;
- replace query-string API authentication with an HTTP header;
- add stricter request validation and JSON response headers;
- add automated PHP linting and security checks;
- document supported WordPress, IPS, and PHP versions;
- add release packaging and migration documentation.

## Contributing

Bug reports, compatibility findings, documentation improvements, and security-conscious refactors are welcome. Keep pull requests focused and describe the WordPress, IPS, and PHP versions used for testing.

For security-sensitive reports, avoid publishing real credentials or production endpoint details in public issues.

## License

No explicit license file is currently included in this repository. Until a license is added, normal copyright restrictions apply.
