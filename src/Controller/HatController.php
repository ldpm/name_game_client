<?php

namespace Drupal\name_game_client\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class HatController.
 */
class HatController extends ControllerBase {

  /**
   * Play.
   *
   * @return string
   *   Return Hello string.
   */
  public function play($hatid) {

    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: play with parameter(s): ' . $hatid),
    ];
  }

  public function addnames($hatid) {
    $tempstore = \Drupal::service('user.private_tempstore')->get('name_game_client');
    $stored_hat = $tempstore->get('name_game_hat_id');

    $client = \Drupal::httpClient();
    $request = $client->get()
  }

}
