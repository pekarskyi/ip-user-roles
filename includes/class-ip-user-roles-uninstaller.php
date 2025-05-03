<?php
/**
 * Plugin uninstall functionality
 *
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class IP_User_Roles_Uninstaller {
    /**
     * Uninstall the plugin
     * 
     * This function is called when the plugin is deleted
     */
    public static function uninstall() {
        // Check if we should delete the database tables
        global $wpdb;
        $settings_table = $wpdb->prefix . 'ip_user_roles_settings';
        
        // Get the 'delete_tables_on_uninstall' setting
        $delete_tables = $wpdb->get_var("SELECT setting_value FROM {$settings_table} WHERE setting_name = 'delete_tables_on_uninstall'");
        
        // If the setting exists and is set to '1', delete the tables
        if ($delete_tables === '1') {
            self::delete_tables();
        } else {
            // If we're not deleting all tables, at least delete the settings table
            $wpdb->query("DROP TABLE IF EXISTS {$settings_table}");
        }
        
        // Delete plugin options
        self::delete_options();
    }
    
    /**
     * Delete plugin database tables
     */
    private static function delete_tables() {
        global $wpdb;
        $roles_table = $wpdb->prefix . 'ip_user_roles';
        $settings_table = $wpdb->prefix . 'ip_user_roles_settings';
        
        // Drop the tables
        $wpdb->query("DROP TABLE IF EXISTS {$roles_table}");
        $wpdb->query("DROP TABLE IF EXISTS {$settings_table}");
    }
    
    /**
     * Delete plugin options
     */
    private static function delete_options() {
        delete_option('ip_user_roles_default_caps');
    }
}