<?php
/**
 * Main admin page display
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
        <div class="ip-user-roles-list">
            <h2><?php esc_html_e('Custom Roles', 'ipuroles'); ?></h2>
            
            <p><?php esc_html_e('Below is a list of custom roles created with IP User Roles plugin.', 'ipuroles'); ?></p>
            
            <a href="#TB_inline?width=600&height=300&inlineId=ip-user-roles-form-modal" class="button button-primary thickbox" id="add-new-role">
                <?php esc_html_e('Add New Role', 'ipuroles'); ?>
            </a>
            
            <?php if (!empty($roles)) : ?>
                <table class="wp-list-table widefat fixed striped roles">
                    <thead>
                        <tr>
                            <th scope="col"><?php esc_html_e('Role Name', 'ipuroles'); ?></th>
                            <th scope="col"><?php esc_html_e('Role Slug', 'ipuroles'); ?></th>
                            <th scope="col"><?php esc_html_e('Created', 'ipuroles'); ?></th>
                            <th scope="col"><?php esc_html_e('Actions', 'ipuroles'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="roles-table-body">
                        <?php foreach ($roles as $role) : ?>
                            <tr id="role-<?php echo esc_attr($role->id); ?>">
                                <td><?php echo esc_html($role->role_name); ?></td>
                                <td><?php echo esc_html($role->role_slug); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($role->created_at))); ?></td>
                                <td>
                                    <a href="#TB_inline?width=600&height=300&inlineId=ip-user-roles-form-modal" class="button button-small edit-role thickbox" data-id="<?php echo esc_attr($role->id); ?>">
                                        <?php esc_html_e('Edit', 'ipuroles'); ?>
                                    </a>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=ip-user-roles&action=delete&id=' . $role->id . '&_wpnonce=' . wp_create_nonce('delete_role_' . $role->id))); ?>" class="button button-small delete-role">
                                        <?php esc_html_e('Delete', 'ipuroles'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <div class="notice notice-warning">
                    <p><?php esc_html_e('No custom roles found. Create your first custom role!', 'ipuroles'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Role Form Modal -->
    <div id="ip-user-roles-form-modal" style="display:none;">
        <div class="ip-user-roles-modal-content">
            <h2 id="modal-title"><?php esc_html_e('Add New Role', 'ipuroles'); ?></h2>
            
            <form id="ip-user-roles-form">
                <input type="hidden" id="role_id" name="role_id" value="0">
                <input type="hidden" id="original_slug" name="original_slug" value="">
                
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
                
                <div class="submit-wrapper">
                    <div class="submit-container">
                        <p class="submit">
                            <input type="submit" id="submit-role" class="button button-primary" value="<?php esc_attr_e('Save Role', 'ipuroles'); ?>">
                            <button type="button" class="button" onclick="tb_remove();"><?php esc_html_e('Cancel', 'ipuroles'); ?></button>
                        </p>
                        <div class="status-message"></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>