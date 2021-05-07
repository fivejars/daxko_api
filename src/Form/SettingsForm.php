<?php

namespace Drupal\daxko_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Daxko API Settings Form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames() {
    return ['daxko_api.settings'];
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'daxko_api_settings';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('daxko_api.settings');

    $form['client_id'] = [
      '#type' => 'number',
      '#title' => $this->t('Client ID'),
      '#default_value' => $config->get('client_id'),
      '#required' => TRUE,
    ];
    $form['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#default_value' => $config->get('username'),
      '#required' => TRUE,
    ];
    $form['password'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Password'),
      '#default_value' => $config->get('password'),
      '#required' => TRUE,
    ];
    $form['refresh_token'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Refresh token'),
      '#default_value' => $config->get('refresh_token'),
      '#required' => TRUE,
    ];
    $form['delay'] = [
      '#type' => 'select',
      '#title' => $this->t('Delay'),
      '#description' => $this->t('Delay between requests in milliseconds.'),
      '#options' => [
        0 => $this->t('Disabled'),
        100 => $this->t('100ms'),
        200 => $this->t('200ms'),
        300 => $this->t('300ms'),
        400 => $this->t('400ms'),
        500 => $this->t('500ms'),
        600 => $this->t('600ms'),
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('daxko_api.settings')
      ->set('client_id', $values['client_id'])
      ->set('username', $values['username'])
      ->set('password', $values['password'])
      ->set('refresh_token', $values['refresh_token'])
      ->set('delay', $values['delay'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
