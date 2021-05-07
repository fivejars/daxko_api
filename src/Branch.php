<?php

namespace Drupal\daxko_api;

/**
 * Branch related endpoints.
 */
class Branch extends DaxkoEndpointBase {

  /**
   * Loads the list of all branches.
   *
   * @return array
   *   The list of loaded branches.
   */
  public function loadAll() {
    $response = $this->client->request('GET', '/v3/branches');
    return $response['branches'] ?? [];
  }

  /**
   * Loads information about a specific branch.
   *
   * @param string $id
   *   The ID of the branch.
   *
   * @return array
   *   The branch info.
   */
  public function load($id) {
    return $this->client->request('GET', '/v3/branches/' . $id);
  }

}
