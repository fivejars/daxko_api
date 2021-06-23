<?php

namespace Drupal\daxko_api;

/**
 * Base Daxko Endpoint.
 */
class DaxkoEndpointBase {

  /**
   * The Daxko API client.
   *
   * @var \Drupal\daxko_api\DaxkoClient
   */
  protected $client;

  /**
   * DaxkoEndpointBase constructor.
   *
   * @param \Drupal\daxko_api\DaxkoClient $client
   *   The Daxko API client.
   */
  public function __construct(DaxkoClient $client) {
    $this->client = $client;
  }

}
