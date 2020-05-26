<?php

namespace Drupal\name_game_client\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class NameForm.
 */
class NameForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'name_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $tempstore = \Drupal::service('user.private_tempstore')->get('name_game_client');
    $stored_hat = $tempstore->get('name_game_hat_id');
    $stored_player = $tempstore->get('name_game_player_id');

    if (empty($stored_hat)) {
      throw new \Exception("You need to join a game first");
    }

    $form['hat_id'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Hat ID'),
      '#description' => $this->t('Hat ID of the Hat to toss this name into'),
      '#default_value' => $stored_hat,
      '#maxlength' => 64,
      '#size' => 5,
      '#weight' => '0',
    ];
    $form['player_id'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Player ID'),
      '#description' => $this->t('Your Player ID'),
      '#default_value' => $stored_player,
      '#maxlength' => 128,
      '#size' => 5,
      '#weight' => '0',
    ];
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name to go into the hat'),
      '#description' => $this->t('The actual name'),
      '#maxlength' => 255,
      '#size' => 64,
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
    $today = new \DateTime();
    $todaystring = $today->format(DATE_ISO8601);
    $client = \Drupal::httpClient();
    $request = $client->post("http://35.226.37.213/namegame/api/names", [
      'json' => [
        'Name' => $form_state->getValue('name'),
        'isGotten' => false,
        'createdDate' => $todaystring,
        'owner' => 'api/players/' . $form_state->getValue('player_id'),
        'hat' => 'api/hats/'. $form_state->getValue('hat_id')
      ]
    ]);
    if ($request->getStatusCode() == 201) {
      // Success!
      $response = json_decode($request->getBody());
      $message = $response->{'Name'} . " successfully added to hat #" . $form_state->getValue('hat_id');
    }
    else {
      $message = "There was a problem: " . $request->getStatusCode();
    }
    \Drupal::messenger()->addMessage($message);
  }

}
