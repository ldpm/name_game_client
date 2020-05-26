<?php

namespace Drupal\name_game_client\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;

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
    $stored_player = $tempstore->get('name_game_player_id');

    $nameForm = $this->formBuilder()->getForm('Drupal\name_game_client\Form\NameForm');
    $renderer = \Drupal::service('renderer');
    $nameFormHTML = $renderer->render($nameForm);
    $client = \Drupal::httpClient();
    $request = $client->get('http://35.226.37.213/namegame/api/hats/' . $hatid . '/names?isGotten=false', ['headers' => array('Content-Type' => 'application/json', 'Accept' => 'application/json')]);
    $response = json_decode($request->getBody());
    $names_markup = "";
    if (sizeof($response > 0)) {
      $mynames = array();
      foreach ($response as $r) {
        $player_id = substr($r->{'owner'}, strrpos($r->{'owner'}, '/') + 1);
        if ($player_id == $stored_player) {
          $mynames[] = $r->{'Name'};
        }
      }
      $names_markup = "<p>You have added the following names so far:</p><ul>";
      foreach ($mynames as $myname) {
        $names_markup .=  "<li>" . $myname . "</li>";
      }
      $names_markup .= "</ul>";
    }
    else {
      $names_markup = "<p>You have not yet added any names to this hat.</p>";
    }
    return [
      '#type' => 'markup',
      '#markup' => Markup::create("
      {$names_markup}
      {$nameFormHTML}
      ")
    ];
  }
}
