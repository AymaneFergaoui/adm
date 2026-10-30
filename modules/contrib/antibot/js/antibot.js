/**
 * @file
 * Unlock protected forms.
 *
 * This works by resetting the form action to the path that It should be as well
 * as injecting the secret form key, only if the current user is verified to be
 * human which is done by waiting for a user interaction (mouse, touch,
 * keyboard, focus, click) or, at the latest, when the form is submitted by a
 * real browser with JavaScript enabled.
 */

((Drupal, drupalSettings) => {
  drupalSettings.antibot = drupalSettings.antibot || {};
  Drupal.antibot = Drupal.antibot || {};

  /**
   * Unlocks a single form element using its antibot config.
   */
  Drupal.antibot.unlockForm = (config) => {
    const form = document.getElementById(config.id);
    if (!form) {
      return;
    }
    const action = form.getAttribute('data-action');
    if (action) {
      form.setAttribute('action', action);
    }
    const input = form.querySelector('input[name="antibot_key"]');
    if (input && config.key) {
      input.value = config.key
        .split('')
        .reverse()
        .join('')
        .match(/.{1,2}/g)
        .map((value) => value.split('').reverse().join(''))
        .join('');
    }
  };

  /**
   * Unlock all locked forms.
   */
  Drupal.antibot.unlockForms = () => {
    if (drupalSettings.antibot && drupalSettings.antibot.forms !== undefined) {
      Object.values(drupalSettings.antibot.forms).forEach((config) => {
        Drupal.antibot.unlockForm(config);
      });
    }
    drupalSettings.antibot.human = true;
  };

  let listenersAdded = false;

  Drupal.behaviors.antibot = {
    attach(context, settings) {
      drupalSettings = settings;
      drupalSettings.antibot = drupalSettings.antibot || {};

      // If the visitor was already verified (e.g. forms added via AJAX),
      // unlock the new forms immediately.
      if (drupalSettings.antibot.human) {
        Drupal.antibot.unlockForms();
      }

      if (listenersAdded) {
        return;
      }
      listenersAdded = true;

      const unlock = () => Drupal.antibot.unlockForms();
      const opts = { passive: true, capture: true };
      [
        'mousemove',
        'mousedown',
        'pointerdown',
        'touchstart',
        'touchmove',
        'click',
        'focusin',
        'input',
        'change',
        'wheel',
        'scroll',
      ].forEach((evt) => {
        document.addEventListener(evt, unlock, opts);
      });
      document.addEventListener(
        'keydown',
        (e) => {
          if (e.code === 'Tab' || e.code === 'Enter' || e.key) {
            unlock();
          }
        },
        opts,
      );

      // Last line of defence: unlock right before a real submit.
      document.addEventListener(
        'submit',
        (e) => {
          const form = e.target;
          if (form && form.classList && form.classList.contains('antibot')) {
            Drupal.antibot.unlockForms();
          }
        },
        true,
      );
    },
  };
})(Drupal, drupalSettings);
