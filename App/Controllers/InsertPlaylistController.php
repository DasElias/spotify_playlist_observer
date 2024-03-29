<?php
namespace App\Controllers;
use App\Models\WatchedPlaylist;
use App\Services\{PlaylistQueryService, ApiSpotifyService, DatabaseService, RetrieveChangesService, UserDatabaseService, TaskAlreadyExistsException, RefreshTokenNotSetException, OwnerDoesntMatchException, UnauthorizedException, PlaylistDoesntExistException};

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

      if($_POST["source"] == "playlist" && isset($_POST["sourcePlaylist"]) && !empty($_POST["sourcePlaylist"])) {
        $source = "playlist";
        $isSourceAuthorized = true;
        $sourceId = $this->getPlaylistIdFromLink($_POST["sourcePlaylist"]);
      } else if($_POST["source"] == "user" && isset($_POST["sourceUser"]) && !empty($_POST["sourceUser"])) {
        $source = "user";
        $isSourceAuthorized = false;
        $sourceId = $this->getUserIdFromLink($_POST["sourceUser"]);
      } else if($_POST["source"] == "recommendations") {
        $source = "recommendations";
        $isSourceAuthorized = true;
        $sourceId = $this->getPlaylistIdFromLink($_POST["destPlaylist"]) . "&recommendations=true";
      } else {
        $errorMsg = "Quelle inkorrekt";
        goto render;
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
        $retrieveChangesService = new RetrieveChangesService($this->getUserId());
        $retrieveChangesService->insertNewPlaylist($sourceId, $source, $destPlaylistId, $dest, $isSourceAuthorized);

        $this->redirect("listPlaylists.php");
      } catch(OwnerDoesntMatchException $e) {
        $errorMsg = "Du hast nicht die erforderlichen Rechte, Songs zur Zielplaylist hinzuzufügen.";
        if($e->isDestCollaborative()) $e = $e . " Leider kann momentan nur der Eigentümer von gemeinsamen Playlists diese als Zielplaylist auswählen.";
        goto render;
      } catch (TaskAlreadyExistsException $e) {
        $errorMsg = "Änderungen in der Playlist werden bereits übernommen.";
        goto render;
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
    $spotifyService = new ApiSpotifyService(new UserDatabaseService(), $this->getUserId());
    $playlists = $spotifyService->getInsertableUserPlaylists();
    usort($playlists["items"], function($a, $b) {
      return strcmp($a["name"], $b["name"]);
    });

    $params = [
      "errorMsg" => $errorMsg,
      "playlists" => $playlists
    ];
    $twig = $this->loadTwig();
    echo $twig->render("pages/p-insert.twig", $params);
  }

  private function getPlaylistIdFromLink($link) {
    $matches = [];
    $success = preg_match('/^(https:\/\/open\.spotify\.com\/playlist\/([^?]+)(\?)?(si=.*)?)$/', $link, $matches);
    if(! $success) {
      return null;
    }

    return $matches[2];
  }

  private function getUserIdFromLink($link) {
    $matches = [];
    $success = preg_match('/^(https:\/\/open\.spotify\.com\/user\/([^?]+)(\?)?(si=.*)?)$/', $link, $matches);
    if(! $success) {
      return null;
    }

    return $matches[2];
  }

}

?>