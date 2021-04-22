<?php
namespace App\Services;
use App\Models\WatchedPlaylist;
use MongoDB\Client;
use MongoDB\BSON\ObjectID;

class DatabaseService {
  private $collection;

  public function __construct() {
    $this->collection = (new Client)->playlistForker->watchedPlaylists;

  }

  public function getPlaylist($id) {
    $playlist = $this->collection->findOne(["_id" => new ObjectID($id)]);
    if($playlist) {
      return WatchedPlaylist::withDbDocument($playlist);
    } else return null;
  }

  public function getPlaylists() {
    $cursor = $this->collection->find(["wasDeleted" => ['$ne' => true]]);
    $playlists = [];
    foreach($cursor as $i) {
      array_push($playlists, WatchedPlaylist::withDbDocument($i));
    }
    return $playlists;
  }

  public function getPlaylistsAsDocuments() {
    $cursor = $this->collection->find(["wasDeleted" => ['$ne' => true]]);
    return $cursor->toArray();
  }

  public function doesTaskExist($sourcePlaylistId, $destPlaylistId) {
    $cursor = $this->collection->find(["sourceId" => $sourcePlaylistId, "destId" => $destPlaylistId, "wasDeleted" => ['$ne' => true]]);
    return count($cursor->toArray()) >= 1;
  }

  public function savePlaylist($playlist) {
    $this->collection->replaceOne(
      ["_id" => $playlist->getDbId() == null ? new ObjectId() : new ObjectId($playlist->getDbId()) ],
      $playlist->getDocument(),
      ["upsert" => true]
    );
  }



}