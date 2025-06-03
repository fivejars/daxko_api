<?php

declare(strict_types=1);

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
  protected function getEditableConfigNames(): array {
    return ['daxko_api.settings'];
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'daxko_api_settings';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('daxko_api.settings');

    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#default_value' => $config->get('client_id'),
      '#required' => TRUE,
    ];

    $form['client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Secret'),
      '#default_value' => $config->get('client_secret'),
      '#required' => TRUE,
    ];

    $form['scope'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Scope'),
      '#default_value' => $config->get('scope'),
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
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $values = $form_state->getValues();
    $this->config('daxko_api.settings')
      ->set('client_id', $values['client_id'])
      ->set('client_secret', $values['client_secret'])
      ->set('scope', $values['scope'])
      ->set('delay', $values['delay'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
