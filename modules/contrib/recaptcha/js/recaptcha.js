/**
 * @file
 * Contains the definition of the behaviour recaptcha.
 */

(function ($, Drupal) {
  function renderWidgets(context) {
    $('.g-recaptcha', context || document).each(function () {
      if (
        typeof grecaptcha === 'undefined' ||
        typeof grecaptcha.render !== 'function'
      ) {
        return;
      }
      if ($(this).closest('body').length > 0) {
        if (!$(this).hasClass('recaptcha-processed') && $(this).children().length === 0) {
          try {
            grecaptcha.render(this, $(this).data());
            $(this).addClass('recaptcha-processed');
          } catch (e) {
            $(this).addClass('recaptcha-processed');
          }
        }
      }
    });
  }

  Drupal.behaviors.recaptcha = {
    attach(context) {
      renderWidgets(context);
      if (typeof grecaptcha !== 'undefined' && typeof grecaptcha.ready === 'function') {
        grecaptcha.ready(function () {
          renderWidgets(context);
        });
      }
    },
  };

  window.drupalRecaptchaOnload = function () {
    renderWidgets(document);
  };
})(jQuery, Drupal);
