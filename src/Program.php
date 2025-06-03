<?php

declare(strict_types=1);

namespace Drupal\daxko_api;

/**
 * Wraps program related requests.
 */
class Program extends DaxkoEndpointBase implements DaxkoApiProgramInterface {

  /**
   * {@inheritdoc}
   */
  public function getCategories(array $location_ids = [], $limit = 100, array $params = []): array {
    $query = [
      'limit' => $limit,
      'location_ids' => implode(',', $location_ids),
    ];

    if (!empty($params)) {
      $query = array_merge($query, $params);
    }

    $query = array_filter($query);
    return $this->client->request('GET', '/api/v1/programs/categories', ['query' => $query]);
  }

  /**
   * {@inheritdoc}
   */
  public function search(array $category_ids = [], array $location_ids = [], array $filters = [], $limit = 100, $sort = '-score'): array {
    $query = [
      'limit' => $limit,
      'location_ids' => implode(',', $location_ids),
      'category_ids' => implode(',', $category_ids),
      'sort' => $sort,
    ];

    if (!empty($filters)) {
      $query = array_merge($query, $filters);
    }

    $query = array_filter($query);
    return $this->client->request('GET', '/api/v1/programs/offerings/search', ['query' => $query]);
  }

  /**
   * {@inheritdoc}
   */
  public function getProgramLocations(array $category_ids = [], int $limit = 100, array $params = []): array {
    $query = [
      'category_ids' => implode(',', $category_ids),
      'limit' => $limit,
    ];

    if (!empty($params)) {
      $query = array_merge($query, $params);
    }

    return $this->client->request('GET', '/api/v1/programs/locations', ['query' => $query]);
  }

  /**
   * {@inheritdoc}
   */
  public function getOfferingList(string $program_id, array $offering_ids = [], array $filters = []): array {
    $query = [];

    if ($offering_ids) {
      $query['offering_ids'] = implode(',', $offering_ids);
    }

    if (!empty($params)) {
      $query = array_merge($query, $params);
    }

    return $this->client->request('GET', '/api/v1/programs/' . $program_id . '/offerings', ['query' => $query]);
  }

  /**
   * {@inheritdoc}
   */
  public function getOfferingDetails(string $program_id, string $offering_id, string $location_id): array {
    $query = [];

    if ($location_id) {
      $query['location_id'] = $location_id;
    }

    return $this->client->request('GET', '/api/v1/programs/' . $program_id . '/offerings/' . $offering_id, ['query' => $query]);
  }

}
