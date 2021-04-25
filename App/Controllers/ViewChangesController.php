<?php
namespace App\Controllers;
use App\Models\{WatchedPlaylist, ImageChooser};
use App\Services\{ApiSpotifyService, DatabaseService, RefreshTokenNotSetException};

class ViewChangesController extends AbstractController {

  public function __construct() {
    parent::__construct();

  }

  public function show() {
    if(! isset($_GET["id"])) {
      $this->redirect("listPlaylists.php");
      return;
    }

    try {
      $dbService = new DatabaseService(); 
      $spotifyService = new ApiSpotifyService();
      $playlist = $dbService->getTask($_GET["id"], $spotifyService->getUserId());
      if(! $playlist) {
        $this->redirect("listPlaylists.php");
        return;
      }

      $sourcePlaylist = $spotifyService->getPlaylist($playlist->getSourceId());
      $destPlaylist = $spotifyService->getPlaylist($playlist->getDestId());
      $playlist->update($sourcePlaylist, $destPlaylist);
      
      $dbService->saveTask($playlist);


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