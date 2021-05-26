<?php
namespace App\Controllers;
use App\Services\{LoginSpotifyService};

class IndexController extends AbstractController {

  public function __construct() {
    parent::__construct();

  }


  public function show() {
    $spotifyService = new LoginSpotifyService();
    $redirectUrl = $spotifyService->authorize();
    $this->redirect($redirectUrl);
  }

}

?>