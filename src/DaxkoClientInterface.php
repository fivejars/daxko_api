<?php

namespace Drupal\daxko_api;

/**
 * Defines an interface for Daxko API client.
 */
interface DaxkoClientInterface {

  /**
   * Makes requests to the Daxko API.
   *
   * @param string $method
   *   HTTP method GET, POST, PUT, etc.
   * @param string $uri
   *   The endpoint URI.
   * @param array $options
   *   The array of request options (the same as for Guzzle client).
   *
   * @return array
   *   The array with API response.
   */
  public function request($method, $uri = '', array $options = []);

}
