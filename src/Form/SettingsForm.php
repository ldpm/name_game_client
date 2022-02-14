<?php

namespace Drupal\name_game_client\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm
 * @package Drupal\name_game_client\Form
 */
class SettingsForm extends ConfigFormBase {
  const SETTINGS = 'name_game_client.settings';

  /**
   * {inheritdoc}
   */
  public function getFormId() {
    return 'name_game_client_settings';
  }

  /**
   * {inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['name_game_client_api_server'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name Game API Server Address'),
      '#default_value' => $config->get('name_game_client_api_server'),
      '#description' => "probably ends in '/namegame/api'",
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('name_game_client_api_server', $form_state->getValue('name_game_client_api_server'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
