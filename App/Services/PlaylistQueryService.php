<?php
namespace App\Services;
use App\Models\WatchedPlaylist;


class PlaylistQueryService {
  private $userDatabaseService;
  private $spotifyService;

  public function __construct($userDatabaseService, $userId) {
    $this->userDatabaseService = $userDatabaseService;
    $this->spotifyService = new ApiSpotifyService($userDatabaseService, $userId);
  }

  public function query($id, $type, $isSourceAuthorized = null ,$destPlaylist = null) {
    if($type == "user") return $this->getFavouriteSongs($id, $isSourceAuthorized);
    else if ($type == "recommendations") return $this->getRecommendations($id, $destPlaylist);
    else return $this->getPlaylist($id);
  }

  private function getFavouriteSongs($id, $isSourceAuthorized) {
    $profile = $this->spotifyService->getUserProfile($id);

    $items = [];


    if($isSourceAuthorized && $this->userDatabaseService->doesUserExist($id)) {
      $foreignSpotifyService = new ApiSpotifyService($this->userDatabaseService, $id);
      $favSongs = $foreignSpotifyService->getFavouriteSongs();

      $items = $favSongs["items"];
    }

    $response = [
      "collaborative" => false,
      "name" => "Lieblingssongs",
      "id" => $id,
      "images" => $profile["images"],
      "owner" => [
        "id" => $profile["id"],
        "display_name" => $profile["display_name"]
      ],
      "tracks" => [
        "items" => $items
      ]
    ];
    return $response;
  }

  private function getPlaylist($id) {
    return $this->spotifyService->getPlaylist($id);
  }

  private function getRecommendations($id, $destPlaylist) {
    $destTracks = $destPlaylist["tracks"]["items"];
    $items = [];
    
    if(count($destTracks) > 0) {
      // get 5x recommendations from tracks
      for($i = 0; $i < 5; $i++) {
        $seedTracks = $this->getRandomFromTracks($destTracks);
        $apiResponse = $this->getFilteredRecommendations($seedTracks);
        $items = array_merge($items, $apiResponse["tracks"]);
      }
    }
    

    $response = [
      "collaborative" => false,
      "name" => "Empfehlungen für " . $destPlaylist["name"],
      "id" => $id,
      "images" => $destPlaylist["images"],
      "owner" => $destPlaylist["owner"],
      "tracks" => [
        "items" => $items
      ]
    ];
    return $response;
  }

  private function getFilteredRecommendations($seedTracks) {
    $filterStrings = ["Remix", "Mix", "Live"];
    $apiResponse = $this->spotifyService->getRecommendations($seedTracks);
    $apiResponse["tracks"] = array_filter($apiResponse["tracks"], function($track) use ($filterStrings) {
      return $this->containsNoString($track["track"]["name"], $filterStrings);
    });
    return $apiResponse;
  }

  private function containsNoString($string, $checkStrings) {
    foreach($checkStrings as $checkString) {
      if(strpos($string, $checkString) !== false) return false;
    }
    return true;
  }
 
  private function getRandomFromTracks($tracks) {
    $useNSourceTracks = 5;
    $rand_keys = array_rand($tracks, $useNSourceTracks);
    $randomTracks = [];
    foreach($rand_keys as $key) {
      $randomTracks[] = $tracks[$key]["track"]["id"];
    }
    return $randomTracks;
  }

}