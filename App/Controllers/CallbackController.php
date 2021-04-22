<?php
namespace App\Controllers;
use App\Services\{LoginSpotifyService, RefreshTokenNotSetException, StateMismatchException};


class CallbackController extends AbstractController {

  public function __construct() {
    parent::__construct();

  }


  public function show() {
    $spotifyService = new LoginSpotifyService();
    try {
      $spotifyService->authorizeCallback();
      $this->redirect("listPlaylists.php");
    } catch(RefreshTokenNotSetException | StateMismatchException $e) {
      $this->redirect("index.php"); 
    }
  }

}

?>