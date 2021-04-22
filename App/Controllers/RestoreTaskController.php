<?php
namespace App\Controllers;
use App\Models\{WatchedPlaylist, ImageChooser};
use App\Services\{ApiSpotifyService, DatabaseService, RefreshTokenNotSetException};

class RestoreTaskController extends AbstractController {

  public function __construct() {
    parent::__construct();

  }

  public function show() {
    if(! isset($_GET["id"])) {
      $this->redirect("listPlaylists.php");
      return;
    }

    if(isset($_GET["restoreTask"])) {
      $_SESSION["restoreTask"] = $_GET["restoreTask"];
      $this->redirect("restoreTask.php?id=" . $_GET["id"]);
      die;
    } 

    if(isset($_SESSION["restoreTask"])) {
      $dbService = new DatabaseService(); 
      $playlist = $dbService->getPlaylist($_GET["id"]);
      if(! $playlist) {
        $this->redirect("listPlaylists.php");
        return;
      }

      $playlist->restore();
      $dbService->savePlaylist($playlist);
      unset($_SESSION["restoreTask"]);
    }


    $this->redirect("listPlaylists.php");
  }
}

?>