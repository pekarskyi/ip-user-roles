<?php
/**
 * Settings admin page display
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
            <?php wp_nonce_field('ip_user_roles_settings'); ?>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <?php esc_html_e('Uninstall Options', 'ipuroles'); ?>
                    </th>
                    <td>
                        <label for="delete_tables">
                            <input type="checkbox" id="delete_tables" name="delete_tables" <?php checked($delete_tables); ?>>
                            <?php esc_html_e('Delete database tables when plugin is deleted', 'ipuroles'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('If checked, all plugin data will be permanently removed when the plugin is deleted.', 'ipuroles'); ?></p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="submit_settings" class="button button-primary" value="<?php esc_attr_e('Save Settings', 'ipuroles'); ?>">
            </p>
        </form>
    </div>
</div>