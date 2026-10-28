/**
 * @file
 * Role Expire js
 *
 * Set of jQuery related routines.
 */

(function (Drupal, drupalSettings, once) {
  Drupal.behaviors.role_expire = {
    attach(context, settings) {
      once('role_expire', 'html', context).forEach(function (element) {
        // No key change needed if Role Assign module is used.
        let rolesKey = 'roles';
        let textfieldId = '';
        let selector = '';

        jQuery('input.role-expire-role-expiry', context).each(function () {
          jQuery(this).parents('details.role-expire-details:first').hide();
        });

        if (jQuery('#edit-role-change').length > 0) {
          // Role Delegation module is used.
          rolesKey = 'role-change';
        }
        // eslint-disable-next-line prefer-template
        selector = '#edit-' + rolesKey + ' input.form-checkbox';

        jQuery(selector, context).each(function () {
          // eslint-disable-next-line prefer-template
          textfieldId = '#' + this.id.replace(rolesKey, 'role-expire');

          // Move all expiry date fields under corresponding checkboxes
          jQuery(this)
            .parent()
            .after(
              jQuery(textfieldId).parents('details.role-expire-details:first'),
            );

          // Show all expiry date fields that have checkboxes checked
          if (jQuery(this).attr('checked')) {
            jQuery(textfieldId)
              .parents('details.role-expire-details:first')
              .show();
          }
        });

        jQuery(selector, context).click(function () {
          // eslint-disable-next-line prefer-template
          textfieldId = '#' + this.id.replace(rolesKey, 'role-expire');

          jQuery(textfieldId)
            .parents('details.role-expire-details:first')
            .toggle();
        });
      });
    },
  };
})(Drupal, drupalSettings, once);
