<?php
/**
 * Plugin deactivation functionality
 *
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class IP_User_Roles_Deactivator {
    /**
     * Deactivate the plugin
     * 
     * This function is called when the plugin is deactivated
     */
    public static function deactivate() {
        // Remove capabilities
        self::remove_capabilities();
    }
    
    /**
     * Remove plugin capabilities from roles
     */
    private static function remove_capabilities() {
        $role = get_role('administrator');
        $role->remove_cap('manage_ip_user_roles');
    }
}