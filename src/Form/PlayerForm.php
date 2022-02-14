<?php

namespace Drupal\name_game_client\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Queue\RequeueException;
use GuzzleHttp\Exception\RequestException;

/**
 * Class PlayerForm.
 */
class PlayerForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'player_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['your_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your &quot;Player Name&quot; for this Game'),
      '#description' => $this->t('This means you, not one of the names you&#039;re entering into the hat.'),
      '#maxlength' => 128,
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
    $today = new \DateTime();
    $todaystring = $today->format(DATE_ISO8601);
    $client = \Drupal::httpClient();
    $api_url = $this->config->get('name_game_client_api_server');
    try {
      $request = $client->post($api_url . "/api/players", [
        'json' => [
          'createdDate' => $todaystring,
          'Name' => $form_state->getValue('your_name')
        ]
      ]);
      $response = json_decode($request->getBody());
      $tempstore = \Drupal::service('user.private_tempstore')->get('name_game_client');
      $tempstore->set('name_game_player_id', $response->{'id'});

      \Drupal::messenger()->addMessage("Your Player ID for this game is " . $response->{'id'}.". You shouldn't need to remember it.");
      $form_state->setRedirect('name_game_client.welcome_controller_welcome');
    }
    catch (RequestException $e) {
      watchdog_exception('name_game_client', $e->getMessage());
      \Drupal::messenger()->addMessage("Sorry, an error occurred: " . $e->getMessage());
    }
  }
}
