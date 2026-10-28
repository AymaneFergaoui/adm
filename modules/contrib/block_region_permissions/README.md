# Block Region Permission

The Block Region Permissions module adds permissions for administering "blocks"
based on each theme's regions. The "Administer blocks" permission normally
manages this entity on the "Block layout" page.

For a full description of the module, visit the
[project page](https://www.drupal.org/project/block_region_permissions).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/block_region_permissions).


## Table of contents

- Requirements
- Recommended modules
- Installation
- Configuration
- Troubleshooting
- Maintainers


## Requirements

This module requires no modules outside of Drupal core.


## Recommended modules

[Block Content Permissions](https://www.drupal.org/project/block_content_permissions):
Adds permissions for administering the "Custom block library" pages.


## Installation

Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).


## Configuration

1. Enable the module at Administration > Extend.
1. Configure user permissions at Administration > People > Permissions:

    - Block > Administer blocks

      (Required) Allows management of blocks. **Warning:** This permission
      grants access to block pages not managed by this module. Use the
      recommended modules to restrict the rest.

    - Block Region Permissions > Administer: [*theme*] - [*region*]

      For a specific theme's region: view it on the layout page, manage its
      blocks, and select it in the region fields.

    - Contextual Links > Use contextual links

      Allows use of operational links near the rendered blocks. This module
      hides links accordingly.

    - Quick Edit > Access in-place editing

      Allows use of "in-place" editing near the rendered blocks. Requires the
      "Use contextual links" permission. This module restricts access and hides
      the link accordingly.

    - System > Use the administration pages and help

      Allows use of admin pages during navigation.

    - System > View the administration theme

      Allows use of administrative theme for aesthetics.

    - Toolbar > Use the toolbar

      Allows use of toolbar during navigation.


## Troubleshooting

List of pages that should deny access depending on permissions.

In-place Quick edit.

"Block layout" pages:
- Block layout:
    - Path: /admin/structure/block
    - Route: block.admin_display
    - Path: /admin/structure/block/list/{theme}
    - Route: block.admin_display_theme
- Configure block:
    - Path: /admin/structure/block/manage/{block}
    - Route: entity.block.edit_form
- Delete block:
    - Path: /admin/structure/block/manage/{block}/delete
    - Route: entity.block.delete_form
- Disable block:
    - Path: /admin/structure/block/manage/{block}/disable
    - Route: entity.block.disable
- Enable block:
    - Path: /admin/structure/block/manage/{block}/enable
    - Route: entity.block.enable
- Place block search:
    - Path: /admin/structure/block/library/{theme}
    - Route: block.admin_library
- Place block configure:
    - Path: /admin/structure/block/add/{plugin_id}/{theme}
    - Route: block.admin_add


## Maintainers

- Joshua Roberson - [joshua.roberson](https://www.drupal.org/u/joshuaroberson)
