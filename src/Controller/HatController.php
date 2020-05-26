<?php

namespace Drupal\name_game_client\Controller;

use Drupal\Core\Ajax\AlertCommand;
use Drupal\Core\Ajax\RedirectCommand;
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

    $hat_id = $_REQUEST['hatid'];
    $name_id = $_REQUEST['nameid'];

    // Sadly, re-fetch the name because I suck.
    $client = \Drupal::httpClient();
    $name = $client->get('http://35.226.37.213/namegame/api/names/' . $name_id);
    if ($name->getStatusCode() == 200) {
      $n_response = json_decode($name->getBody());

      // Time to mark this name as completed!
      $update = $client->put('http://35.226.37.213/namegame/api/names/' . $name_id, [
        'json' => [
          'Name' => $n_response->{'Name'},
          'isGotten' => true,
          'createdDate' => $n_response->{'createdDate'},
          'owner' => $n_response->{'owner'},
          'hat' => $n_response->{'hat'}
        ]
      ]);
    }
    else {
      throw new \Exception("Couldn't find that name on the server");
    }

    $response = new AjaxResponse();
    $redirect = new RedirectCommand("/hat/$hat_id/play");
    $response->addCommand($redirect);
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
        $markup = "<div id='current_name'><h2>" . $response[$thisname]->{'Name'} . "</h2></div>";
        $arguments = array(
          'hatid' => $hatid,
          'nameid' => $response[$thisname]->{'id'},
        );
        $url = Url::fromRoute('name_game_client.hat_controller_get_next', $arguments);
        $url->setOption('attributes', ['class' => array('use-ajax','button')]);
        $back = Url::fromRoute('name_game_client.welcome_controller_welcome');
        $back->setOption('attributes', ['class' => 'button']);

      }
      else {
        throw new \Exception("We got an unexpected response: " . $request->getReasonPhrase());
      }
      $return[] = array(
        '#type' => 'markup',
        '#markup' => $markup,
      );
      $return[] = array(
        '#type' => 'link',
        '#url' => $url,
        '#title' => $this->t("Got it! Draw another name"),
      );
      $return[] = array(
        '#type' => 'link',
        '#url' => $back,
        '#title' => $this->t("We didn't get it; return to the waiting room"),
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
