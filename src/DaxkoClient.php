<?php

namespace Drupal\daxko_api;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Daxko APIv3 client.
 */
class DaxkoClient implements DaxkoClientInterface {

  const API_BASE_URL = 'https://api.daxko.com';

  const TOKEN_GRANT_TYPE = 'client_credentials';

  const GRANT_TYPE = 'password';

  const SCOPE = 'member:auto_login';

  /**
   * The config.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The HTTP client factory.
   *
   * @var \Drupal\Core\Http\ClientFactory
   */
  protected $httpClientFactory;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  protected $logger;

  /**
   * Daxko configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Daxko HTTP client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * Authentication constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Http\ClientFactory $httpClientFactory
   *   The HTTP client factory.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $loggerChannel
   *   Logger channel.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ClientFactory $httpClientFactory, CacheBackendInterface $cache, LoggerChannelInterface $loggerChannel) {
    $this->configFactory = $config_factory;
    $this->cache = $cache;
    $this->httpClientFactory = $httpClientFactory;
    $this->logger = $loggerChannel;

    $client_config = [
      'base_uri' => static::API_BASE_URL,
    ];

    $this->config = $this->configFactory->get('daxko_api.settings');
    $delay = $this->config->get('delay');

    if ($delay) {
      $client_config['delay'] = $delay;
    }

    $this->client = $this->httpClientFactory->fromOptions($client_config);

  }

  /**
   * Get Daxko OAuth access token.
   *
   * @return string|null
   *   The Access token.
   */
  protected function getAccessToken() {
    $token = $this->cache->get('daxko.access_token');
    if ($token) {
      return $token->data;
    }

    $token = $this->refreshToken();
    return $token;
  }

  /**
   * Refreshes Daxko OAuth access token.
   *
   * @return string|null
   *   Access token or null.
   */
  protected function refreshToken() {
    $access_token = NULL;

    try {
      // According to the Daxko API V3 docs we should username as client_id.
      // And send client_id in the scope param.
      $form_params = [
        'client_id' => $this->config->get('username'),
        'client_secret' => $this->config->get('password'),
        'grant_type' => static::TOKEN_GRANT_TYPE,
        'scope' => 'client:' . $this->config->get('client_id'),
      ];

      $headers = [
        'Authorization' => "Bearer " . $this->config->get('refresh_token'),
      ];

      $options = ['form_params' => $form_params, 'headers' => $headers];
      $response = $this->client->request('POST', '/v3/partners/oauth2/token', $options);

      $body = $response->getBody();
      $data = Json::decode((string) $body);
      if (isset($data['access_token']) && isset($data['expires_in'])) {
        $expire = time() + $data['expires_in'];
        $this->cache->set('daxko.access_token', $data['access_token'], $expire);
        $this->logger->info('The new access token has been granted.');

        $access_token = $data['access_token'];
      }
    }
    catch (GuzzleException $e) {
      $message = 'Unable to get Daxko Access token with the message %message';
      $this->logger->error($message, ['%message' => $e->getMessage()]);
    }

    return $access_token;
  }

  /**
   * {@inheritdoc}
   */
  public function request($method, $uri = '', array $options = []) {
    $data = [];
    try {
      $access_token = $this->getAccessToken();

      if (!$access_token) {
        return $data;
      }

      $default_headers = [
        'Authorization' => "Bearer " . $access_token,
        'username' => $this->config->get('user'),
        'password' => $this->config->get('pass'),
        'grant_type' => static::GRANT_TYPE,
        'scope' => static::SCOPE,
      ];

      if (isset($options['headers'])) {
        $options['headers'] = array_merge($default_headers, $options['headers']);
      }
      else {
        $options['headers'] = $default_headers;
      }

      $response = $this->client->request($method, $uri, $options);

      $body = (string) $response->getBody();
      $data = (array) Json::decode($body);
    }
    catch (GuzzleException $e) {
      $message = 'Failed to call Daxko API method %method %uri with the message %message';
      $params = [
        '%method' => $method,
        '%message' => $e->getMessage(),
        '%uri' => $uri,
      ];
      $this->logger->error($message, $params);
    }

    return $data;
  }

}
