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

  public function query($id, $type, $isSourceAuthorized = null) {
    if($type == "user") return $this->getFavouriteSongs($id, $isSourceAuthorized);
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



}