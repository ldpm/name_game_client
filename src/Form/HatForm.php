<?php

namespace Drupal\name_game_client\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class HatForm.
 */
class HatForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hat_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['hat_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hat ID'),
      '#description' => $this->t('Please enter the Hat ID for the game you&#039;d like to join'),
      '#maxlength' => 64,
      '#size' => 5,
      '#weight' => '0',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    $tempstore = \Drupal::service('user.private_tempstore')
      ->get('name_game_client');
    $tempstore->set('name_game_hat_id', $form_state->getValue('hat_id'));
    \Drupal::messenger()->addMessage("You have successfully joined Hat #" . $form_state->getValue('hat_id'));
  }
}
