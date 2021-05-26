<?php
namespace App\Services;

use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\SpotifyWebAPIException;
use SpotifyWebAPI\SpotifyWebAPIAuthException;

class ApiSpotifyService {
  private $apiOrNull;
  private $sessionOrNull;
  private $userDatabaseService;
  private $user;

  public function __construct($userDatabaseService, $userId) {
    $this->userDatabaseService = $userDatabaseService;
    $this->user = $userDatabaseService->getUser($userId);
  }

  public function __destruct() {
    if($this->sessionOrNull != null) {
      $this->user->setAccessToken($this->sessionOrNull->getAccessToken());
      $this->user->setRefreshToken($this->sessionOrNull->getRefreshToken());
    }
  }

  private function getApi() {
    if($this->apiOrNull == null) {
      $this->sessionOrNull = new Session(
        $_ENV["CLIENT_ID"],
        $_ENV["CLIENT_SECRET"]
      );
  
      if($this->user->isRefreshTokenSet()) {
        $accessToken = $this->user->getAccessToken();
        $refreshToken = $this->user->getRefreshToken();
    
        if($accessToken) {
          $this->sessionOrNull->setAccessToken($accessToken);
          $this->sessionOrNull->setRefreshToken($refreshToken);
        } else {
          // Or request a new access token
          $this->sessionOrNull->refreshAccessToken($refreshToken);
        }
      } else {
        throw new RefreshTokenNotSetException();
      }
  
      $options = [
        'auto_refresh' => true,
        'return_assoc' => true
      ];
      $this->apiOrNull = new SpotifyWebAPI($options, $this->sessionOrNull);
    }

    return $this->apiOrNull;
  }

  public function getUserId() {
    return $this->user->getUserId();
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