<?php
namespace App\Services;

use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\SpotifyWebAPIException;
use SpotifyWebAPI\SpotifyWebAPIAuthException;

class ApiSpotifyService extends SpotifyService {
  private $apiOrNull;

  public function __construct() {
    parent::__construct();
  }

  private function getApi() {
    if($this->apiOrNull == null) {
      $session = new Session(
        $_ENV["CLIENT_ID"],
        $_ENV["CLIENT_SECRET"]
      );
  
      if($this->isRefreshTokenSet()) {
        $accessToken = $this->getAccessToken();
        $refreshToken = $this->getRefreshToken();
    
        if($accessToken) {
          $session->setAccessToken($accessToken);
          $session->setRefreshToken($refreshToken);
        } else {
          // Or request a new access token
          $session->refreshAccessToken($refreshToken);
        }
      } else {
        throw new RefreshTokenNotSetException();
      }
  
      $options = [
        'auto_refresh' => true,
        'return_assoc' => true
      ];
      $this->apiOrNull = new SpotifyWebAPI($options, $session);
    }

    return $this->apiOrNull;
  }

  public function getUserId() {
    if(isset($_SESSION["userId"])) {
      return $_SESSION["userId"];
    } else {
      $me = $this->getApi()->me();
      $id = $me["id"];
      $_SESSION["userId"] = $id;
      return $id;
    }
  }

  public function getPlaylist($playlistId) {
    try {
      return $this->getApi()->getPlaylist($playlistId, [
        "fields" => "collaborative,name,id,images,owner(display_name,id),tracks(items(track(name,id,uri,preview_url,album(name, images),artists(id, name))))",
        "market" => $_ENV["MARKET"]
      ]);
    } catch(SpotifyWebAPIAuthException $e) {
      throw new UnauthorizedException("Can't access playlist " . $playlistId, $e->getCode(), $e);
    } catch(SpotifyWebAPIException $e) {
      if($e->getCode() == 404) {
        throw new PlaylistDoesntExistException("Playlist " . $playlistId . " does not exist.", $e->getCode(), $e);
      } 
      throw $e;
    }
  }

  public function addSongsToPlaylist($playlistId, $songUriArray) {
    if(empty($songUriArray)) return;
    $success = $this->getApi()->addPlaylistTracks($playlistId, $songUriArray); 
  }

}