<?php

declare(strict_types=1);

namespace Drupal\daxko_api;

/**
 * Provides Membership-related endpoints logic.
 */
class Membership extends DaxkoEndpointBase implements DaxkoApiMembershipInterface {

  /**
   * {@inheritdoc}
   */
  public function getTypes(string $branch_id, bool $discount_group_ids = FALSE, string $registration_type = 'online'): array {
    $query = [];
    $query['branch_id'] = $branch_id;

    if ($discount_group_ids) {
      $query['discount_group_ids'] = $discount_group_ids;
    }

    if ($registration_type) {
      $query['registration_type'] = $registration_type;
    }

    $data = $this->client->request('GET', '/api/v1/membership/membership_types', ['query' => $query]);
    return $data['membership_types'] ?? [];
  }

  /**
   * Returns the age groups list.
   *
   * @return array
   *   List of age groups
   */
  public function getAgeGroups(): array {
    $data = $this->client->request('GET', '/api/v1/membership/age_groups');
    return $data['age_groups'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getBranches(string $registration_type = 'online', bool $renew = FALSE): array {
    $query = [
      'registration_type' => $registration_type,
      'renew' => $renew,
    ];
    $query = array_filter($query);
    $data = $this->client->request('GET', '/api/v1/membership/branches', ['query' => $query]);
    return $data['branches'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function join(string $type_id, ?string $registration_type = NULL): array {
    $form_params = [
      'membership_type_id' => $type_id,
    ];

    if ($registration_type) {
      $form_params['registration_type'] = $registration_type;
    }
    $options = ['form_params' => $form_params];

    return $this->client->request('POST', '/api/v1/membership/join', $options);
  }

  /**
   * This call is used to retrieve the information needed to review the cart.
   *
   * @param string $cart_id
   *   The Daxko Membership cart ID.
   *
   * @return array
   *   The Membership information.
   */
  public function review(string $cart_id): array {
    return $this->client->request('GET', '/api/v1/membership/' . $cart_id);
  }

  /**
   * {@inheritdoc}
   */
  public function renew(string $type_id, string $unit_id, string $registration_type = 'online'): array {
    $form_params = [
      'membership_type_id' => $type_id,
      'unit_id' => $unit_id,
      'registration_type' => $registration_type,
    ];
    $options = ['form_params' => $form_params];

    return $this->client->request('POST', '/api/v1/membership/renewal', $options);
  }

}
