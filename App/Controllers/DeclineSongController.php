<?php
namespace App\Controllers;
use App\Models\{WatchedPlaylist, ImageChooser};
use App\Services\{ApiSpotifyService, DatabaseService, RefreshTokenNotSetException};

class DeclineSongController extends AbstractController {

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
      $this->redirect("declineSong.php?id=" . $_GET["id"]);
      die;
    } 
  

    $dbService = new DatabaseService(); 
    $playlist = $dbService->getPlaylist($_GET["id"]);
    if(! $playlist) {
      $this->redirect("listPlaylists.php");
      return;
    }

    if(isset($_SESSION["songUri"])) {
      $acceptSongUri = $_SESSION["songUri"];
      $playlist->removeSongFromChanges($acceptSongUri);
      $dbService->savePlaylist($playlist);
      unset($_SESSION["songUri"]);
    }
    

    $this->redirect("viewChanges.php?id=" . $_GET["id"]);
  }
}

?>