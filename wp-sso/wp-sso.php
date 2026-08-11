<?php
/**
 * Plugin Name: WP-SSO Bridge for IPS
 * Plugin URI: https://github.com/drnecrotix/WP-SSO
 * Description: WordPress-side SSO bridge for IPS/Invision Community with authenticated user, role, and login URL endpoints.
 * Version: 1.0.0
 * Author: Nikola Stoyanov
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

final class WP_SSO_Bridge
{
    const VERSION = '1.0.0';
    const OPTION_API_KEY = 'wp_sso_api_key';
    const ENDPOINT_QUERY_VAR = 'wp_sso_api';

    public static function init()
    {
        add_action('template_redirect', array(__CLASS__, 'maybeHandleRequest'), 0);
        add_action('admin_menu', array(__CLASS__, 'registerSettingsPage'));
        add_action('admin_init', array(__CLASS__, 'registerSettings'));
    }

    public static function activate()
    {
        if (!get_option(self::OPTION_API_KEY)) {
            update_option(self::OPTION_API_KEY, wp_generate_password(48, false, false), false);
        }
    }

    public static function registerSettings()
    {
        register_setting(
            'wp_sso_settings',
            self::OPTION_API_KEY,
            array(
                'type' => 'string',
                'sanitize_callback' => array(__CLASS__, 'sanitizeApiKey'),
                'default' => '',
            )
        );
    }

    public static function sanitizeApiKey($value)
    {
        $value = is_string($value) ? trim($value) : '';

        if ($value === '') {
            return get_option(self::OPTION_API_KEY, '');
        }

        return preg_replace('/[^A-Za-z0-9._~-]/', '', $value);
    }

    public static function registerSettingsPage()
    {
        add_options_page(
            'WP-SSO Bridge',
            'WP-SSO Bridge',
            'manage_options',
            'wp-sso-bridge',
            array(__CLASS__, 'renderSettingsPage')
        );
    }

    public static function renderSettingsPage()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $endpoint = add_query_arg(self::ENDPOINT_QUERY_VAR, '1', home_url('/'));
        $apiKey = self::getConfiguredApiKey();
        ?>
        <div class="wrap">
            <h1>WP-SSO Bridge</h1>
            <p>Use this endpoint in the IPS integration:</p>
            <p><code><?php echo esc_html($endpoint); ?></code></p>

            <form method="post" action="options.php">
                <?php settings_fields('wp_sso_settings'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="wp_sso_api_key">API secret</label></th>
                        <td>
                            <input
                                type="password"
                                class="regular-text code"
                                id="wp_sso_api_key"
                                name="<?php echo esc_attr(self::OPTION_API_KEY); ?>"
                                value="<?php echo esc_attr($apiKey); ?>"
                                autocomplete="new-password"
                            />
                            <p class="description">Use a long random secret. Environment variable <code>WP_SSO_API_KEY</code> or constant <code>WP_SSO_API_KEY</code> takes precedence over this setting.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

            <h2>Connectivity example</h2>
            <p><code>curl -H "X-WP-SSO-Key: YOUR_SECRET" "<?php echo esc_html(add_query_arg(array(self::ENDPOINT_QUERY_VAR => '1', 'type' => 'test'), home_url('/'))); ?>"</code></p>
        </div>
        <?php
    }

    public static function maybeHandleRequest()
    {
        if (!isset($_GET[self::ENDPOINT_QUERY_VAR]) || (string) $_GET[self::ENDPOINT_QUERY_VAR] !== '1') {
            return;
        }

        nocache_headers();
        header('X-Content-Type-Options: nosniff');

        $apiKey = self::getConfiguredApiKey();
        if ($apiKey === '') {
            self::respondError(500, 'The SSO API key has not been configured.');
        }

        $providedKey = self::getProvidedApiKey();
        if ($providedKey === null || !hash_equals($apiKey, $providedKey)) {
            self::respondError(401, 'API key not provided or incorrect.');
        }

        $type = isset($_GET['type']) && is_string($_GET['type']) ? sanitize_key(wp_unslash($_GET['type'])) : '';
        $allowedTypes = array('userinfo', 'roles', 'login', 'register', 'logout', 'test');

        if (!in_array($type, $allowedTypes, true)) {
            self::respondError(404, 'Unknown or missing request type.');
        }

        switch ($type) {
            case 'userinfo':
                $id = wp_validate_auth_cookie('', 'logged_in');
                if (!$id) {
                    self::respondError(403, 'The cookie does not appear to be valid.');
                }

                $user = get_user_by('id', $id);
                if (!$user) {
                    self::respondError(404, 'The user could not be located.');
                }

                self::respondJson(array(
                    'user_id' => $user->ID,
                    'display_name' => $user->display_name,
                    'email' => $user->user_email,
                    'role' => count($user->roles) ? $user->roles : false,
                ));
                break;

            case 'roles':
                self::respondJson(wp_roles()->get_names());
                break;

            case 'login':
                self::respondJson(array('url' => wp_login_url(self::getRedirectUrl())));
                break;

            case 'register':
                self::respondJson(array('url' => wp_registration_url(self::getRedirectUrl())));
                break;

            case 'logout':
                self::respondJson(array('url' => wp_logout_url(self::getRedirectUrl())));
                break;

            case 'test':
                status_header(200);
                header('Content-Type: text/plain; charset=utf-8');
                echo 'OK';
                exit;
        }
    }

    private static function getConfiguredApiKey()
    {
        $environmentKey = getenv('WP_SSO_API_KEY');
        if (is_string($environmentKey) && trim($environmentKey) !== '') {
            return trim($environmentKey);
        }

        if (defined('WP_SSO_API_KEY') && is_string(WP_SSO_API_KEY) && trim(WP_SSO_API_KEY) !== '') {
            return trim(WP_SSO_API_KEY);
        }

        $optionKey = get_option(self::OPTION_API_KEY, '');
        return is_string($optionKey) ? trim($optionKey) : '';
    }

    private static function getProvidedApiKey()
    {
        if (isset($_SERVER['HTTP_X_WP_SSO_KEY']) && is_string($_SERVER['HTTP_X_WP_SSO_KEY'])) {
            return trim(wp_unslash($_SERVER['HTTP_X_WP_SSO_KEY']));
        }

        if (isset($_SERVER['HTTP_AUTHORIZATION']) && is_string($_SERVER['HTTP_AUTHORIZATION'])) {
            $authorization = trim(wp_unslash($_SERVER['HTTP_AUTHORIZATION']));
            if (stripos($authorization, 'Bearer ') === 0) {
                return trim(substr($authorization, 7));
            }
        }

        if (isset($_GET['api_key']) && is_string($_GET['api_key'])) {
            header('Deprecation: true');
            header('Warning: 299 - "Query-string api_key authentication is deprecated; use X-WP-SSO-Key"');
            return trim(wp_unslash($_GET['api_key']));
        }

        return null;
    }

    private static function getRedirectUrl()
    {
        if (!isset($_GET['redirect']) || !is_string($_GET['redirect'])) {
            return '';
        }

        $url = esc_url_raw(wp_unslash($_GET['redirect']), array('http', 'https'));
        return $url ? $url : '';
    }

    private static function respondJson($data, $status = 200)
    {
        status_header($status);
        header('Content-Type: application/json; charset=utf-8');
        echo wp_json_encode($data);
        exit;
    }

    private static function respondError($status, $message)
    {
        self::respondJson(array('error' => $message), $status);
    }
}

register_activation_hook(__FILE__, array('WP_SSO_Bridge', 'activate'));
WP_SSO_Bridge::init();
