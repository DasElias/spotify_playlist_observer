<?php
namespace App\Controllers;
use App\Models\{WatchedPlaylist, ImageChooser};
use App\Services\{ApiSpotifyService, DatabaseService, RefreshTokenNotSetException};

class DeclineAllController extends AbstractController {

  public function __construct() {
    parent::__construct();

  }

  public function show() {
    if(! isset($_GET["id"])) {
      $this->redirect("listPlaylists.php");
      return;
    }

    if(isset($_GET["declineAll"])) {
      $_SESSION["declineAll"] = $_GET["declineAll"];
      $this->redirect("declineAll.php?id=" . $_GET["id"]);
      die;
    } 

    if(isset($_SESSION["declineAll"])) {
      $dbService = new DatabaseService(); 
      $playlist = $dbService->getPlaylist($_GET["id"]);
      if(! $playlist) {
        $this->redirect("listPlaylists.php");
        return;
      }

      $uris = $playlist->removeAllChangesAndGetUris();
      $dbService->savePlaylist($playlist);
    }


    $this->redirect("viewChanges.php?id=" . $_GET["id"]);
  }
}

?>