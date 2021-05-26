<?php
namespace App\Controllers;
use App\Services\{LoginSpotifyService, UserDatabaseService, RefreshTokenNotSetException, StateMismatchException};


class CallbackController extends AbstractUserIdController {

  public function __construct() {
    parent::__construct([
      "suppressUserIdCheck" => true
    ]);

  }


  public function show() {
    $spotifyService = new LoginSpotifyService();
    try {
      $userId = $spotifyService->authorizeCallbackAndGetUserId(new UserDatabaseService());
      $this->writeUserId($userId);
      $this->redirect("listPlaylists.php");
    } catch(RefreshTokenNotSetException | StateMismatchException $e) {
      $this->redirect("index.php"); 
    }
  }

}

?>