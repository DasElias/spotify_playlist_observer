<?php
namespace App\Controllers;
use App\Models\WatchedPlaylist;
use App\Services\{PlaylistQueryService, ApiSpotifyService, DatabaseService, UserDatabaseService, RefreshTokenNotSetException};

class InsertPlaylistController extends AbstractUserIdController {

  public function __construct() {
    parent::__construct();

  }


  public function show() {
    $errorMsg = null;

    if(isset($_POST["source"]) && isset($_POST["destPlaylist"])) {
      $destPlaylistUrl = $_POST["destPlaylist"];
      $destPlaylistId = $this->getPlaylistIdFromLink($destPlaylistUrl);

      $sourceId = null;
      $source = null;
      $isSourceAuthorized = null;
      $dest = "playlist";

      if(isset($_POST["sourcePlaylist"]) && !empty($_POST["sourcePlaylist"])) {
        $source = "playlist";
        $isSourceAuthorized = true;
        $sourceId = $this->getPlaylistIdFromLink($_POST["sourcePlaylist"]);
      } else if(isset($_POST["sourceUser"]) && !empty($_POST["sourceUser"])) {
        $source = "user";
        $isSourceAuthorized = false;
        $sourceId = $this->getUserIdFromLink($_POST["sourceUser"]);
      }

      if($sourceId == null || $destPlaylistId == null) {
        $errorMsg = "Eine der beiden eingegebenen Links hat ein ungültiges Format.";
        goto render;
      }
      if($sourceId == $destPlaylistId) {
        $errorMsg = "Quell- und Zielplaylist können nicht identisch sein.";
        goto render;
      }

      try {
        $userId = $this->getUserId();
        $playlistQueryService = new PlaylistQueryService(new UserDatabaseService(), $userId);
        $sourcePlaylist = $playlistQueryService->query($sourceId, $source, $isSourceAuthorized);
        $destPlaylist = $playlistQueryService->query($destPlaylistId, $dest);
        $playlist = WatchedPlaylist::withApiResponse($sourcePlaylist, $destPlaylist, $source, $dest);

        if($destPlaylist["owner"]["id"] != $userId) {
          if($destPlaylist["collaborative"]) {
            $errorMsg = "Du hast nicht die erforderlichen Rechte, Songs zur Zielplaylist (\"" . $destPlaylist['name'] . "\" von \"" . $destPlaylist["owner"]["display_name"] . "\") hinzuzufügen. Leider kann momentan nur der Eigentümer von gemeinsamen Playlists diese als Zielplaylist auswählen";
          } else {
            $errorMsg = "Du hast nicht die erforderlichen Rechte, Songs zur Zielplaylist (\"" . $destPlaylist['name'] . "\" von \"" . $destPlaylist["owner"]["display_name"] . "\") hinzuzufügen.";
          }
          goto render;
        }

        $dbService = new DatabaseService();
        if($dbService->doesTaskExist($sourceId, $destPlaylistId)) {
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

  private function getUserIdFromLink($link) {
    $matches = [];
    $success = preg_match('/^(https:\/\/open\.spotify\.com\/user\/(.*)\?si=.*)$/', $link, $matches);
    if(! $success) {
      return null;
    }

    return $matches[2];
  }

}

?>