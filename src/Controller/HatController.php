<?php

namespace Drupal\name_game_client\Controller;

use Drupal\Core\Ajax\AlertCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Ajax\AjaxResponse;
use Robo\Task\Composer\Remove;
use Drupal\Core\Url;


/**
 * Class HatController.
 */
class HatController extends ControllerBase {

  /**
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function getNext() {

    $response = new AjaxResponse();
    $alert = new AlertCommand("Hello, world!");
    //$replace = new ReplaceCommand('#current_name',"Hello, world!");
    $response->addCommand($alert);
    //$response->addCommand($replace);
    return $response;
  }

  /**
   * Play.
   *
   * @return string
   *   Return Hello string.
   */
  public function play($hatid) {
    try {
      $tempstore = \Drupal::service('user.private_tempstore')->get('name_game_client');
      $stored_hat = $tempstore->get('name_game_hat_id');
      $stored_player = $tempstore->get('name_game_player_id');
      // Make sure we're at the right hat
      if ($hatid != $stored_hat) {
        throw new \Exception("You are signed into a different hat");
      }
      // First fetch all the ungotten names from the current hat
      $client = \Drupal::httpClient();
      $request = $client->get('http://35.226.37.213/namegame/api/hats/' . $hatid . '/names?isGotten=false', ['headers' => array('Content-Type' => 'application/json', 'Accept' => 'application/json')]);
      if ($request->getStatusCode() == 200) {
        $response = json_decode($request->getBody());
        $thisname = array_rand($response);
        $markup = "<div id='current_name'>" . $response[$thisname]->{'Name'} . "</div>";
        $url = Url::fromRoute('name_game_client.hat_controller_get_next');
        $url->setOption('attributes', ['class' => 'use-ajax']);

      }
      else {
        throw new \Exception("We got an unexpected response: " . $request->getReasonPhrase());
      }
      $return[] = array(
        '#type' => 'link',
        '#url' => $url,
        '#title' => $this->t("Get Next Name"),
      );
      $return[] = array(
        '#type' => 'markup',
        '#markup' => $markup,
      );

      return $return;
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addMessage($e->getMessage(), MessengerInterface::TYPE_ERROR);

      return [
        '#type' => 'markup',
        '#markup' => "Error"
      ];
    }
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
