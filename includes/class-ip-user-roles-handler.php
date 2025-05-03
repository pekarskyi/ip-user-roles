<?php
/**
 * Handles custom roles management and multi-role functionality
 *
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class IP_User_Roles_Handler {
    /**
     * Database table name
     *
     * @var string
     */
    private $table_name;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ip_user_roles';
        
        // Initialize multi-role functionality
        $this->init_multi_role_support();
    }
    
    /**
     * Initialize multi-role support
     */
    private function init_multi_role_support() {
        // Add action to save multiple roles when user profile is updated
        add_action('profile_update', array($this, 'save_user_roles'), 10, 2);
        add_action('user_register', array($this, 'save_user_roles'), 10, 2);
        
        // Filter to modify user roles in admin area
        add_filter('editable_roles', array($this, 'add_custom_roles'));
    }
    
    /**
     * Get all roles for a user (multi-role support)
     *
     * @param string $role Current role
     * @param int $user_id User ID
     * @return string Comma-separated list of user roles
     */
    public function get_user_roles($role, $user_id = null) {
        if (!$user_id) {
            $user = wp_get_current_user();
            $user_id = $user->ID;
        }
        
        // Get all roles assigned to the user
        $user_roles = get_user_meta($user_id, 'ip_user_roles', true);
        
        if (!empty($user_roles)) {
            $role_names = array();
            
            // Get all roles
            $all_roles = wp_roles()->roles;
            
            // Get role names for display
            foreach ($user_roles as $role_id) {
                if (isset($all_roles[$role_id])) {
                    $role_names[] = translate_user_role($all_roles[$role_id]['name']);
                }
            }
            
            if (!empty($role_names)) {
                return implode(', ', $role_names);
            }
        }
        
        return $role;
    }
    
    /**
     * Save multiple roles for a user
     *
     * @param int $user_id User ID
     * @param WP_User $user User object
     */
    public function save_user_roles($user_id, $user = null) {
        // Check for form submission with roles
        if (isset($_POST['ip_user_roles']) && current_user_can('edit_users')) {
            $roles = isset($_POST['ip_user_roles']) ? (array) $_POST['ip_user_roles'] : array();
            
            // Sanitize roles
            $roles = array_map('sanitize_key', $roles);
            
            // Save roles to user meta
            update_user_meta($user_id, 'ip_user_roles', $roles);
            
            // Get user object if not provided
            if (!$user) {
                $user = get_userdata($user_id);
            }
            
            // Remove all roles
            foreach ($user->roles as $role) {
                $user->remove_role($role);
            }
            
            // Add each selected role
            foreach ($roles as $role) {
                $user->add_role($role);
            }
        }
    }
    
    /**
     * Add custom roles to the editable roles list
     *
     * @param array $roles All editable roles
     * @return array Modified roles
     */
    public function add_custom_roles($roles) {
        // Get custom roles from database
        $custom_roles = $this->get_custom_roles();
        
        // Add custom roles to the list
        if (!empty($custom_roles)) {
            foreach ($custom_roles as $role) {
                $role_slug = $role->role_slug;
                if (!isset($roles[$role_slug]) && $wp_role = get_role($role_slug)) {
                    $roles[$role_slug] = array(
                        'name' => $role->role_name,
                        'capabilities' => $wp_role->capabilities
                    );
                }
            }
        }
        
        return $roles;
    }
    
    /**
     * Get all custom roles from database
     *
     * @return array Custom roles
     */
    public function get_custom_roles() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$this->table_name}");
    }
}