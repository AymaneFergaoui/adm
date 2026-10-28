<?php

namespace Drupal\socialfeed\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\socialfeed\Services\InstagramApiService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for socialfeed routes.
 *
 * @package Drupal\socialfeed\Controller
 */
class InstagramAuthController extends ControllerBase {

  /**
   * The Instagram API service.
   *
   * @var \Drupal\socialfeed\Services\InstagramApiService
   */
  protected $instagramApi;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * InstagramAuthController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration service.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param \Drupal\socialfeed\Services\InstagramApiService $instagram_api
   *   The Instagram API service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Request $request, InstagramApiService $instagram_api) {
    $this->configFactory = $config_factory;
    $this->currentRequest = $request;
    $this->instagramApi = $instagram_api;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('socialfeed.instagram_api')
    );
  }

  /**
   * Get an Instagram access token.
   */
  public function accessToken() {
    $code = $this->currentRequest->query->get('code');
    $message = 'Something went wrong. The access token could not be created.';
    $token = '';

    if ($code) {
      $config = $this->configFactory->getEditable('socialfeed.instagram.settings');

      $this->instagramApi->setCredentials(
        $config->get('client_id'),
        $config->get('app_secret'),
        Url::fromRoute('socialfeed.instagram_auth', [], ['absolute' => TRUE])->toString()
      );

      // Get the short-lived access token (valid for 1 hour).
      $token = $this->instagramApi->getOAuthToken($code);

      // Exchange this token for a long lived token (valid for 60 days).
      if ($token) {
        $token = $this->instagramApi->getLongLivedToken($token);
        $config->set('access_token', $token);
        $config->set('access_token_date', time());
        $config->save();

        $message = 'Your Access Token has been generated and saved.';
      }
    }

    return [
      '#markup' => $this->t('@message @token', [
        '@message' => $message,
        '@token' => $token,
      ]),
    ];
  }

}
