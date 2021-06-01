<?php
namespace App\Controllers;
use App\Models\{WatchedPlaylist, ImageChooser};
use App\Services\{ApiSpotifyService, DatabaseService, UserDatabaseService, RefreshTokenNotSetException};

class DeleteTaskController extends AbstractUserIdController {

  public function __construct() {
    parent::__construct();

  }

  public function show() {
    if(! isset($_GET["id"])) {
      $this->redirect("listPlaylists.php");
      return;
    }

    if(isset($_GET["deleteTask"])) {
      $_SESSION["deleteTask"] = $_GET["deleteTask"];
      $this->redirect("deleteTask.php?id=" . $_GET["id"]);
      die;
    } 

    if(isset($_SESSION["deleteTask"])) {
      try {
        $userId = $this->getUserId();
        $spotifyService = new ApiSpotifyService(new UserDatabaseService(), $userId);
        $dbService = new DatabaseService(); 
        $playlist = $dbService->getTaskSuppressOwnerCheck($_GET["id"]);
        if(! $playlist) {
          $this->redirect("listPlaylists.php");
          return;
        }

        if(! (($playlist->getSourceOwnerId() == $userId && $playlist->getSourceType() == "user") || $playlist->getDestOwnerId() == $userId)) {
          $this->redirect("listPlaylists.php");
          return;
        }
  
        $playlist->delete();
        $dbService->saveTask($playlist);

        $redirectString = "listPlaylists.php" . ($playlist->getDestOwnerId() == $userId ? "?restoreableTask=".$_GET["id"] : "");
        $this->redirect($redirectString);
        return;
      } catch(RefreshTokenNotSetException $e) {
        $this->redirect("index.php"); 
        return;
      } finally {
        unset($_SESSION["deleteTask"]);
      }
      
    }

    $this->redirect("listPlaylists.php");
  }
}

?>