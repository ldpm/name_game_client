<?php

namespace Drupal\name_game_client\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class WelcomeController.
 */
class WelcomeController extends ControllerBase {

  /**
   * Welcome.
   *
   * @return string
   *   Return Hello string.
   */
  public function welcome() {
    $tempstore = \Drupal::service('user.private_tempstore')->get('name_game_client');
    $stored_hat = $tempstore->get('name_game_hat_id');
    $stored_player = $tempstore->get('name_game_player_id');

    $client = \Drupal::httpClient();
    $request = $client->get('http://35.226.37.213/namegame/api/players/' . $stored_player);
    if ($request->getStatusCode() == 200) {
      $player = json_decode($request->getBody(), true);
    }

    return [
      '#theme' => 'waiting_room',
      '#hat_id' => $stored_hat,
      '#player' => $player,
    ];
  }

}
