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

  /**
   * Returns a list of program locations that meet the criteria.
   *
   * @param array $category_ids
   *   (optional) Array of Daxko categories IDs.
   *   Restricts the list of locations to be associated with programs by categories.
   *   If omitted, no category-based restrictions will be applied.
   * @param int $limit
   *   (optional) Number of results to return.
   * @param array $params
   *   (optional) The array of additional parameters.
   *   - as_of: Restricts the list of locations to have the specified start and end datetime in at least one of the registration datetime ranges.
   *   Example: {start:2017-04-19T09:58:32,end:2017-04-23T09:58:32,mode:registration_occurs_between}
   *   - offering_types: Restricts the list of locations to only be associated with certain types of program offerings.
   *   If omitted, returns locations associated with all types of program offerings.
   *   Valid values: session, package, rate_plan, camp_instance
   *   - registration_type: Restricts the list of locations to only be associated with in-house or online program offerings.
   *   Valid values:  in_house, online
   *   - include_inactive_categories: Indicates whether to show locations associated with inactive categories.
   *   - include_inactive_locations: Indicates whether to show inactive locations.
   *   - date_ranges: Restricts results that occur within at least one of the date ranges.
   *
   * @see https://api.daxko.com/v3/docs/api/index.html#list-program-locations
   */
  public function getProgramLocations(array $category_ids = [], int $limit = 100, array $params = []) : array;

}
