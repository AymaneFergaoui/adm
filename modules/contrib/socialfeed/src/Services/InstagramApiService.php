<?php

namespace Drupal\socialfeed\Services;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Instagram API Service for handling Instagram Graph API operations.
 *
 * IMPORTANT: The Instagram Basic Display API was deprecated on
 * September 4, 2024 and completely discontinued on December 4, 2024.
 * This service uses the Instagram Graph API which requires Professional
 * (Creator or Business) accounts. Personal Instagram accounts are no
 * longer supported.
 *
 * @package Drupal\socialfeed\Services
 */
class InstagramApiService {

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Instagram's application api key.
   *
   * @var string
   */
  protected $appId;

  /**
   * Instagram application api secret.
   *
   * @var string
   */
  protected $appSecret;

  /**
   * Instagram application redirect Uri.
   *
   * @var string
   */
  protected $redirectUri;

  /**
   * Instagram API base URL.
   *
   * @var string
   */
  const API_BASE_URL = 'https://api.instagram.com';

  /**
   * Instagram Graph API base URL.
   *
   * @var string
   */
  const GRAPH_BASE_URL = 'https://graph.instagram.com';

  /**
   * Facebook Graph API base URL.
   *
   * @var string
   */
  const FACEBOOK_GRAPH_BASE_URL = 'https://graph.facebook.com';

  /**
   * Graph API version (used with Facebook Graph endpoints).
   *
   * @var string
   */
  protected $graphVersion = 'v18.0';

  /**
   * InstagramApiService constructor.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(ClientInterface $http_client, LoggerChannelFactoryInterface $logger_factory) {
    $this->httpClient = $http_client;
    $this->logger = $logger_factory->get('socialfeed');
  }

  /**
   * Set credentials.
   *
   * @param string $app_id
   *   Instagram App ID.
   * @param string $app_secret
   *   Instagram App Secret.
   * @param string $redirect_uri
   *   Instagram Redirect URI.
   */
  public function setCredentials($app_id, $app_secret, $redirect_uri) {
    $this->appId = $app_id;
    $this->appSecret = $app_secret;
    $this->redirectUri = $redirect_uri;
  }

  /**
   * Set the Facebook Graph API version (e.g., v18.0).
   *
   * @param string $version
   *   The Graph API version.
   */
  public function setGraphVersion(string $version) {
    $this->graphVersion = $version ?: $this->graphVersion;
  }

  /**
   * Get the login URL for Instagram OAuth.
   *
   * NOTE: This uses the Instagram Login method which requires users to have
   * a Professional (Creator or Business) Instagram account. Personal accounts
   * are no longer supported by Instagram as of December 4, 2024.
   *
   * @return string
   *   The login URL.
   *
   * @see https://developers.facebook.com/docs/instagram-platform/instagram-api-with-facebook-login/
   */
  public function getLoginUrl() {
    $params = [
      'client_id' => $this->appId,
      'redirect_uri' => $this->redirectUri,
      'scope' => 'user_profile,user_media',
      'response_type' => 'code',
    ];

    return self::API_BASE_URL . '/oauth/authorize?' . http_build_query($params);
  }

  /**
   * Exchange authorization code for access token.
   *
   * @param string $code
   *   The authorization code.
   *
   * @return string|null
   *   The short-lived access token or NULL on failure.
   */
  public function getOauthToken($code) {
    try {
      $response = $this->httpClient->request('POST', self::API_BASE_URL . '/oauth/access_token', [
        'form_params' => [
          'client_id' => $this->appId,
          'client_secret' => $this->appSecret,
          'grant_type' => 'authorization_code',
          'redirect_uri' => $this->redirectUri,
          'code' => $code,
        ],
      ]);

      $data = json_decode($response->getBody()->getContents(), TRUE);
      return $data['access_token'] ?? NULL;
    }
    catch (GuzzleException $e) {
      $this->logger->error('Failed to get OAuth token: @message', ['@message' => $e->getMessage()]);
      return NULL;
    }
  }

  /**
   * Exchange short-lived token for long-lived token.
   *
   * @param string $access_token
   *   The short-lived access token.
   *
   * @return string|null
   *   The long-lived access token or NULL on failure.
   */
  public function getLongLivedToken($access_token) {
    try {
      $response = $this->httpClient->request('GET', self::GRAPH_BASE_URL . '/access_token', [
        'query' => [
          'grant_type' => 'ig_exchange_token',
          'client_secret' => $this->appSecret,
          'access_token' => $access_token,
        ],
      ]);

      $data = json_decode($response->getBody()->getContents(), TRUE);
      return $data['access_token'] ?? NULL;
    }
    catch (GuzzleException $e) {
      $this->logger->error('Failed to get long-lived token: @message', ['@message' => $e->getMessage()]);
      return NULL;
    }
  }

  /**
   * Refresh a long-lived access token.
   *
   * @param string $access_token
   *   The current long-lived access token.
   *
   * @return string|null
   *   The refreshed access token or NULL on failure.
   */
  public function refreshToken($access_token) {
    try {
      $response = $this->httpClient->request('GET', self::GRAPH_BASE_URL . '/refresh_access_token', [
        'query' => [
          'grant_type' => 'ig_refresh_token',
          'access_token' => $access_token,
        ],
      ]);

      $data = json_decode($response->getBody()->getContents(), TRUE);
      return $data['access_token'] ?? NULL;
    }
    catch (GuzzleException $e) {
      $this->logger->error('Failed to refresh token: @message', ['@message' => $e->getMessage()]);
      return NULL;
    }
  }

  /**
   * Get Instagram user ID from access token.
   *
   * @param string $access_token
   *   The access token.
   *
   * @return string|null
   *   The Instagram user ID or NULL on failure.
   */
  public function getInstagramUserId($access_token) {
    try {
      $response = $this->httpClient->request('GET', self::GRAPH_BASE_URL . '/me', [
        'query' => [
          'fields' => 'id,username',
          'access_token' => $access_token,
        ],
      ]);

      $data = json_decode($response->getBody()->getContents(), TRUE);
      return $data['id'] ?? NULL;
    }
    catch (GuzzleException $e) {
      $this->logger->error('Failed to get Instagram user ID: @message', ['@message' => $e->getMessage()]);
      return NULL;
    }
  }

  /**
   * Get user media from Instagram.
   *
   * @param string $access_token
   *   The access token.
   * @param int $limit
   *   Number of media items to retrieve.
   * @param string $user_id
   *   The user ID (defaults to 'me').
   *
   * @return object|null
   *   The response object or NULL on failure.
   */
  public function getUserMedia($access_token, $limit = 25, $user_id = 'me') {
    try {
      $response = $this->httpClient->request('GET', self::GRAPH_BASE_URL . '/' . $user_id . '/media', [
        'query' => [
          'fields' => 'id,caption,media_type,media_url,permalink,thumbnail_url,timestamp,username,children{media_url,media_type}',
          'access_token' => $access_token,
          'limit' => $limit,
        ],
      ]);

      $data = json_decode($response->getBody()->getContents());
      return $data;
    }
    catch (GuzzleException $e) {
      $this->logger->error('Failed to get user media: @message', ['@message' => $e->getMessage()]);
      return NULL;
    }
  }

}
