<?php
namespace App\Controllers;
use App\Models\{WatchedPlaylist, ImageChooser};
use App\Services\{ApiSpotifyService, DatabaseService, RefreshTokenNotSetException};

class AcceptAllController extends AbstractController {

  public function __construct() {
    parent::__construct();

  }

  public function show() {
    if(! isset($_GET["id"])) {
      $this->redirect("listPlaylists.php");
      return;
    }

    if(isset($_GET["acceptAll"])) {
      $_SESSION["acceptAll"] = $_GET["acceptAll"];
      $this->redirect("acceptAll.php?id=" . $_GET["id"]);
      die;
    } 

    try {
      if(isset($_SESSION["acceptAll"])) {
        $dbService = new DatabaseService(); 
        $playlist = $dbService->getPlaylist($_GET["id"]);
        if(! $playlist) {
          $this->redirect("listPlaylists.php");
          return;
        }
  
        $spotifyService = new ApiSpotifyService();
        $uris = $playlist->removeAllChangesAndGetUris();
        $spotifyService->addSongsToPlaylist($playlist->getDestId(), $uris);
        $dbService->savePlaylist($playlist);
      }
     

      $this->redirect("viewChanges.php?id=" . $_GET["id"]);
    } catch(RefreshTokenNotSetException $e) {
      $this->redirect("index.php"); 
      return;
    }
  }
}

?>