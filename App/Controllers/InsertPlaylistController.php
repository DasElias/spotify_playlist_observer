<?php
namespace App\Controllers;
use App\Models\WatchedPlaylist;
use App\Services\{ApiSpotifyService, DatabaseService, UserDatabaseService, RefreshTokenNotSetException};

class InsertPlaylistController extends AbstractUserIdController {

  public function __construct() {
    parent::__construct();

  }


  public function show() {
    $errorMsg = null;

    if(isset($_POST["sourcePlaylist"]) && isset($_POST["destPlaylist"])) {
      $sourcePlaylistUrl = $_POST["sourcePlaylist"];
      $destPlaylistUrl = $_POST["destPlaylist"];
      $sourcePlaylistId = $this->getPlaylistIdFromLink($sourcePlaylistUrl);
      $destPlaylistId = $this->getPlaylistIdFromLink($destPlaylistUrl);
      if($sourcePlaylistId == null || $destPlaylistId == null) {
        $errorMsg = "Eine der beiden eingegebenen Playlistlinks hat ein ungültiges Format.";
        goto render;
      }
      if($sourcePlaylistId == $destPlaylistId) {
        $errorMsg = "Quell- und Zielplaylist können nicht identisch sein.";
        goto render;
      }

      try {
        $spotifyService = new ApiSpotifyService(new UserDatabaseService(), $this->getUserId());
        $sourcePlaylist = $spotifyService->getPlaylist($sourcePlaylistId);
        $destPlaylist = $spotifyService->getPlaylist($destPlaylistId);
        $userId = $spotifyService->getUserId();
        $playlist = WatchedPlaylist::withApiResponse($sourcePlaylist, $destPlaylist);

        if($destPlaylist["owner"]["id"] != $userId) {
          if($destPlaylist["collaborative"]) {
            $errorMsg = "Du hast nicht die erforderlichen Rechte, Songs zur Zielplaylist (\"" . $destPlaylist['name'] . "\" von \"" . $destPlaylist["owner"]["display_name"] . "\") hinzuzufügen. Leider kann momentan nur der Eigentümer von gemeinsamen Playlists diese als Zielplaylist auswählen";
          } else {
            $errorMsg = "Du hast nicht die erforderlichen Rechte, Songs zur Zielplaylist (\"" . $destPlaylist['name'] . "\" von \"" . $destPlaylist["owner"]["display_name"] . "\") hinzuzufügen.";
          }
          goto render;
        }

        $dbService = new DatabaseService();
        if($dbService->doesTaskExist($sourcePlaylistId, $destPlaylistId)) {
          $errorMsg = "Änderungen in der Playlist \"" . $sourcePlaylist["name"] . "\" von \"". $sourcePlaylist["owner"]["display_name"] . "\" werden bereits in die Playlist \"" . $destPlaylist["name"] . "\" übernommen.";
          goto render;
        }

        $dbService->saveTask($playlist);
        $this->redirect("listPlaylists.php");
      } catch(UnauthorizedException $e) {
        $errorMsg = "Du hast entweder auf die Quell- oder die Zielplaylist keinen Zugriff.";
        goto render;
      } catch(PlaylistDoesntExistException $e) {
        $errorMsg = "Entweder die Quell- oder die Zielplaylist existiert nicht.";
        goto render;
      } catch(RefreshTokenNotSetException $e) {
        $this->redirect("index.php"); 
        return;
      }

      
    }


    render:
    $params = [
      "errorMsg" => $errorMsg
    ];
    $twig = $this->loadTwig();
    echo $twig->render("pages/p-insert.twig", $params);
  }

  private function getPlaylistIdFromLink($link) {
    $matches = [];
    $success = preg_match('/^(https:\/\/open\.spotify\.com\/playlist\/(.*)\?si=.*)$/', $link, $matches);
    if(! $success) {
      return null;
    }

    return $matches[2];
  }

}

?>