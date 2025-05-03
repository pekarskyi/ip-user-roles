<?php
/**
 * Plugin Name: IP User Roles
 * Description: Allows creating new user roles with subscriber level permissions and supports multiple role assignments
 * Version: 1.0.0
 * Author: InwebPress
 * Plugin URI: https://github.com/inwebpress/ip-user-roles
 * Author URI: https://inwebpress.com
 * Text Domain: ipuroles
 * Domain Path: /lang
 * Requires at least: 6.0.0
 * Tested up to: 6.7.2
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get plugin data for version info
if (!function_exists('get_plugin_data')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}
$plugin_data = get_plugin_data(__FILE__);

// Define plugin constants
define('IP_USER_ROLES_VERSION', $plugin_data['Version']);
define('IP_USER_ROLES_PATH', plugin_dir_path(__FILE__));
define('IP_USER_ROLES_URL', plugin_dir_url(__FILE__));
define('IP_USER_ROLES_BASENAME', plugin_basename(__FILE__));
define('IP_USER_ROLES_TEXT_DOMAIN', 'ipuroles');

// Include main plugin class
require_once IP_USER_ROLES_PATH . 'includes/class-ip-user-roles.php';

// Initialize plugin
function ip_user_roles_init() {
    $plugin = new IP_User_Roles();
    $plugin->init();
}
add_action('plugins_loaded', 'ip_user_roles_init');

// Activation hook
register_activation_hook(__FILE__, function() {
    require_once IP_USER_ROLES_PATH . 'includes/class-ip-user-roles-activator.php';
    IP_User_Roles_Activator::activate();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    require_once IP_USER_ROLES_PATH . 'includes/class-ip-user-roles-deactivator.php';
    IP_User_Roles_Deactivator::deactivate();
});

// Uninstall hook - using a file-based uninstaller instead of a closure
// Make sure the uninstaller file is loaded
require_once IP_USER_ROLES_PATH . 'includes/class-ip-user-roles-uninstaller.php';
register_uninstall_hook(__FILE__, array('IP_User_Roles_Uninstaller', 'uninstall'));