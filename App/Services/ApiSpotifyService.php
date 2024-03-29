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
    if($this->user == null) {
      throw new UnauthorizedException("userId not found in database");
    }
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
    $itemFields = "items(track(name,id,uri,preview_url,linked_from(id, uri),album(name, images),artists(id, name)))";

    try {
      $playlist = $this->getApi()->getPlaylist($playlistId, [
        "fields" => "collaborative,name,id,images,owner(display_name,id),tracks(total, limit, " . $itemFields . ")",
        "market" => $_ENV["MARKET"]
      ]);
      
      $limit = $playlist["tracks"]["limit"];
      $offset = $limit;
      $remaining = max(0, $playlist["tracks"]["total"] - $limit);


      while($remaining > 0) {
        $additionalTracks = $this->getApi()->getPlaylistTracks($playlistId, [
          "fields" => $itemFields,
          "limit" => $limit,
          "offset" => $offset,
          "market" => $_ENV["MARKET"]
        ]);
        
        array_push($playlist["tracks"]["items"], ...$additionalTracks["items"]);

        $offset += $limit;
        $remaining= max(0, $remaining - $limit);
      }

      $playlist["tracks"]["items"] = $this->handleLinkedTracks($playlist["tracks"]["items"]);
      $playlist["tracks"]["items"] = $this->filterNullTracks($playlist["tracks"]["items"]);

      return $playlist;
    } catch(SpotifyWebAPIAuthException $e) {
      throw new UnauthorizedException("Can't access playlist " . $playlistId, $e->getCode(), $e);
    } catch(SpotifyWebAPIException $e) {
      if($e->getCode() == 404 || $e->getCode() == 400) {
        throw new PlaylistDoesntExistException("Playlist " . $playlistId . " does not exist.", $e->getCode(), $e);
      } 
      throw $e;
    }
  }

  public function me() {
    return $this->getApi()->me();
  }

  public function getUserProfile($userId) {
    return $this->getApi()->getUser($userId);
  }

  public function getFavouriteSongs() {
    $return = $this->getItems(function($api, $args) {
        return $api->getMySavedTracks($args);
      }, 
      [
        "market" => $_ENV["MARKET"]
      ]
    );

    $return["items"] = $this->handleLinkedTracks($return["items"]);
    $return["items"] = $this->filterNullTracks($return["items"]);

    return $return;
  }

  public function getUserPlaylists() {
    $return = $this->getItems(function($api, $args) {
        return $api->getMyPlaylists($args);
      }
    );

    return $return;
  }

  public function getInsertableUserPlaylists() {
    $userId = $this->getUserId();
    $playlists = $this->getUserPlaylists();
    $filteredItems = array_filter($playlists["items"], function($elem) use($userId) {
      return $elem["owner"]["id"] == $userId && !$elem["collaborative"];
    });
    $playlists["items"] = array_values($filteredItems);

    return $playlists;
  }

  private function getItems($callback, $params = []) {
    $total = 0;
    $offset = 0;
    $limit = 50;

    $return = [
      "items" => []
    ];

    do {
      $r = $callback(
        $this->getApi(),
        [
          "offset" => $offset,
          "limit" => $limit,
          ...$params
        ]
      );
      
      $total = $r["total"];
      $offset = $offset + $limit;
      $return["items"] = array_merge($r["items"], $return["items"]);
    } while($total > $offset);

    return $return;
  }

  public function addSongsToPlaylist($playlistId, $songUriArray) {
    if(empty($songUriArray)) return;
    $success = $this->getApi()->addPlaylistTracks($playlistId, $songUriArray); 
  }

  private function handleLinkedTracks($items) {
    foreach($items as $value) {
      $track = $value["track"];

      if(isset($track["linked_from"])) {
        $track["id"] = $track["linked_from"]["id"];
        $track["uri"] = $track["linked_from"]["uri"];
      }
    }



    return $items;
  }

  private function filterNullTracks($items) {
    return array_filter($items, function($i) {
      return $i["track"] !== null;
    });
  }

  public function getRecommendations($seedTracks) {
    // we can pass only 4 seed tracks
    $seedTracks = array_slice($seedTracks, 0, 5, true);

    $response = $this->getApi()->getRecommendations([
      "seed_tracks" => $seedTracks,
      "limit" => 20,
      "market" => $_ENV["MARKET"]
    ]);
    
    // warp all responses in track attribute
    for($i = 0; $i < count($response["tracks"]); $i++) {
      $response["tracks"][$i] = [
        "track" => $response["tracks"][$i]
      ];
    }

    return $response;
  }
}