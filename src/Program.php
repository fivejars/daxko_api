<?php

namespace Drupal\daxko_api;

/**
 * Wraps program related requests.
 */
class Program extends DaxkoEndpointBase {

  /**
   * Loads categories.
   *
   * @param array $location_ids
   *   Restricts the list to be associated with the specified locations.
   *   If omitted, no location-based restrictions will be applied.
   * @param int $limit
   *   (Optional) Number of results to return.
   * @param array $params
   *   (Optional) The array of additional query params.
   *
   * @return array|mixed
   *   The array of program categories.
   */
  public function getCategories(array $location_ids = [], $limit = 100, array $params = []) {
    $query = [
      'limit' => $limit,
      'location_ids' => implode(',', $location_ids),
    ];

    if (!empty($params)) {
      $query = array_merge($query, $params);
    }

    $query = array_filter($query);
    return $this->client->request('GET', '/v3/programs/categories', ['query' => $query]);
  }

  /**
   * Returns offerings for the different program types as a common format.
   *
   * Accepts a number of search parameters.
   *
   * @param array $category_ids
   *   Restricts the results to the list of categories.
   * @param array $location_ids
   *   Restricts the results to the of list of locations.
   * @param array $filters
   *   The array of additional filters.
   * @param int $limit
   *   Number of offering results to return (max of 100).
   * @param string $sort
   *   Determines how the offering results will be sorted.
   *   Example: +name,-score
   *   Valid values: name, score, start_date.
   *
   * @return array
   *   Search results.
   *
   * @see https://api.daxko.com/v3/docs/api/index.html#searchProgramOfferings
   */
  public function search(array $category_ids = [], array $location_ids = [], array $filters = [], $limit = 100, $sort = '-score') {
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
    return $this->client->request('GET', '/v3/programs/offerings/search', ['query' => $query]);
  }

}
