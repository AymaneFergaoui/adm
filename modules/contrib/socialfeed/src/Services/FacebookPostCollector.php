<?php

namespace Drupal\socialfeed\Services;

use FacebookAds\Api as Facebook;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\Page;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collects posts from a Facebook page using the Facebook Graph API.
 *
 * @package Drupal\socialfeed
 */
class FacebookPostCollector {

  /**
   * The Field names to retrieve from Facebook.
   *
   * @var array
   */
  protected array $fields = [
    'permalink_url',
    'message',
    'created_time',
    'picture',
    'status_type',
  ];

  /**
   * The Facebook's App ID.
   *
   * @var string
   */
  protected string $appId;

  /**
   * The Facebook's App Secret.
   *
   * @var string
   */
  protected string $appSecret;

  /**
   * The Facebook User Token.
   *
   * @var ?string
   */
  protected ?string $userToken;

  /**
   * The Facebook Page Name.
   *
   * @var ?string
   */
  protected ?string $pageName;

  /**
   * The Facebook Client.
   *
   * @var ?\FacebookAds\Api
   */
  protected ?Facebook $facebook;

  /**
   * FacebookPostCollector constructor.
   *
   * @param string $appId
   *   The Facebook's App ID.
   * @param string $appSecret
   *   The Facebook's App Secret.
   * @param ?string $userToken
   *   The Facebook User Token.
   * @param ?string $pageName
   *   The Facebook Page Name.
   * @param ?\FacebookAds\Api $facebook
   *   The Facebook Client.
   */
  public function __construct(string $appId, string $appSecret, ?string $userToken = NULL, ?string $pageName = NULL, ?Facebook $facebook = NULL) {
    $this->appId = $appId;
    $this->appSecret = $appSecret;
    $this->userToken = $userToken;
    $this->pageName = $pageName;
    $this->facebook = $facebook;
    $this->setFacebookClient();
  }

  /**
   * Sets the Facebook client.
   */
  public function setFacebookClient(): void {
    if (NULL === $this->facebook) {
      $this->facebook = Facebook::init($this->appId, $this->appSecret, $this->defaultAccessToken());
    }
  }

  /**
   * Fetches Facebook posts from a given feed.
   *
   * @param string $pageId
   *   The ID of the page to fetch results from.
   * @param string $postTypes
   *   The post types to filter for.
   * @param int $numPosts
   *   The number of posts to return.
   *
   * @return array
   *   An array of Facebook posts.
   */
  public function getPosts(string $pageId, string $postTypes, int $numPosts = 10): array {
    $posts = [];
    $postCount = 0;
    $params = [];
    $this->facebook->setLogger(new CurlLogger());
    do {
      $response = (new Page($pageId))->getFeed($this->fields, $params)->getLastResponse();
      // Ensure not caught in an infinite loop if there's no next page.
      $url = NULL;
      if ($response->getStatusCode() == Response::HTTP_OK) {
        $data = $response->getContent();
        $posts = array_merge($this->extractFacebookFeedData($postTypes, $data['data']), $posts);
        $postCount = count($posts);
        if ($postCount < $numPosts && isset($data['paging']['next'])) {
          $url = $data['paging']['next'];
        }
      }
    } while ($postCount < $numPosts && NULL != $url);
    return array_slice($posts, 0, $numPosts);
  }

  /**
   * Extracts information from the Facebook feed.
   *
   * @param string $postTypes
   *   The type of posts to extract.
   * @param array $data
   *   An array of data to extract information from.
   *
   * @return array
   *   An array of posts.
   */
  protected function extractFacebookFeedData(string $postTypes, array $data): array {
    $posts = array_map(function ($post) {
      return $post;
    }, $data);

    // Following is TRUE, when user has asked for specific post type in Facebook
    // settings form instead of all posts.
    if ($postTypes !== '1') {
      return array_filter($posts, function ($post) use ($postTypes) {
        if (!empty($post['status_type'])) {
          return $post['status_type'] === $postTypes;
        }
      });
    }
    return $posts;
  }

  /**
   * Generates the Facebook access token.
   *
   * @return string
   *   The access token.
   */
  protected function defaultAccessToken(): string {
    $config = \Drupal::service('config.factory')->getEditable('socialfeed.facebook.settings');
    $permanentToken = $config->get('page_permanent_token');
    if (empty($permanentToken)) {
      $graphVersion = 'v14.0';
      $args = [
        'usertoken' => $this->userToken,
        'appid' => $this->appId,
        'appsecret' => $this->appSecret,
        'pageid' => $this->pageName,
      ];
      $client = \Drupal::httpClient();
      // Token.
      $request = $client->request('GET', "https://graph.facebook.com/$graphVersion/oauth/access_token?grant_type=fb_exchange_token&client_id={$args['appid']}&client_secret={$args['appsecret']}&fb_exchange_token={$args['usertoken']}");
      $request = json_decode($request->getBody()->getContents());
      $longToken = $request->access_token;
      // User ID.
      $request = $client->request('GET', "https://graph.facebook.com/$graphVersion/me?access_token=$longToken");
      $request = json_decode($request->getBody()->getContents());
      $accountId = $request->id;
      // Page ID.
      $request = $client->request('GET', "https://graph.facebook.com/$graphVersion/{$args['pageid']}?fields=id&access_token=$longToken");
      $request = json_decode($request->getBody()->getContents());
      $pageId = $request->id;
      $config->set('page_id', $pageId)->save();
      // Permanent Token.
      $request = $client->request('GET', "https://graph.facebook.com/$graphVersion/$accountId/accounts?access_token=$longToken");
      $request = json_decode($request->getBody()->getContents());
      foreach ($request->data as $responseData) {
        if ($responseData->id == $pageId) {
          $config->set('page_permanent_token', $responseData->access_token)->save();
          return $responseData->access_token;
        }
      }
    }
    return $permanentToken;
  }

}
