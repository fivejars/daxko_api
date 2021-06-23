<?php

namespace Drupal\daxko_api;

/**
 * Provides interface for work with the Program related endpoints.
 */
interface DaxkoApiProgramInterface {

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
   * @return array
   *   The array of program categories.
   */
  public function getCategories(array $location_ids = [], int $limit = 100, array $params = []):array;

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
  public function search(array $category_ids = [], array $location_ids = [], array $filters = [], int $limit = 100, string $sort = '-score'): array;

}
