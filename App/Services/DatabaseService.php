<?php
namespace App\Services;
use App\Models\WatchedPlaylist;
use MongoDB\Client;
use MongoDB\BSON\ObjectID;

class DatabaseService {
  private $collection;

  public function __construct() {
    $this->collection = (new Client($_ENV["MONGODB_CONNSTRING"]))->selectDatabase($_ENV["MONGODB_DATABASE"])->watchedPlaylists;

  }

  public function getTask($id, $userId) {
    $playlist = $this->collection->findOne([
      "_id" => new ObjectID($id),
      "destOwner.id" => $userId
    ]);

    if($playlist) {
      return WatchedPlaylist::withDbDocument($playlist);
    } else return null;
  }

  public function getTaskSuppressOwnerCheck($id) {
    $playlist = $this->collection->findOne([
      "_id" => new ObjectID($id)
    ]);

    if($playlist) {
      return WatchedPlaylist::withDbDocument($playlist);
    } else return null;
  }

  public function getTasks($userId) {
    $cursor = $this->collection->find([
      "wasDeleted" => ['$ne' => true],
      "destOwner.id" => $userId
    ]);
    $playlists = [];
    foreach($cursor as $i) {
      array_push($playlists, WatchedPlaylist::withDbDocument($i));
    }
    return $playlists;
  }

  public function getTasksAsDocuments($userId) {
    $cursor = $this->collection->find(
      ["wasDeleted" => ['$ne' => true],
      "destOwner.id" => $userId
    ]);
    return $cursor->toArray();
  }

  public function getTasksWaitingForAuthorization($userId) {
    $cursor = $this->collection->find(
      ["wasDeleted" => ['$ne' => true],
      "sourceOwner.id" => $userId,
      "sourceType" => "user",
      "isSourceAuthorized" => ['$ne' => true] 
    ]);
    $playlists = [];
    foreach($cursor as $i) {
      array_push($playlists, WatchedPlaylist::withDbDocument($i));
    }
    return $playlists;
  }

  public function doesTaskExist($sourcePlaylistId, $destPlaylistId) {
    $cursor = $this->collection->find([
      "sourceId" => $sourcePlaylistId, 
      "destId" => $destPlaylistId, 
      "wasDeleted" => ['$ne' => true]
    ]);

    return count($cursor->toArray()) >= 1;
  }

  public function saveTask($playlist) {
    $this->collection->replaceOne(
      ["_id" => $playlist->getDbId() == null ? new ObjectId() : new ObjectId($playlist->getDbId()) ],
      $playlist->getDocument(),
      ["upsert" => true]
    );
  }



}