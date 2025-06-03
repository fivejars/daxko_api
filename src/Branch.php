<?php

declare(strict_types=1);

namespace Drupal\daxko_api;

/**
 * Provides methods for retrieving branch information from the Daxko API.
 */
class Branch extends DaxkoEndpointBase {

  /**
   * Loads the list of all branches.
   *
   * @return array
   *   The list of loaded branches.
   */
  public function loadAll(): array {
    $response = $this->client->request('GET', '/api/v1/branches');
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
   *
   * @throws \InvalidArgumentException
   */
  public function load(string $id): array {
    if (empty($id)) {
      throw new \InvalidArgumentException('Branch ID is required.');
    }

    return $this->client->request('GET', '/api/v1/branches/' . $id);
  }

}
