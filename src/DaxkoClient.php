<?php

declare(strict_types=1);

namespace Drupal\daxko_api;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Logger\LoggerChannelInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Daxko Partners API client.
 */
class DaxkoClient implements DaxkoClientInterface {

  const API_BASE_URL = 'https://api.partners.daxko.com/';

  const TOKEN_GRANT_TYPE = 'client_credentials';

  /**
   * The config factory.
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The HTTP client factory.
   */
  protected ClientFactory $httpClientFactory;

  /**
   * The cache backend.
   */
  protected CacheBackendInterface $cache;

  /**
   * Logger channel.
   */
  protected LoggerChannelInterface|LoggerChannel $logger;

  /**
   * Daxko configuration.
   */
  protected ImmutableConfig $config;

  /**
   * Daxko HTTP client.
   */
  protected Client $client;

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
   * Retrieves the Daxko OAuth access token.
   *
   * @return string|null
   *   The access token, or NULL on failure.
   */
  protected function getAccessToken(): ?string {
    $cache_key = 'daxko.access_token';
    $cached = $this->cache->get($cache_key);
    if ($cached) {
      return $cached->data;
    }

    return $this->refreshToken();
  }

  /**
   * Refreshes the Daxko OAuth access token.
   *
   * @return string|null
   *   The new access token, or NULL on failure.
   */
  protected function refreshToken(): ?string {
    $access_token = NULL;

    try {
      $form_params = [
        'client_id' => $this->config->get('client_id'),
        'client_secret' => $this->config->get('client_secret'),
        'scope' => $this->config->get('scope'),
        'grant_type' => static::TOKEN_GRANT_TYPE,
      ];

      $options = [
        'headers' => ['Content-Type' => 'application/json'],
        'json' => $form_params,
      ];

      $response = $this->client->request('POST', '/auth/token', $options);
      $data = Json::decode((string) $response->getBody());

      if (!empty($data['access_token']) && !empty($data['expires_in'])) {
        $expire = time() + (int) $data['expires_in'];
        $this->cache->set('daxko.access_token', $data['access_token'], $expire);
        $this->logger->info('The new access token has been granted.');

        $access_token = $data['access_token'];
      }
    }
    catch (GuzzleException $e) {
      $this->logger->error('Unable to get Daxko access token: @message', ['@message' => $e->getMessage()]);
    }

    return $access_token;
  }

  /**
   * {@inheritdoc}
   */
  public function request($method, $uri = '', array $options = []): array {
    $data = [];

    try {
      $access_token = $this->getAccessToken();
      if (!$access_token) {
        return $data;
      }

      $default_headers = [
        'Authorization' => "Bearer {$access_token}",
        'Accept' => 'application/json',
      ];

      $options['headers'] = array_merge(
        $default_headers,
        $options['headers'] ?? []
      );

      $response = $this->client->request($method, $uri, $options);
      $data = Json::decode((string) $response->getBody());
    }
    catch (GuzzleException $e) {
      $this->logger->error('Failed to call Daxko API method @method @uri: @message', [
        '@method' => $method,
        '@uri' => $uri,
        '@message' => $e->getMessage(),
      ]);
    }

    return (array) $data;
  }

}
