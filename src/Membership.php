<?php

namespace Drupal\daxko_api;

/**
 * Provides Membership related endpoints logic.
 */
class Membership extends DaxkoEndpointBase implements DaxkoApiMembershipInterface {

  /**
   * {@inheritdoc}
   */
  public function getTypes($branch_id, $discount_group_ids = FALSE, $registration_type = 'online') {
    $query = [];
    $query['branch_id'] = $branch_id;

    if ($discount_group_ids) {
      $query['discount_group_ids'] = $discount_group_ids;
    }

    if ($registration_type) {
      $query['registration_type'] = $registration_type;
    }

    $data = $this->client->request('GET', '/v3/membership/membership_types', ['query' => $query]);
    return $data['membership_types'] ?? [];
  }

  /**
   * Returns the age groups list.
   *
   * @return array
   *   List of age groups
   */
  public function getAgeGroups() {
    $data = $this->client->request('GET', '/v3/membership/age_groups');
    return $data['age_groups'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getBranches($registration_type = 'online', $renew = FALSE) {
    $query = [
      'registration_type' => $registration_type,
      'renew' => $renew,
    ];
    $query = array_filter($query);
    $data = $this->client->request('GET', '/v3/membership/branches', ['query' => $query]);
    return $data['branches'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function join($type_id, $registration_type = 'online') {
    $form_params = [
      'membership_type_id' => $type_id,
      'registration_type' => $registration_type,
    ];
    $options = ['form_params' => $form_params];

    return $this->client->request('POST', '/v3/membership/join', $options);
  }

  /**
   * {@inheritdoc}
   */
  public function renew($type_id, $unit_id, $registration_type = 'online') {
    $form_params = [
      'membership_type_id' => $type_id,
      'unit_id' => $unit_id,
      'registration_type' => $registration_type,
    ];
    $options = ['form_params' => $form_params];

    return $this->client->request('POST', '/v3/membership/renewal', $options);
  }

}
