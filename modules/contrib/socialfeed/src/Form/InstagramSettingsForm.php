<?php

namespace Drupal\socialfeed\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Instagram settings for this site.
 *
 * @package Drupal\socialfeed\Form
 */
class InstagramSettingsForm extends ConfigFormBase {

  /**
   * The Instagram API service.
   *
   * @var \Drupal\socialfeed\Services\InstagramApiService
   */
  protected $instagramApi;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->instagramApi = $container->get('socialfeed.instagram_api');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'instagram_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('socialfeed.instagram.settings');
    $redirect_uri = Url::fromRoute('socialfeed.instagram_auth', [], ['absolute' => TRUE])->toString();

    $form['header'] = [
      '#type' => 'markup',
      '#markup' => '<div class="messages messages--error"><h3>⚠️ Important: Instagram API Migration Required</h3>
        <p><strong>The Instagram Basic Display API was completely discontinued on December 4, 2024.</strong> This module now uses only the Instagram Graph API for Professional accounts.</p>
        <p>If you have existing Instagram feeds using personal accounts, <strong>they will no longer work</strong> and must be reconfigured with a Professional (Business or Creator) account.</p></div>' .
      '<div class="messages messages--warning">' . $this->t('<h3>Setup Instructions</h3>
        <h4>Requirements (As of December 4, 2024)</h4>
        <ul>
          <li><strong>Professional Instagram Account REQUIRED:</strong> Your Instagram account must be a Professional account (Business or Creator). Personal accounts are no longer supported by Instagram.</li>
          <li><strong>Account Conversion:</strong> If you currently have a personal Instagram account, you must convert it to a Business or Creator account. This is free and takes just a few minutes. <a href="@convert_url" target="_blank">Learn how to convert your account</a>.</li>
        </ul>
        <h4>Setup Steps</h4>
        <ol>
          <li><strong>Verify Professional Account:</strong> Ensure your Instagram account is set to Business or Creator mode.</li>
          <li><strong>Create/Configure Facebook App:</strong> Go to <a href="@fb_dev" target="_blank">Meta for Developers</a> and create a new app or use an existing one.</li>
          <li><strong>Get App ID & Secret:</strong> Navigate to Settings → Basic in your app dashboard. Copy your App ID and App Secret and paste them below.</li>
          <li><strong>Configure Redirect URI:</strong> Add the Redirect URI shown below to your app\'s "Valid OAuth Redirect URIs" field in Settings → Basic.</li>
          <li><strong>Generate Access Token:</strong> After saving the App ID and Secret, click the generated link below to authorize and get your Access Token. You MUST use your Professional Instagram account for this step.</li>
        </ol>
        <p>For detailed setup instructions, see the <a href="@guide" target="_blank">Instagram API with Facebook Login Guide</a>.</p>
        <p><strong>Note:</strong> This module uses direct API calls to Instagram\'s Graph API and does not require any external PHP libraries.</p>', [
          '@fb_dev' => 'https://developers.facebook.com/apps/',
          '@guide' => 'https://developers.facebook.com/docs/instagram-platform/instagram-api-with-facebook-login/',
          '@convert_url' => 'https://help.instagram.com/502981923235522',
        ]) . '</div>',
    ];

    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App ID'),
      '#description' => $this->t('Found in <a href="@link" target="_blank">Meta for Developers</a> → Your App → Settings → Basic. Also called "Instagram App ID".', [
        '@link' => 'https://developers.facebook.com/apps/',
      ]),
      '#default_value' => $config->get('client_id'),
      '#size' => 60,
      '#maxlength' => 100,
      '#required' => TRUE,
    ];
    $form['app_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App Secret'),
      '#description' => $this->t('Found in <a href="@link" target="_blank">Meta for Developers</a> → Your App → Settings → Basic. Also called "Instagram App Secret".', [
        '@link' => 'https://developers.facebook.com/apps/',
      ]),
      '#default_value' => $config->get('app_secret'),
      '#size' => 60,
      '#maxlength' => 100,
      '#required' => TRUE,
    ];

    $form['redirect_uri'] = [
      '#type' => 'item',
      '#title' => $this->t('Redirect URI'),
      '#description' => $this->t('Copy this URL and add it to "Valid OAuth Redirect URIs" in <a href="@link" target="_blank">Meta for Developers</a> → Your App → Settings → Basic.', [
        '@link' => 'https://developers.facebook.com/apps/',
      ]),
      '#markup' => '<strong>' . $redirect_uri . '</strong>',
    ];

    $token_message = $this->t('Once the App ID and Secret Key have been saved, a link to generate the Access Key will appear.');
    if ($config->get('client_id')) {
      $this->instagramApi->setCredentials(
        $config->get('client_id'),
        $config->get('app_secret'),
        $redirect_uri
      );

      $token_message = $this->t('<a href="@this" target="_blank">Login with Instagram to generate the Access Token</a>', [
        '@this' => Url::fromUri($this->instagramApi->getLoginUrl())->toString(),
      ]);
    }

    $form['access_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Access Token'),
      '#field_prefix' => '<div>' . $token_message . '</div>',
      '#description' => $this->t('This access token will automatically be renewed before the current one expires in 60 days.'),
      '#default_value' => $config->get('access_token'),
      '#size' => 60,
      '#maxlength' => 300,
    ];

    $form['picture_count'] = [
      '#type' => 'number',
      '#title' => $this->t('Default Picture Count'),
      '#default_value' => $config->get('picture_count'),
      '#size' => 60,
      '#maxlength' => 100,
      '#min' => 1,
    ];
    $form['video_thumbnail'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show video thumbnails instead of actual videos'),
      '#default_value' => $config->get('video_thumbnail'),
    ];
    $form['post_link'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show post URL'),
      '#default_value' => $config->get('post_link'),
    ];

    if ($config->get('access_token')) {
      $form['feed'] = [
        '#type' => 'item',
        '#title' => $this->t('Feed URL'),
        '#markup' => $this->t('https://graph.instagram.com/me/media?fields=id,media_type,media_url,username,timestamp&limit=@picture_count&access_token=@access_token',
          [
            '@access_token' => $config->get('access_token'),
            '@picture_count' => $config->get('picture_count'),
          ]
        ),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('socialfeed.instagram.settings');
    foreach ($form_state->getValues() as $key => $value) {
      $config->set($key, $value);
    }
    $config->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'socialfeed.instagram.settings',
    ];
  }

}
