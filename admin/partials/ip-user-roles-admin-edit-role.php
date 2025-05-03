<?php
/**
 * Edit role admin page display
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
        <?php if (isset($role) && !empty($role)) : ?>
            <form method="post" action="">
                <?php wp_nonce_field('ip_user_roles_edit_role'); ?>
                <input type="hidden" name="role_id" value="<?php echo esc_attr($role->id); ?>">
                <input type="hidden" name="original_slug" value="<?php echo esc_attr($role->role_slug); ?>">
                
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="role_name"><?php esc_html_e('Role Name', 'ipuroles'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="role_name" name="role_name" class="regular-text" value="<?php echo esc_attr($role->role_name); ?>" required>
                            <p class="description"><?php esc_html_e('The name of the role, e.g. "Content Editor"', 'ipuroles'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="role_slug"><?php esc_html_e('Role Slug', 'ipuroles'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="role_slug" name="role_slug" class="regular-text" value="<?php echo esc_attr($role->role_slug); ?>" required>
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
                    <input type="submit" name="submit_edit_role" class="button button-primary" value="<?php esc_attr_e('Update Role', 'ipuroles'); ?>">
                </p>
            </form>
        <?php else : ?>
            <div class="notice notice-error">
                <p><?php esc_html_e('Role not found.', 'ipuroles'); ?></p>
            </div>
            <p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=ip-user-roles')); ?>" class="button"><?php esc_html_e('Back to Roles', 'ipuroles'); ?></a>
            </p>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Auto-generate slug from role name only if slug field hasn't been manually edited
        var originalSlug = $('#role_slug').val();
        var slugEdited = false;
        
        $('#role_slug').on('change', function() {
            slugEdited = true;
        });
        
        $('#role_name').on('keyup', function() {
            if (!slugEdited) {
                var slug = $(this).val().toLowerCase().replace(/[^a-z0-9]/g, '_').replace(/_{2,}/g, '_').replace(/^_|_$/g, '');
                $('#role_slug').val(slug);
            }
        });
    });
</script>