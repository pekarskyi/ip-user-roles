<?php
/**
 * Handles user profile integration for the multi-role selection
 *
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class IP_User_Roles_Profile {
    /**
     * Constructor
     */
    public function __construct() {
        // Add profile fields for multi-role selection
        add_action('show_user_profile', array($this, 'add_role_selection'));
        add_action('edit_user_profile', array($this, 'add_role_selection'));
    }
    
    /**
     * Add multi-role selection to user profile
     *
     * @param WP_User $user User object
     */
    public function add_role_selection($user) {
        // Only show if current user can edit users
        if (!current_user_can('edit_users')) {
            return;
        }
        
        // Get all available roles
        $roles = wp_roles()->roles;
        
        // Get user's selected roles
        $user_roles = get_user_meta($user->ID, 'ip_user_roles', true);
        
        if (empty($user_roles)) {
            // If no custom roles are set, use the current WordPress role
            $user_roles = $user->roles;
        }
        ?>
        <h2><?php esc_html_e('Multiple Roles', 'ipuroles'); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('User Roles', 'ipuroles'); ?></th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><?php esc_html_e('User Roles', 'ipuroles'); ?></legend>
                        <?php foreach ($roles as $role_id => $role) : ?>
                            <label>
                                <input type="checkbox" name="ip_user_roles[]" value="<?php echo esc_attr($role_id); ?>" <?php checked(in_array($role_id, (array) $user_roles)); ?>>
                                <?php echo esc_html(translate_user_role($role['name'])); ?>
                            </label>
                            <br>
                        <?php endforeach; ?>
                        <p class="description"><?php esc_html_e('Select one or more roles for this user', 'ipuroles'); ?></p>
                    </fieldset>
                </td>
            </tr>
        </table>
        <?php
    }
}

// Initialize the profile class
new IP_User_Roles_Profile();