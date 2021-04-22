<?php
namespace App\Controllers;
use App\Models\{WatchedPlaylist, ImageChooser};
use App\Services\{ApiSpotifyService, DatabaseService, RefreshTokenNotSetException};

class ViewChangesController extends AbstractController {

  public function __construct() {
    parent::__construct();

  }
/*
  private function setSessionFromGet($key) {
    if(isset($_GET[$key])) {
      $_SESSION[$key] = $_GET[$key];
      $this->redirect("viewChanges.php?id=" . $_GET["id"]);
      die;
    } 
  }*/


  public function show() {
    if(! isset($_GET["id"])) {
      $this->redirect("listPlaylists.php");
      return;
    }
    
/*    $this->setSessionFromGet("acceptSongUri");
    $this->setSessionFromGet("declineSongUri");
    $this->setSessionFromGet("acceptAll");
    $this->setSessionFromGet("declineAll");*/


    try {
      $dbService = new DatabaseService(); 
      $playlist = $dbService->getPlaylist($_GET["id"]);
      if(! $playlist) {
        $this->redirect("listPlaylists.php");
        return;
      }

      $spotifyService = new ApiSpotifyService();
   /*   if(isset($_SESSION["acceptSongUri"])) {
        $acceptSongUri = $_SESSION["acceptSongUri"];
        $spotifyService->addSongsToPlaylist($playlist->getDestId(), [
          $acceptSongUri
        ]);
        $playlist->removeSongFromChanges($acceptSongUri);
        unset($_SESSION["acceptSongUri"]);
      }
      if(isset($_SESSION["declineSongUri"])) {
        $declineSongUri = $_SESSION["declineSongUri"];
        $playlist->removeSongFromChanges($declineSongUri);
        unset($_SESSION["declineSongUri"]);
      }
      if(isset($_SESSION["acceptAll"])) {
        $uris = $playlist->removeAllChangesAndGetUris();
        $spotifyService->addSongsToPlaylist($playlist->getDestId(), $uris);
        unset($_SESSION["acceptAll"]);
      }
      if(isset($_SESSION["declineAll"])) {
        $uris = $playlist->removeAllChangesAndGetUris();
        $spotifyService->addSongsToPlaylist($playlist->getDestId(), $uris);
        unset($_SESSION["declineAll"]);
      }*/

      $sourcePlaylist = $spotifyService->getPlaylist($playlist->getSourceId());
      $destPlaylist = $spotifyService->getPlaylist($playlist->getDestId());
      $playlist->update($sourcePlaylist, $destPlaylist);
      
      $dbService->savePlaylist($playlist);


      $params = [
        "playlist" => $playlist->getDocument(),
        "playlistObject" => $playlist,
        "imageChooser" => new ImageChooser()
      ];
      $twig = $this->loadTwig();
      echo $twig->render("pages/p-changes.twig", $params);
    } catch(RefreshTokenNotSetException $e) {
      $this->redirect("index.php"); 
      return;
    }
  }
}

?>