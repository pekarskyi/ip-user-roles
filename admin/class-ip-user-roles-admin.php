<?php
/**
 * The admin-specific functionality of the plugin
 *
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class IP_User_Roles_Admin {
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
     * Settings table name
     *
     * @var string
     */
    private $settings_table;
    
    /**
     * Constructor
     *
     * @param string $version Plugin version
     */
    public function __construct($version) {
        $this->version = $version;
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ip_user_roles';
        $this->settings_table = $wpdb->prefix . 'ip_user_roles_settings';
    }
    
    /**
     * Register the stylesheets for the admin area
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'ip-user-roles',
            IP_USER_ROLES_URL . 'assets/css/ip-user-roles.css',
            array(),
            $this->version,
            'all'
        );
    }
    
    /**
     * Register the JavaScript for the admin area
     */
    public function enqueue_scripts() {
        // Add ThickBox for modal dialogs
        add_thickbox();
        
        wp_enqueue_script(
            'ip-user-roles',
            IP_USER_ROLES_URL . 'assets/js/ip-user-roles.js',
            array('jquery'),
            $this->version,
            false
        );
        
        // Add script localization for AJAX
        wp_localize_script('ip-user-roles', 'ip_user_roles', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'admin_url' => admin_url(),
            'nonce' => wp_create_nonce('ip_user_roles_nonce'),
            'confirm_delete' => __('Are you sure you want to delete this role? Users with only this role will be set to the default role.', 'ipuroles'),
            'role_created' => __('Role created successfully', 'ipuroles'),
            'role_updated' => __('Role updated successfully', 'ipuroles'),
            'role_error' => __('Error processing role', 'ipuroles'),
            'add_role_title' => __('Add New Role', 'ipuroles'),
            'edit_role_title' => __('Edit Role', 'ipuroles'),
            'add_role_button' => __('Create Role', 'ipuroles'),
            'edit_role_button' => __('Update Role', 'ipuroles')
        ));
    }
    
    /**
     * Register admin menu pages
     */
    public function register_admin_menu() {
        add_menu_page(
            __('IP User Roles', 'ipuroles'),
            __('IP User Roles', 'ipuroles'),
            'manage_ip_user_roles',
            'ip-user-roles',
            array($this, 'display_main_page'),
            'dashicons-groups',
            70
        );
        
        add_submenu_page(
            'ip-user-roles',
            __('Roles', 'ipuroles'),
            __('Roles', 'ipuroles'),
            'manage_ip_user_roles',
            'ip-user-roles',
            array($this, 'display_main_page')
        );
        
        // Add the edit role page (hidden from menu)
        add_submenu_page(
            null, // No parent - hide from menu
            __('Edit Role', 'ipuroles'),
            __('Edit Role', 'ipuroles'),
            'manage_ip_user_roles',
            'ip-user-roles-edit',
            array($this, 'display_edit_role_page')
        );
        
        add_submenu_page(
            'ip-user-roles',
            __('Settings', 'ipuroles'),
            __('Settings', 'ipuroles'),
            'manage_ip_user_roles',
            'ip-user-roles-settings',
            array($this, 'display_settings_page')
        );
    }
    
    /**
     * Add settings link to the plugins page
     *
     * @param array $links Plugin action links
     * @return array Modified plugin action links
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=ip-user-roles-settings') . '">' . __('Settings', 'ipuroles') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Display the main plugin page
     */
    public function display_main_page() {
        // Check if we need to delete a role
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && isset($_GET['_wpnonce'])) {
            $role_id = intval($_GET['id']);
            $nonce = sanitize_text_field($_GET['_wpnonce']);
            
            if (wp_verify_nonce($nonce, 'delete_role_' . $role_id)) {
                $this->delete_role($role_id);
            }
        }
        
        // Get all custom roles from database
        $roles = $this->get_custom_roles();
        
        // Include the template
        include_once IP_USER_ROLES_PATH . 'admin/partials/ip-user-roles-admin-display.php';
    }
    
    /**
     * Display the new role page
     */
    public function display_new_role_page() {
        // Check if form is submitted
        if (isset($_POST['submit_role']) && check_admin_referer('ip_user_roles_new_role')) {
            $this->handle_role_submission();
        }
        
        // Include the template
        include_once IP_USER_ROLES_PATH . 'admin/partials/ip-user-roles-admin-new-role.php';
    }
    
    /**
     * Display the settings page
     */
    public function display_settings_page() {
        // Check if form is submitted
        if (isset($_POST['submit_settings']) && check_admin_referer('ip_user_roles_settings')) {
            $this->handle_settings_submission();
        }
        
        // Get current settings from database table
        $delete_tables = $this->get_setting('delete_tables_on_uninstall', '0');
        $delete_tables = $delete_tables === '1'; // Convert to boolean
        
        // Include the template
        include_once IP_USER_ROLES_PATH . 'admin/partials/ip-user-roles-admin-settings.php';
    }
    
    /**
     * Display the edit role page
     */
    public function display_edit_role_page() {
        // Get role ID from URL
        $role_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        // Get role data from database
        $role = $this->get_role_by_id($role_id);
        
        // Check if form is submitted
        if (isset($_POST['submit_edit_role']) && check_admin_referer('ip_user_roles_edit_role')) {
            $this->handle_edit_role_submission();
        }
        
        // Include the template
        include_once IP_USER_ROLES_PATH . 'admin/partials/ip-user-roles-admin-edit-role.php';
    }
    
    /**
     * Handle form submission for new role
     */
    private function handle_role_submission() {
        $role_name = sanitize_text_field($_POST['role_name']);
        $role_slug = sanitize_key($_POST['role_slug']);
        
        if (empty($role_name) || empty($role_slug)) {
            add_settings_error('ip-user-roles', 'empty-fields', __('Role name and slug cannot be empty', 'ipuroles'));
            return;
        }
        
        // Get subscriber capabilities
        $capabilities = get_option('ip_user_roles_default_caps', array());
        
        // Insert into database
        global $wpdb;
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'role_name' => $role_name,
                'role_slug' => $role_slug,
                'capabilities' => serialize($capabilities)
            ),
            array('%s', '%s', '%s')
        );
        
        if ($result) {
            // Create the role in WordPress
            add_role($role_slug, $role_name, $capabilities);
            
            // Add success message
            add_settings_error('ip-user-roles', 'role-created', __('Role created successfully', 'ipuroles'), 'success');
        } else {
            // Add error message
            add_settings_error('ip-user-roles', 'role-error', __('Error creating role', 'ipuroles'));
        }
    }
    
    /**
     * Handle form submission for editing a role
     */
    private function handle_edit_role_submission() {
        $role_id = isset($_POST['role_id']) ? intval($_POST['role_id']) : 0;
        $role_name = sanitize_text_field($_POST['role_name']);
        $role_slug = sanitize_key($_POST['role_slug']);
        $original_slug = sanitize_key($_POST['original_slug']);
        
        if (empty($role_name) || empty($role_slug)) {
            add_settings_error('ip-user-roles', 'empty-fields', __('Role name and slug cannot be empty', 'ipuroles'));
            return;
        }
        
        // Update the role in database
        global $wpdb;
        $result = $wpdb->update(
            $this->table_name,
            array(
                'role_name' => $role_name,
                'role_slug' => $role_slug
            ),
            array('id' => $role_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result !== false) {
            // If the slug has changed, we need to recreate the role
            if ($original_slug !== $role_slug) {
                // Get the capabilities from the original role
                $wp_role = get_role($original_slug);
                $capabilities = $wp_role ? $wp_role->capabilities : array();
                
                // Remove the old role
                remove_role($original_slug);
                
                // Add the new role
                add_role($role_slug, $role_name, $capabilities);
                
                // Update users with this role
                $this->update_users_role($original_slug, $role_slug);
            } else {
                // Just update the display name of the role
                $wp_roles = wp_roles();
                $wp_roles->roles[$role_slug]['name'] = $role_name;
                $wp_roles->role_names[$role_slug] = $role_name;
                update_option($wp_roles->role_key, $wp_roles->roles);
            }
            
            // Add success message
            add_settings_error('ip-user-roles', 'role-updated', __('Role updated successfully', 'ipuroles'), 'success');
        } else {
            // Add error message
            add_settings_error('ip-user-roles', 'role-error', __('Error updating role', 'ipuroles'));
        }
        
        // Redirect to prevent form resubmission
        wp_redirect(admin_url('admin.php?page=ip-user-roles-edit&id=' . $role_id));
        exit;
    }
    
    /**
     * Handle form submission for settings
     */
    private function handle_settings_submission() {
        $delete_tables = isset($_POST['delete_tables']) ? '1' : '0';
        
        // Update setting in our custom table
        $result = $this->update_setting('delete_tables_on_uninstall', $delete_tables);
        
        if ($result) {
            // Add success message
            add_settings_error('ip-user-roles', 'settings-updated', __('Settings updated successfully', 'ipuroles'), 'success');
        } else {
            // Add error message
            add_settings_error('ip-user-roles', 'settings-error', __('Error updating settings', 'ipuroles'), 'error');
        }
    }
    
    /**
     * Delete a custom role
     *
     * @param int $role_id Role ID
     */
    private function delete_role($role_id) {
        global $wpdb;
        
        // Get the role from database
        $role = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $role_id));
        
        if ($role) {
            // Delete the role from WordPress
            remove_role($role->role_slug);
            
            // Delete the role from database
            $wpdb->delete(
                $this->table_name,
                array('id' => $role_id),
                array('%d')
            );
            
            // Add success message
            add_settings_error('ip-user-roles', 'role-deleted', __('Role deleted successfully', 'ipuroles'), 'success');
        } else {
            // Add error message
            add_settings_error('ip-user-roles', 'role-not-found', __('Role not found', 'ipuroles'));
        }
    }
    
    /**
     * Get all custom roles from database
     *
     * @return array List of custom roles
     */
    private function get_custom_roles() {
        global $wpdb;
        $results = $wpdb->get_results("SELECT * FROM {$this->table_name} ORDER BY id DESC");
        
        return $results;
    }
    
    /**
     * Get a role by ID
     *
     * @param int $role_id Role ID
     * @return object|null Role object or null if not found
     */
    private function get_role_by_id($role_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $role_id));
    }
    
    /**
     * Update users with a specific role to a new role
     *
     * @param string $old_role Old role slug
     * @param string $new_role New role slug
     */
    private function update_users_role($old_role, $new_role) {
        // Get users with this role
        $users = get_users(array('role' => $old_role));
        
        // Update each user
        foreach ($users as $user) {
            // Get all roles for the user
            $user_roles = get_user_meta($user->ID, 'ip_user_roles', true);
            
            // If no custom roles set, use default WP roles
            if (empty($user_roles)) {
                $user_roles = $user->roles;
            }
            
            // Replace old role with new role
            $updated_roles = array();
            foreach ($user_roles as $role) {
                if ($role === $old_role) {
                    $updated_roles[] = $new_role;
                } else {
                    $updated_roles[] = $role;
                }
            }
            
            // Update user roles
            update_user_meta($user->ID, 'ip_user_roles', $updated_roles);
            
            // Remove all roles
            foreach ($user->roles as $role) {
                $user->remove_role($role);
            }
            
            // Add each role
            foreach ($updated_roles as $role) {
                $user->add_role($role);
            }
        }
    }

    /**
     * Initialize AJAX handlers for role operations
     */
    public function init_ajax_handlers() {
        // Ajax handlers
        add_action('wp_ajax_ip_user_roles_get_role', array($this, 'ajax_get_role'));
        add_action('wp_ajax_ip_user_roles_save_role', array($this, 'ajax_save_role'));
    }
    
    /**
     * AJAX handler for getting role data
     */
    public function ajax_get_role() {
        // Check nonce
        if (!check_ajax_referer('ip_user_roles_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed.', 'ipuroles')));
        }
        
        // Check if user has permission
        if (!current_user_can('manage_ip_user_roles')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'ipuroles')));
        }
        
        $role_id = isset($_POST['role_id']) ? intval($_POST['role_id']) : 0;
        
        if ($role_id > 0) {
            // Get existing role data
            $role = $this->get_role_by_id($role_id);
            
            if ($role) {
                wp_send_json_success(array(
                    'id' => $role->id,
                    'name' => $role->role_name,
                    'slug' => $role->role_slug
                ));
            } else {
                wp_send_json_error(array('message' => __('Role not found.', 'ipuroles')));
            }
        } else {
            // Return empty role data for new role
            wp_send_json_success(array(
                'id' => 0,
                'name' => '',
                'slug' => ''
            ));
        }
    }
    
    /**
     * AJAX handler for saving role data
     */
    public function ajax_save_role() {
        // Check nonce
        if (!check_ajax_referer('ip_user_roles_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed.', 'ipuroles')));
        }
        
        // Check if user has permission
        if (!current_user_can('manage_ip_user_roles')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'ipuroles')));
        }
        
        $role_id = isset($_POST['role_id']) ? intval($_POST['role_id']) : 0;
        $role_name = isset($_POST['role_name']) ? sanitize_text_field($_POST['role_name']) : '';
        $role_slug = isset($_POST['role_slug']) ? sanitize_key($_POST['role_slug']) : '';
        $original_slug = isset($_POST['original_slug']) ? sanitize_key($_POST['original_slug']) : '';
        
        if (empty($role_name) || empty($role_slug)) {
            wp_send_json_error(array('message' => __('Role name and slug cannot be empty.', 'ipuroles')));
        }
        
        global $wpdb;
        
        if ($role_id > 0) {
            // Updating existing role
            $result = $wpdb->update(
                $this->table_name,
                array(
                    'role_name' => $role_name,
                    'role_slug' => $role_slug
                ),
                array('id' => $role_id),
                array('%s', '%s'),
                array('%d')
            );
            
            if ($result !== false) {
                // If the slug has changed, we need to recreate the role
                if ($original_slug !== $role_slug) {
                    // Get the capabilities from the original role
                    $wp_role = get_role($original_slug);
                    $capabilities = $wp_role ? $wp_role->capabilities : array();
                    
                    // Remove the old role
                    remove_role($original_slug);
                    
                    // Add the new role
                    add_role($role_slug, $role_name, $capabilities);
                    
                    // Update users with this role
                    $this->update_users_role($original_slug, $role_slug);
                } else {
                    // Just update the display name of the role
                    $wp_roles = wp_roles();
                    $wp_roles->roles[$role_slug]['name'] = $role_name;
                    $wp_roles->role_names[$role_slug] = $role_name;
                    update_option($wp_roles->role_key, $wp_roles->roles);
                }
                
                wp_send_json_success(array(
                    'message' => __('Role updated successfully.', 'ipuroles'),
                    'role' => array(
                        'id' => $role_id,
                        'name' => $role_name,
                        'slug' => $role_slug
                    )
                ));
            } else {
                wp_send_json_error(array('message' => __('Error updating role.', 'ipuroles')));
            }
        } else {
            // Creating new role
            $capabilities = get_option('ip_user_roles_default_caps', array());
            
            // Insert into database
            $result = $wpdb->insert(
                $this->table_name,
                array(
                    'role_name' => $role_name,
                    'role_slug' => $role_slug,
                    'capabilities' => serialize($capabilities)
                ),
                array('%s', '%s', '%s')
            );
            
            if ($result) {
                $new_role_id = $wpdb->insert_id;
                
                // Create the role in WordPress
                add_role($role_slug, $role_name, $capabilities);
                
                // Get the creation date
                $role = $this->get_role_by_id($new_role_id);
                $created_date = date_i18n(get_option('date_format'), strtotime($role->created_at));
                
                wp_send_json_success(array(
                    'message' => __('Role created successfully.', 'ipuroles'),
                    'role' => array(
                        'id' => $new_role_id,
                        'name' => $role_name,
                        'slug' => $role_slug,
                        'created_at' => $created_date
                    )
                ));
            } else {
                wp_send_json_error(array('message' => __('Error creating role.', 'ipuroles')));
            }
        }
    }

    /**
     * Get a setting from the settings table
     *
     * @param string $setting_name The name of the setting
     * @param mixed $default Default value if setting not found
     * @return mixed The setting value
     */
    private function get_setting($setting_name, $default = '') {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT setting_value FROM {$this->settings_table} WHERE setting_name = %s",
            $setting_name
        );
        
        $result = $wpdb->get_var($query);
        
        if ($result === null) {
            return $default;
        }
        
        return $result;
    }
    
    /**
     * Update a setting in the settings table
     *
     * @param string $setting_name The name of the setting
     * @param mixed $setting_value The new value
     * @return bool True if update was successful
     */
    private function update_setting($setting_name, $setting_value) {
        global $wpdb;
        
        // Check if the setting exists
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->settings_table} WHERE setting_name = %s",
                $setting_name
            )
        );
        
        if ($exists > 0) {
            // Update existing setting
            $result = $wpdb->update(
                $this->settings_table,
                array('setting_value' => $setting_value),
                array('setting_name' => $setting_name),
                array('%s'),
                array('%s')
            );
            
            return $result !== false;
        } else {
            // Insert new setting
            $result = $wpdb->insert(
                $this->settings_table,
                array(
                    'setting_name' => $setting_name,
                    'setting_value' => $setting_value
                ),
                array('%s', '%s')
            );
            
            return $result !== false;
        }
    }
}