<?php
namespace App\Controllers;
use App\Models\{WatchedPlaylist, ImageChooser};
use App\Services\{ApiSpotifyService, DatabaseService, UserDatabaseService, RefreshTokenNotSetException};

class AcceptSongController extends AbstractUserIdController {

  public function __construct() {
    parent::__construct();

  }

  public function show() {
    if(! isset($_GET["id"])) {
      $this->redirect("listPlaylists.php");
      return;
    }

    if(isset($_GET["songUri"])) {
      $_SESSION["songUri"] = $_GET["songUri"];
      $this->redirect("acceptSong.php?id=" . $_GET["id"]);
      die;
    } 
  

    try {
      $dbService = new DatabaseService(); 
      $spotifyService = new ApiSpotifyService(new UserDatabaseService(), $this->getUserId());
      $playlist = $dbService->getTask($_GET["id"], $spotifyService->getUserId());
      if(! $playlist) {
        $this->redirect("listPlaylists.php");
        return;
      }

      if(isset($_SESSION["songUri"])) {
        $acceptSongUri = $_SESSION["songUri"];
        $spotifyService->addSongsToPlaylist($playlist->getDestId(), [
          $acceptSongUri
        ]);
        $playlist->removeSongFromChanges($acceptSongUri);
        $dbService->saveTask($playlist);
      }
      

      $this->redirect("viewChanges.php?id=" . $_GET["id"]);
    } catch(RefreshTokenNotSetException $e) {
      $this->redirect("index.php"); 
      return;
    } finally {
      unset($_SESSION["songUri"]);
    }
  }
}

?>