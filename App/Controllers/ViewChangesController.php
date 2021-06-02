<?php
namespace App\Controllers;
use App\Models\{WatchedPlaylist, ImageChooser};
use App\Services\{PlaylistQueryService, ApiSpotifyService, DatabaseService, UserDatabaseService, RefreshTokenNotSetException};

class ViewChangesController extends AbstractUserIdController {

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
      $userDatabaseService = new UserDatabaseService();
      $playlistQueryService = new PlaylistQueryService($userDatabaseService, $this->getUserId());
      $playlist = $dbService->getTask($_GET["id"], $this->getUserId());
      if(! $playlist) {
        $this->redirect("listPlaylists.php");
        return;
      }

      $sourcePlaylist = $playlistQueryService->query($playlist->getSourceId(), $playlist->getSourceType(), $playlist->isSourceAuthorized());
      $destPlaylist = $playlistQueryService->query($playlist->getDestId(), $playlist->getDestType());
      $playlist->update($sourcePlaylist, $destPlaylist);
      
      $dbService->saveTask($playlist);


      $params = [
        "playlist" => $playlist->getDocument(),
        "playlistObject" => $playlist,
        "isAuthorized" => $playlist->isSourceAuthorized(),
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