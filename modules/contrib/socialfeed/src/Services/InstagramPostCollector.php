<?php

namespace Drupal\socialfeed\Services;

/**
 * The collector class for Instagram.
 *
 * @package Drupal\socialfeed
 */
class InstagramPostCollector {

  /**
   * Instagram's application api key.
   *
   * @var string
   */
  protected $apiKey;

  /**
   * Instagram application api secret.
   *
   * @var string
   */
  protected $apiSecret;

  /**
   * Instagram application redirect Uri.
   *
   * @var string
   */
  protected $redirectUri;

  /**
   * Instagram's application access token.
   *
   * @var string
   */
  protected $accessToken;

  /**
   * Instagram API service.
   *
   * @var \Drupal\socialfeed\Services\InstagramApiService
   */
  protected $instagramApi;

  /**
   * InstagramPostCollector constructor.
   *
   * @param string $apiKey
   *   Instagram API key.
   * @param string $apiSecret
   *   Instagram API secret.
   * @param string $redirectUri
   *   Instagram Redirect URI.
   * @param string $accessToken
   *   Instagram Access token.
   * @param \Drupal\socialfeed\Services\InstagramApiService $instagram_api
   *   Instagram API service.
   */
  public function __construct(string $apiKey, string $apiSecret, string $redirectUri, string $accessToken, InstagramApiService $instagram_api) {
    $this->apiKey = $apiKey;
    $this->apiSecret = $apiSecret;
    $this->redirectUri = $redirectUri;
    $this->accessToken = $accessToken;
    $this->instagramApi = $instagram_api;
  }

  /**
   * Retrieves user's posts.
   *
   * @param int $numPosts
   *   Number of posts to get.
   * @param string $user_id
   *   The user id from whom to get media. Defaults to the user that the access
   *   token was created for.
   *
   * @return array
   *   An array of Instagram posts.
   */
  public function getPosts($numPosts, $user_id = 'me') {
    $posts = [];
    $response = $this->instagramApi->getUserMedia($this->accessToken, $numPosts, $user_id);
    if (isset($response->data)) {
      $posts = array_map(function ($post) {
        return [
          'raw' => $post,
          'media_url' => $post->media_url,
          'type' => $post->media_type,
          'children' => ($post->children ?? NULL),
        ];
      }, $response->data);
    }
    return $posts;
  }

}
