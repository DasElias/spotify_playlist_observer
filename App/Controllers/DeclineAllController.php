<?php
namespace App\Controllers;
use App\Models\{WatchedPlaylist, ImageChooser};
use App\Services\{ApiSpotifyService, DatabaseService, UserDatabaseService, RefreshTokenNotSetException};

class DeclineAllController extends AbstractUserIdController {

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
      try {
        $dbService = new DatabaseService(); 
        $spotifyService = new ApiSpotifyService(new UserDatabaseService(), $this->getUserId());
        $playlist = $dbService->getTask($_GET["id"], $spotifyService->getUserId());
        if(! $playlist) {
          $this->redirect("listPlaylists.php");
          return;
        }
  
        $uris = $playlist->removeAllChangesAndGetUris();
        $dbService->saveTask($playlist);
      } catch(RefreshTokenNotSetException $e) {
        $this->redirect("index.php"); 
        return;
      } finally {
        unset($_SESSION["declineAll"]);
      }
    }


    $this->redirect("viewChanges.php?id=" . $_GET["id"]);
  }
}

?>