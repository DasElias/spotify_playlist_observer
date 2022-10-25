<?php
namespace App\Controllers;
use App\Models\{WatchedPlaylist, ImageChooser};
use App\Services\{ApiSpotifyService, DatabaseService, UserDatabaseService, RefreshTokenNotSetException};

class DeclineSongController extends AbstractUserIdController {

  public function __construct() {
    parent::__construct();

  }

  public function show() {
    if(! isset($_GET["id"])) {
      $this->redirectIfDesired("listPlaylists.php", 400);
      return;
    }

    $this->storeInSession("songUri", "declineSong.php?id=" . $_GET["id"], 400);

    try {  
      $dbService = new DatabaseService(); 
      $spotifyService = new ApiSpotifyService(new UserDatabaseService(), $this->getUserId());
      $playlist = $dbService->getTask($_GET["id"], $spotifyService->getUserId());
      if(! $playlist) {
        $this->redirectIfDesired("listPlaylists.php", 404);
        return;
      }

      $this->getFromSession("songUri", function($songUri) use ($playlist, $dbService) {
        $playlist->removeSongFromChanges($songUri);
        $dbService->saveTask($playlist);
      });      

      $this->redirectIfDesired("viewChanges.php?id=" . $_GET["id"]);
    } catch(RefreshTokenNotSetException $e) {
      $this->redirectIfDesired("index.php", 401);
      return;
    } finally {
      unset($_SESSION["songUri"]);
    }
  

  }
}

?>