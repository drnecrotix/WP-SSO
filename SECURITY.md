# Security Policy

## Supported versions

WP-SSO is currently maintained as a legacy integration. No specific WordPress, IPS/Invision Community, or PHP release line is formally guaranteed as supported yet.

Before using the project in production, test it against your exact environment and keep all platform dependencies up to date.

## Reporting a vulnerability

Please do not publish real API keys, cookies, private endpoint URLs, access logs, or production credentials in public issues.

When reporting a security issue, include:

- the affected WP-SSO file or endpoint;
- WordPress version;
- IPS/Invision Community version;
- PHP version;
- a minimal reproduction without production secrets;
- the expected and observed behavior;
- any suggested mitigation, if known.

If a public issue would disclose exploitable details or credentials, contact the maintainer privately instead.

## Current security considerations

The existing implementation uses a shared API secret provided through the `api_key` query parameter. Query-string secrets may be stored in browser history, web-server logs, reverse-proxy logs, analytics systems, or monitoring tools.

Deployments should use HTTPS, a long random secret, limited endpoint exposure, and strict server permissions. A future revision should move API authentication to an HTTP header and add stronger request validation.
