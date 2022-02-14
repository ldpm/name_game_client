<?php

namespace Drupal\name_game_client\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class WelcomeController.
 */
class WelcomeController extends ControllerBase {

  protected $config;

  public function __construct()
  {
    $this->config = \Drupal::config('name_game_client.settings');
  }

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
    $api_url = $this->config->get('name_game_client_api_server');
    $request = $client->get($api_url . '/players/' . $stored_player);
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
