<?php

namespace Drupal\name_game_client\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CreateHat.
 */
class CreateHat extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'create_hat';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create New Hat'),
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
    $today = new \DateTime();
    $todaystring = $today->format(DATE_ISO8601);
    $client = \Drupal::httpClient();
    $request = $client->post("http://35.226.37.213/namegame/api/hats", [
      'json' => [
        'createdDate' => $todaystring
      ]
    ]);
    sleep(0);
    $response = json_decode($request->getBody());


    foreach ($form_state->getValues() as $key => $value) {
      \Drupal::messenger()->addMessage($key . ': ' . ($key === 'text_format'?$value['value']:$value));
    }
  }

}
