<?php
namespace App\Controllers;
use App\Models\{WatchedPlaylist, ImageChooser};
use App\Services\{PlaylistQueryService, DeepCloneService, ApiSpotifyService, DatabaseService, RetrieveChangesService, UserDatabaseService, RefreshTokenNotSetException};

class ViewChangesController extends AbstractUserIdController {

  public function __construct() {
    parent::__construct();

  }

  public function show() {
    $taskId = $this->retrieveGetVar("id");

    try {
      $retreiveChangesService = new RetrieveChangesService($this->getUserId());
      $destPlaylistIntercepter = function($destPlaylist) {
        $deepCloneService = new DeepCloneService();
        $playlistWithoutTracks = $deepCloneService->clone($destPlaylist);
        $playlistWithoutTracks["tracks"]["items"] = [];
        return $playlistWithoutTracks;
      };

      $playlist = $retreiveChangesService->saveUpdatedPlaylist($taskId, null, $destPlaylistIntercepter);
      $this->ensureVarIsset($playlist);

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