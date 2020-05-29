<?php

namespace Drupal\name_game_client\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'PlayerListBlock' block.
 *
 * @Block(
 *  id = "player_list_block",
 *  admin_label = @Translation("Player list block"),
 * )
 */
class PlayerListBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\user\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $userPrivateTempstore;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->userPrivateTempstore = $container->get('user.private_tempstore');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $tempstore = $this->userPrivateTempstore->get('name_game_client');
    $stored_hat = $tempstore->get('name_game_hat_id');
    if ($stored_hat > 0) {
      $players = array();
      $names_remaining = "0";
      // First fetch all the ungotten names from the current hat
      $client = \Drupal::httpClient();
      $remaining = $client->get('http://35.226.37.213/namegame/api/hats/' . $stored_hat . '/names?isGotten=false', [
        'headers' => array(
          'Content-Type' => 'application/json',
          'Accept' => 'application/json'
        )
      ]);
      if ($remaining->getStatusCode() == 200) {
        $remaining_names = json_decode($remaining->getBody());
        $names_remaining = sizeof($remaining_names);
      }
      $all = $client->get('http://35.226.37.213/namegame/api/hats/' . $stored_hat . '/names', [
        'headers' => array(
          'Content-Type' => 'application/json',
          'Accept' => 'application/json'
        )
      ]);
      if ($all->getStatusCode() == 200) {
        $all_names = json_decode($all->getBody());
        foreach ($all_names as $name) {
          $owner_id = $name->{'owner'};
          $player_resp = $client->get('http://35.226.37.213' . $owner_id, [
            'headers' => array(
              'Content-Type' => 'application/json',
              'Accept' => 'application/json'
            )
          ]);
          if ($player_resp->getStatusCode() == 200) {
            $player = json_decode($player_resp->getBody());
            $players[] = $player->{'Name'};
          }
        }
      }
    }

    $build['#theme'] = 'player_list_block';
    $build['#hat_id'] = $stored_hat;
    $build['#players'] = $players;
    $build['#names_remaining'] = $names_remaining;
    $build['player_list_block']['#markup'] = 'Implement PlayerListBlock.';


    return $build;
  }

  public function getCacheMaxAge() {
    return 0;
  }

}
