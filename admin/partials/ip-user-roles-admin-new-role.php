<?php
/**
 * New role admin page display
 *
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php
    // Show any admin notices
    settings_errors('ip-user-roles');
    ?>
    
    <div class="ip-user-roles-admin">
        <form method="post" action="">
            <?php wp_nonce_field('ip_user_roles_new_role'); ?>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="role_name"><?php esc_html_e('Role Name', 'ipuroles'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="role_name" name="role_name" class="regular-text" required>
                        <p class="description"><?php esc_html_e('The name of the role, e.g. "Content Editor"', 'ipuroles'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="role_slug"><?php esc_html_e('Role Slug', 'ipuroles'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="role_slug" name="role_slug" class="regular-text" required>
                        <p class="description"><?php esc_html_e('The slug for the role, e.g. "content_editor". Use only lowercase letters, numbers, and underscores.', 'ipuroles'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <?php esc_html_e('Permissions', 'ipuroles'); ?>
                    </th>
                    <td>
                        <p><?php esc_html_e('This role will have Subscriber-level permissions.', 'ipuroles'); ?></p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="submit_role" class="button button-primary" value="<?php esc_attr_e('Create Role', 'ipuroles'); ?>">
            </p>
        </form>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Auto-generate slug from role name
        $('#role_name').on('keyup', function() {
            var slug = $(this).val().toLowerCase().replace(/[^a-z0-9]/g, '_').replace(/_{2,}/g, '_').replace(/^_|_$/g, '');
            $('#role_slug').val(slug);
        });
    });
</script>