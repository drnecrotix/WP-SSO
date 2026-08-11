# Contributing to WP-SSO

Thanks for helping improve WP-SSO. The project bridges WordPress with IPS / Invision Community, so authentication and compatibility changes should be kept small, reviewable, and security-conscious.

## Before opening a pull request

1. Open or reference an issue for behavior changes, compatibility work, or larger refactors.
2. Never include production API keys, cookies, user data, private URLs, or server credentials.
3. Keep each pull request focused on one purpose.
4. Preserve backward compatibility unless the change is explicitly documented as breaking.

## Local checks

PHP files should pass syntax validation:

```bash
php -l wp-sso/wp-sso.php
php -l wp_api.php
```

When changing the WordPress plugin package, verify that `wp-sso/readme.txt` and the plugin header use the same version.

## Pull request checklist

- Describe what changed and why.
- List the WordPress, IPS / Invision Community, and PHP versions used for testing when relevant.
- Explain any authentication, cookie, redirect, or API compatibility impact.
- Update documentation for user-visible behavior.
- Add or update release notes for release-worthy changes.
- Confirm that no secrets or personal data are included.

## Security reports

Do not open a public issue for a vulnerability that could expose credentials, authentication state, or private user information. Follow [SECURITY.md](SECURITY.md) instead.

## Commit style

Short Conventional Commit-style subjects are preferred, for example:

```text
fix: validate redirect URL
feat: add bearer authentication
docs: update migration guide
ci: add release validation
```

## Scope

Useful contributions include:

- WordPress and IPS compatibility fixes;
- authentication hardening;
- migration and deployment documentation;
- automated tests;
- release and packaging improvements;
- accessibility and administrator UX improvements.

Avoid unrelated formatting-only churn in security-sensitive files.