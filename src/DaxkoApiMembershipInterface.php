<?php

declare(strict_types=1);

namespace Drupal\daxko_api;

/**
 * Daxko Membership Interface.
 */
interface DaxkoApiMembershipInterface {

  /**
   * Returns a list of membership types.
   *
   * @param string $branch_id
   *   Restricts the list of membership types to a particular branch.
   * @param bool $discount_group_ids
   *   Allows the supplied discount group ids to be applied to the fees.
   * @param string $registration_type
   *   (optional) Restricts the list to only be associated with a type of join.
   *   Valid values: 'in_house', 'online'.
   *
   * @return array
   *   The array of membership types.
   */
  public function getTypes(string $branch_id, bool $discount_group_ids = FALSE, string $registration_type = 'online'): array;

  /**
   * Returns a list of branches that can handle membership join.
   *
   * @param string $registration_type
   *   Restricts the list to only be associated with a type of join.
   *   Valid values: in_house, online.
   * @param bool $renew
   *   When true, include branches that allow members to renew their membership.
   *   When false, the renew parameter does not restrict the list of branches.
   *
   * @return array
   *   The list of branches that allow members to join via the API.
   */
  public function getBranches(string $registration_type = 'online', bool $renew = FALSE): array;

  /**
   * Starts the join process for a given membership type id.
   *
   * The returned cart_id will be required on subsequent membership calls.
   * NOTE: This cart_id is only valid for membership API calls.
   *
   * @param string $type_id
   *   Membership type id to start join process with.
   * @param string $registration_type
   *   (optional) Sets the context of the registration.
   *
   * @return array
   *   An associative array containing:
   *   - cart_id: The ID of the cart that was created.
   *   - links: A list of related resources and their corresponding URL links.
   *
   *   Example:
   *   @code
   *   [
   *     'cart_id' => 'db2c4395-888c-42ce-9056-08e2f8a5f2d0',
   *     'links' => ['rel' => 'review', 'href' => '/api/v1/membership/db2c4395-888c-42ce-9056-08e2f8a5f2d0'],
   *   ]
   *   @endcode
   */
  public function join(string $type_id, string $registration_type = 'online'): array;

  /**
   * Starts the renewal process for a given 'membership_type_id' and 'unit_id'.
   *
   * @param string $type_id
   *   Membership type id to start renewal process with.
   * @param string $unit_id
   *   Unit ID to renew.
   * @param string $registration_type
   *   (optional) Sets the context of the registration.
   *   Valid values: 'online', 'in_house'.
   *
   * @return array
   *   An associative array containing:
   *   - cart_id: The ID of the cart that was created.
   *   - links: A list of related resources and their corresponding URL links.
   *
   *   Example:
   *   @code
   *   [
   *     'cart_id' => 'db2c4395-888c-42ce-9056-08e2f8a5f2d0',
   *     'links' => ['rel' => 'review', 'href' =>
   * '/api/v1/membership/db2c4395-888c-42ce-9056-08e2f8a5f2d0'],
   *   ];
   *   @endcode
   */
  public function renew(string $type_id, string $unit_id, string $registration_type = 'online'): array;

}
