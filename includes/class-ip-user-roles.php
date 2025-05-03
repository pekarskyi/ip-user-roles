<?php
/**
 * The main plugin class
 *
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class IP_User_Roles {
    /**
     * The plugin version
     *
     * @var string
     */
    private $version;
    
    /**
     * Database table name
     *
     * @var string
     */
    private $table_name;
    
    /**
     * Admin class instance
     *
     * @var IP_User_Roles_Admin
     */
    private $admin;
    
    /**
     * Plugin constructor
     */
    public function __construct() {
        $this->version = IP_USER_ROLES_VERSION;
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ip_user_roles';
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Admin class
        require_once IP_USER_ROLES_PATH . 'admin/class-ip-user-roles-admin.php';
        $this->admin = new IP_User_Roles_Admin($this->version);
        
        // User profile class
        require_once IP_USER_ROLES_PATH . 'includes/class-ip-user-roles-profile.php';
    }
    
    /**
     * Set the plugin locale for internationalization
     */
    private function set_locale() {
        add_action('plugins_loaded', function() {
            load_plugin_textdomain(
                IP_USER_ROLES_TEXT_DOMAIN,
                false,
                dirname(dirname(plugin_basename(__FILE__))) . '/lang/'
            );
        });
    }
    
    /**
     * Register all admin hooks
     */
    private function define_admin_hooks() {
        // Add settings link to plugins page
        add_filter('plugin_action_links_' . IP_USER_ROLES_BASENAME, array($this->admin, 'add_settings_link'));
        
        // Enqueue admin scripts and styles
        add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_scripts'));
        
        // Register admin menu
        add_action('admin_menu', array($this->admin, 'register_admin_menu'));
        
        // Initialize AJAX handlers
        $this->admin->init_ajax_handlers();
    }
    
    /**
     * Register all public hooks
     */
    private function define_public_hooks() {
        // Initialize multi-roles support
        require_once IP_USER_ROLES_PATH . 'includes/class-ip-user-roles-handler.php';
        $roles_handler = new IP_User_Roles_Handler();
        
        // Filter user roles to show multiple roles
        add_filter('get_user_role', array($roles_handler, 'get_user_roles'));
    }
}