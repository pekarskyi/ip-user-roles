<?php
/**
 * Plugin activation functionality
 *
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class IP_User_Roles_Activator {
    /**
     * Activate the plugin
     * 
     * This function is called when the plugin is activated
     */
    public static function activate() {
        // Create the database table for storing custom roles
        self::create_tables();
        
        // Add capabilities
        self::add_capabilities();
    }
    
    /**
     * Create plugin database tables
     */
    private static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table for storing custom roles
        $roles_table = $wpdb->prefix . 'ip_user_roles';
        $roles_sql = "CREATE TABLE $roles_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            role_name varchar(50) NOT NULL,
            role_slug varchar(50) NOT NULL,
            capabilities longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY role_slug (role_slug)
        ) $charset_collate;";
        
        // Table for storing plugin settings
        $settings_table = $wpdb->prefix . 'ip_user_roles_settings';
        $settings_sql = "CREATE TABLE $settings_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            setting_name varchar(50) NOT NULL,
            setting_value varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY setting_name (setting_name)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($roles_sql);
        dbDelta($settings_sql);
        
        // Insert default settings
        self::initialize_settings($settings_table);
    }
    
    /**
     * Initialize default settings in the settings table
     * 
     * @param string $table_name The settings table name
     */
    private static function initialize_settings($table_name) {
        global $wpdb;
        
        // Check if the setting already exists
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE setting_name = %s",
                'delete_tables_on_uninstall'
            )
        );
        
        // Only insert if the setting doesn't exist
        if ($exists == 0) {
            $wpdb->insert(
                $table_name,
                array(
                    'setting_name' => 'delete_tables_on_uninstall',
                    'setting_value' => '0'  // Default to false
                ),
                array('%s', '%s')
            );
        }
    }
    
    /**
     * Add required capabilities to administrators
     */
    private static function add_capabilities() {
        // Get the administrator role
        $role = get_role('administrator');
        
        // Add IP User Roles capabilities
        $role->add_cap('manage_ip_user_roles');
        
        // Add default subscriber capabilities to custom roles
        // This will be used as the base for all custom roles
        update_option('ip_user_roles_default_caps', self::get_subscriber_capabilities());
    }
    
    /**
     * Get subscriber level capabilities
     * 
     * @return array Subscriber capabilities
     */
    private static function get_subscriber_capabilities() {
        $subscriber = get_role('subscriber');
        return $subscriber->capabilities;
    }
}