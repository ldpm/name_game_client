<?php

namespace Drupal\name_game_client\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
    $api_url = $this->config->get('name_game_client_api_server');
    $request = $client->post($api_url . "/hats", [
      'json' => [
        'createdDate' => $todaystring
      ]
    ]);
    $response = json_decode($request->getBody());

    \Drupal::messenger()->addMessage("You have created Hat #" . $response->{'id'});
    $form_state->setRedirect('name_game_client.welcome_controller_welcome');
  }

}
