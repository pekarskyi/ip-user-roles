/**
 * IP User Roles Plugin Scripts
 *
 * @since 1.0.0
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        // Handle role deletion
        $('.ip-user-roles-list a.delete-role').on('click', function (e) {
            if (!confirm(ip_user_roles.confirm_delete)) {
                e.preventDefault();
                return false;
            }
        });

        // Variables for modal functionality
        var $modal = $('#ip-user-roles-form-modal');
        var $form = $('#ip-user-roles-form');
        var $modalTitle = $('#modal-title');
        var $roleId = $('#role_id');
        var $roleName = $('#role_name');
        var $roleSlug = $('#role_slug');
        var $originalSlug = $('#original_slug');
        var $submitBtn = $('#submit-role');
        var $statusMessage = $('.status-message');
        var slugEdited = false;

        // Cyrillic to Latin transliteration map
        var transliterationMap = {
            'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'є': 'ye',
            'ж': 'zh', 'з': 'z', 'и': 'y', 'і': 'i', 'ї': 'yi', 'й': 'y',
            'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r',
            'с': 's', 'т': 't', 'у': 'u', 'ф': 'f', 'х': 'kh', 'ц': 'ts', 'ч': 'ch',
            'ш': 'sh', 'щ': 'shch', 'ь': '', 'ю': 'yu', 'я': 'ya',
            'А': 'A', 'Б': 'B', 'В': 'V', 'Г': 'G', 'Д': 'D', 'Е': 'E', 'Є': 'Ye',
            'Ж': 'Zh', 'З': 'Z', 'И': 'Y', 'І': 'I', 'Ї': 'Yi', 'Й': 'Y',
            'К': 'K', 'Л': 'L', 'М': 'M', 'Н': 'N', 'О': 'O', 'П': 'P', 'Р': 'R',
            'С': 'S', 'Т': 'T', 'У': 'U', 'Ф': 'F', 'Х': 'Kh', 'Ц': 'Ts', 'Ч': 'Ch',
            'Ш': 'Sh', 'Щ': 'Shch', 'Ь': '', 'Ю': 'Yu', 'Я': 'Ya',
            'ы': 'y', 'э': 'e', 'ъ': '', 'ё': 'yo', // Russian specific
            'Ы': 'Y', 'Э': 'E', 'Ъ': '', 'Ё': 'Yo'  // Russian specific
        };

        // Function to transliterate Cyrillic to Latin
        function transliterate(text) {
            return text.split('').map(function (char) {
                return transliterationMap[char] || char;
            }).join('');
        }

        // Handle Add New Role button
        $('#add-new-role').on('click', function () {
            // Reset the form
            resetForm();

            // Set modal title for new role
            $modalTitle.text(ip_user_roles.add_role_title || 'Add New Role');
            $submitBtn.val(ip_user_roles.add_role_button || 'Create Role');
        });

        // Handle Edit Role button
        $('.edit-role').on('click', function () {
            var roleId = $(this).data('id');

            // Reset the form
            resetForm();

            // Set modal title for edit role
            $modalTitle.text(ip_user_roles.edit_role_title || 'Edit Role');
            $submitBtn.val(ip_user_roles.edit_role_button || 'Update Role');

            // Get role data from server
            $.ajax({
                url: ip_user_roles.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'ip_user_roles_get_role',
                    nonce: ip_user_roles.nonce,
                    role_id: roleId
                },
                success: function (response) {
                    if (response.success) {
                        // Fill the form with role data
                        $roleId.val(response.data.id);
                        $roleName.val(response.data.name);
                        $roleSlug.val(response.data.slug);
                        $originalSlug.val(response.data.slug);

                        // Mark slug as edited for existing roles to prevent auto-generation
                        slugEdited = true;
                    } else {
                        // Show error message
                        showMessage('error', response.data.message);
                    }
                },
                error: function () {
                    // Show error message
                    showMessage('error', 'Error loading role data.');
                }
            });
        });

        // Auto-generate slug from role name
        $roleName.on('keyup', function () {
            // Generate slug only if:
            // 1. The slug field is empty OR
            // 2. User hasn't manually edited the slug yet
            if ($roleSlug.val() === '' || !slugEdited) {
                var name = $(this).val();

                // First transliterate any Cyrillic characters
                var transliterated = transliterate(name);

                // Then apply standard slug formatting
                var slug = transliterated.toLowerCase()
                    .replace(/[^a-z0-9]/g, '_')
                    .replace(/_{2,}/g, '_')
                    .replace(/^_|_$/g, '');

                $roleSlug.val(slug);
            }
        });

        // Mark slug as edited when user changes it
        $roleSlug.on('change input', function () {
            slugEdited = true;
        });

        // Handle form submission
        $form.on('submit', function (e) {
            e.preventDefault();

            // Clear status message
            $statusMessage.empty().removeClass('error success');

            // Get form data
            var formData = {
                action: 'ip_user_roles_save_role',
                nonce: ip_user_roles.nonce,
                role_id: $roleId.val(),
                role_name: $roleName.val(),
                role_slug: $roleSlug.val(),
                original_slug: $originalSlug.val()
            };

            // Submit form
            $.ajax({
                url: ip_user_roles.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: formData,
                success: function (response) {
                    if (response.success) {
                        // Show success message
                        showMessage('success', response.data.message);

                        // Update table or add new row
                        if (formData.role_id > 0) {
                            updateRoleRow(response.data.role);
                        } else {
                            addRoleRow(response.data.role);
                        }

                        // Close modal after a brief delay
                        setTimeout(function () {
                            tb_remove();
                        }, 1500);
                    } else {
                        // Show error message
                        showMessage('error', response.data.message);
                    }
                },
                error: function () {
                    // Show error message
                    showMessage('error', 'Error saving role.');
                }
            });
        });

        // Function to reset the form
        function resetForm() {
            $form[0].reset();
            $roleId.val(0);
            $originalSlug.val('');
            $statusMessage.empty().removeClass('error success');
            slugEdited = false;
        }

        // Function to show status message
        function showMessage(type, message) {
            $statusMessage.text(message).addClass(type);
        }

        // Function to update a role in the table
        function updateRoleRow(role) {
            var $row = $('#role-' + role.id);

            if ($row.length) {
                $row.find('td:nth-child(1)').text(role.name);
                $row.find('td:nth-child(2)').text(role.slug);
            }
        }

        // Function to add a new role to the table
        function addRoleRow(role) {
            var $tbody = $('#roles-table-body');

            // Check if the table exists, if not create it
            if (!$tbody.length) {
                var $table = $('<table class="wp-list-table widefat fixed striped roles"></table>');
                var $thead = $('<thead><tr><th>Role Name</th><th>Role Slug</th><th>Created</th><th>Actions</th></tr></thead>');
                $tbody = $('<tbody id="roles-table-body"></tbody>');

                $table.append($thead).append($tbody);
                $('.ip-user-roles-list .notice').remove();
                $('.ip-user-roles-list').append($table);
            }

            // Create delete nonce
            var deleteNonce = Math.random().toString(36).substring(2, 15);

            // Create new row
            var $row = $('<tr id="role-' + role.id + '"></tr>');
            $row.append('<td>' + role.name + '</td>');
            $row.append('<td>' + role.slug + '</td>');
            $row.append('<td>' + role.created_at + '</td>');

            var $actions = $('<td></td>');
            $actions.append('<a href="#TB_inline?width=600&height=300&inlineId=ip-user-roles-form-modal" class="button button-small edit-role thickbox" data-id="' + role.id + '">Edit</a> ');
            $actions.append('<a href="' + ip_user_roles.admin_url + 'admin.php?page=ip-user-roles&action=delete&id=' + role.id + '&_wpnonce=' + deleteNonce + '" class="button button-small delete-role">Delete</a>');
            $row.append($actions);

            $tbody.prepend($row);

            // Rebind edit button event
            $row.find('.edit-role').on('click', function () {
                var roleId = $(this).data('id');
                resetForm();
                $modalTitle.text('Edit Role');
                $submitBtn.val('Update Role');

                $.ajax({
                    url: ip_user_roles.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'ip_user_roles_get_role',
                        nonce: ip_user_roles.nonce,
                        role_id: roleId
                    },
                    success: function (response) {
                        if (response.success) {
                            $roleId.val(response.data.id);
                            $roleName.val(response.data.name);
                            $roleSlug.val(response.data.slug);
                            $originalSlug.val(response.data.slug);

                            // Mark slug as edited for existing roles to prevent auto-generation
                            slugEdited = true;
                        } else {
                            showMessage('error', response.data.message);
                        }
                    },
                    error: function () {
                        showMessage('error', 'Error loading role data.');
                    }
                });
            });

            // Rebind delete button event
            $row.find('.delete-role').on('click', function (e) {
                if (!confirm(ip_user_roles.confirm_delete)) {
                    e.preventDefault();
                    return false;
                }
            });
        }

        // Add toggle all checkbox functionality for role capabilities
        $('#select-all-capabilities').on('click', function () {
            var isChecked = $(this).prop('checked');
            $('.capabilities-list input[type="checkbox"]').prop('checked', isChecked);
        });
    });
})(jQuery);