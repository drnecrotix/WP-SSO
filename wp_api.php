<?php

/**
 * WordPress SSO API for IPS.
 *
 * Prefer setting the API key through the WP_SSO_API_KEY environment variable.
 * The query-string api_key parameter remains temporarily supported for legacy
 * IPS integrations, but new clients should send X-WP-SSO-Key instead.
 * Legacy query-string authentication is retained for backward compatibility only.
 */

$apiKey = getenv('WP_SSO_API_KEY');
if ($apiKey === false || $apiKey === '') {
    $apiKey = 'YOUR-API-HERE';
}

if ($apiKey === 'YOUR-API-HERE') {
    respondError(500, 'The SSO API key has not been configured.');
}

$providedKey = getProvidedApiKey();
if ($providedKey === null || !hash_equals((string) $apiKey, (string) $providedKey)) {
    respondError(401, 'API key not provided or incorrect.');
}

/* Get WordPress. */
$wpLoad = __DIR__ . '/wp-load.php';
if (!is_file($wpLoad)) {
    respondError(500, 'WordPress bootstrap file could not be located.');
}

require_once $wpLoad;

$type = isset($_GET['type']) && is_string($_GET['type']) ? $_GET['type'] : '';
$allowedTypes = array('userinfo', 'roles', 'login', 'register', 'logout', 'test');

if (!in_array($type, $allowedTypes, true)) {
    respondError(404, 'Unknown or missing request type.');
}

switch ($type) {
    /* Verify user cookie. */
    case 'userinfo':
        $id = wp_validate_auth_cookie('', 'logged_in');
        if (!$id) {
            respondError(403, 'The cookie does not appear to be valid.');
        }

        $user = get_user_by('id', $id);
        if (!$user) {
            respondError(404, 'The user could not be located.');
        }

        respondJson(array(
            'user_id' => $user->ID,
            'display_name' => $user->display_name,
            'email' => $user->user_email,
            'role' => count($user->roles) ? $user->roles : false,
        ));
        break;

    /* Return WordPress roles. */
    case 'roles':
        respondJson(wp_roles()->get_names());
        break;

    /* Login URL. */
    case 'login':
        respondJson(array('url' => wp_login_url(getRedirectUrl())));
        break;

    /* Register URL. */
    case 'register':
        respondJson(array('url' => wp_registration_url(getRedirectUrl())));
        break;

    /* Logout URL. */
    case 'logout':
        respondJson(array('url' => wp_logout_url(getRedirectUrl())));
        break;

    /* Test API connectivity. Keep the legacy plain-text response. */
    case 'test':
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: no-store');
        echo 'OK';
        exit;
}

/**
 * Read the API key from a request header, Bearer token, or legacy query string.
 *
 * @return string|null
 */
function getProvidedApiKey()
{
    if (isset($_SERVER['HTTP_X_WP_SSO_KEY']) && is_string($_SERVER['HTTP_X_WP_SSO_KEY'])) {
        return trim($_SERVER['HTTP_X_WP_SSO_KEY']);
    }

    if (isset($_SERVER['HTTP_AUTHORIZATION']) && is_string($_SERVER['HTTP_AUTHORIZATION'])) {
        $authorization = trim($_SERVER['HTTP_AUTHORIZATION']);
        if (stripos($authorization, 'Bearer ') === 0) {
            return trim(substr($authorization, 7));
        }
    }

    if (isset($_GET['api_key']) && is_string($_GET['api_key'])) {
        header('Deprecation: true');
        header('Warning: 299 - "Query-string api_key authentication is deprecated; use X-WP-SSO-Key"');
        return $_GET['api_key'];
    }

    return null;
}

/**
 * Return a validated redirect URL or null.
 *
 * @return string|null
 */
function getRedirectUrl()
{
    if (!isset($_GET['redirect']) || !is_string($_GET['redirect'])) {
        return null;
    }

    $url = filter_var($_GET['redirect'], FILTER_VALIDATE_URL);
    if ($url === false) {
        return null;
    }

    $scheme = parse_url($url, PHP_URL_SCHEME);
    if (!in_array(strtolower((string) $scheme), array('http', 'https'), true)) {
        return null;
    }

    return $url;
}

/**
 * Send a JSON response and stop execution.
 *
 * @param mixed $data
 * @param int   $status
 * @return void
 */
function respondJson($data, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');

    echo json_encode($data);
    exit;
}

/**
 * Send a JSON error response and stop execution.
 *
 * @param int    $status
 * @param string $message
 * @return void
 */
function respondError($status, $message)
{
    respondJson(array('error' => $message), $status);
}
