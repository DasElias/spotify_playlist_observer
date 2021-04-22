<?php
namespace App\Services;

use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\SpotifyWebAPIException;
use SpotifyWebAPI\SpotifyWebAPIAuthException;

class ApiSpotifyService extends SpotifyService {
  private $session;
  private $api;

  public function __construct() {
    parent::__construct();

    $this->session = new Session(
      $_ENV["CLIENT_ID"],
      $_ENV["CLIENT_SECRET"]
    );

    if($this->isRefreshTokenSet()) {
      $accessToken = $this->getAccessToken();
      $refreshToken = $this->getRefreshToken();
  
      if($accessToken) {
        $this->session->setAccessToken($accessToken);
        $this->session->setRefreshToken($refreshToken);
      } else {
        // Or request a new access token
        $this->session->refreshAccessToken($refreshToken);
      }
    } else {
      throw new RefreshTokenNotSetException();
    }

    $options = [
      'auto_refresh' => true,
      'return_assoc' => true
    ];
    $this->api = new SpotifyWebAPI($options, $this->session);
  }

  public function me() {
    return $this->api->me();
  }

  public function getPlaylist($playlistId) {
    try {
      // collaborative,name,id,images,owner(display_name,id),tracks(items(track(name,id,uri,preview_url,album(name, images),artists(id, name))))
      return $this->api->getPlaylist($playlistId);
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
    $success = $this->api->addPlaylistTracks($playlistId, $songUriArray); 
  }

}