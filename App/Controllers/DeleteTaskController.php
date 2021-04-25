<?php
namespace App\Controllers;
use App\Models\{WatchedPlaylist, ImageChooser};
use App\Services\{ApiSpotifyService, DatabaseService, RefreshTokenNotSetException};

class DeleteTaskController extends AbstractController {

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
        $spotifyService = new ApiSpotifyService();
        $dbService = new DatabaseService(); 
        $playlist = $dbService->getTask($_GET["id"], $spotifyService->getUserId());
        if(! $playlist) {
          $this->redirect("listPlaylists.php");
          return;
        }
  
        $playlist->delete();
        $dbService->saveTask($playlist);
  
        $this->redirect("listPlaylists.php?restoreableTask=".$_GET["id"]);
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