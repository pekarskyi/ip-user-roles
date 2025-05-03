# IP User Roles

A WordPress plugin that allows creating custom user roles with subscriber-level permissions and supports multiple role assignments to users.

[Українською](/README_UA.md)

## Description

IP User Roles extends WordPress's built-in user role management system by providing a simple yet powerful interface to create custom roles and assign multiple roles to users. With this plugin, site administrators can create specialized roles for different team members without having to install complex user management systems.

### Key Features

* **Custom Role Creation** - Create custom user roles with subscriber-level permissions
* **Multiple Role Assignment** - Assign multiple roles to a single user
* **User-Friendly Interface** - Intuitive interface integrated into the WordPress admin
* **Transliteration Support** - Automatic transliteration of Cyrillic characters to Latin for role slugs
* **Clean Uninstallation** - Option to remove all plugin data upon uninstallation

## Requirements

* WordPress 6.0.0 or higher
* PHP 7.4 or higher

## Installation

1. Upload the `ip-user-roles` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to 'IP User Roles' in the admin menu to manage custom roles

## Usage

### Creating a New Role

1. Navigate to the IP User Roles page in the admin menu
2. Click "Add New Role"
3. Enter a role name and the slug will be automatically generated (you can modify it if needed)
4. Click "Create Role"

### Editing Roles

1. Find the role you want to edit in the roles list
2. Click "Edit" next to the role name
3. Update the role details
4. Click "Update Role"

### Deleting Roles

1. Find the role you want to delete in the roles list
2. Click "Delete" next to the role name
3. Confirm the deletion when prompted

### Assigning Multiple Roles to a User

1. Edit a user from the WordPress Users screen
2. Scroll down to the "Multiple Roles" section
3. Check all the roles you want to assign to the user
4. Update the user profile

### Plugin Settings

1. Navigate to IP User Roles > Settings in the admin menu
2. Configure whether to delete database tables upon plugin uninstallation
3. Save settings

## Structure

```
ip-user-roles/
├── admin/                     # Admin-related files
│   ├── class-ip-user-roles-admin.php  # Admin functionality
│   └── partials/              # Admin templates
│       ├── ip-user-roles-admin-display.php
│       ├── ip-user-roles-admin-edit-role.php
│       ├── ip-user-roles-admin-new-role.php
│       └── ip-user-roles-admin-settings.php
├── assets/                    # Front-end assets
│   ├── css/
│   │   └── ip-user-roles.css  # Plugin styles
│   └── js/
│       └── ip-user-roles.js   # Plugin JavaScript
├── includes/                  # Core plugin files
│   ├── class-ip-user-roles-activator.php    # Activation functionality
│   ├── class-ip-user-roles-deactivator.php  # Deactivation functionality
│   ├── class-ip-user-roles-handler.php      # Role management
│   ├── class-ip-user-roles-profile.php      # User profile integration
│   ├── class-ip-user-roles-uninstaller.php  # Uninstallation functionality
│   └── class-ip-user-roles.php              # Main plugin class
├── lang/                      # Translation files
│   └── ipuroles.pot           # Translation template
├── README.md                  # English documentation (this file)
├── README_UA.md               # Ukrainian documentation 
└── ip-user-roles.php          # Main plugin file
```

## Frequently Asked Questions

**Q: Can I edit the capabilities of custom roles?**
A: Currently, all custom roles inherit capabilities from the subscriber role. Enhanced capability management will be added in future versions.

**Q: What happens to users with a custom role if I delete that role?**
A: Users with the deleted role will be assigned the default WordPress role (usually Subscriber).

**Q: Is this plugin compatible with other user management plugins?**
A: IP User Roles is designed to be compatible with standard WordPress functions. However, some advanced user management plugins might conflict with the multiple role functionality.

## Changelog

1.0.0 - 30-04-2025
- Start Release