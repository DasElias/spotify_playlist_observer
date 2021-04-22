<?php
namespace App\Models;

class WatchedPlaylist {
  private $dbDocument;
  private $isPresentInDb;

  private function __construct($dbDocument, $isPresentInDb) {
    $this->dbDocument = $dbDocument;
    $this->isPresentInDb = $isPresentInDb;
  }

  public static function withDbDocument($dbDocument) {
    return new self($dbDocument, true);
  }

  public static function withApiResponse($src, $dest) {
    $dbDocument = [
      "lastSourceTracks" => [],
      "trackedChanges" => []
    ];
    $dbDocument = WatchedPlaylist::writeMetadata($dbDocument, $src, $dest);
    return new self($dbDocument, false);
  }

  private static function writeMetadata($dbDocument, $srcApiResponse, $destApiResponse) {
    $dbDocument["sourceId"] = $srcApiResponse["id"];
    $dbDocument["sourceName"] = $srcApiResponse["name"];
    $dbDocument["sourceImages"] = $srcApiResponse["images"];
    $dbDocument["sourceOwner"] = $srcApiResponse["owner"];
    $dbDocument["destId"] = $destApiResponse["id"];
    $dbDocument["destName"] = $destApiResponse["name"];
    $dbDocument["destImages"] = $destApiResponse["images"];
    $dbDocument["destOwner"] = $destApiResponse["owner"];
    return $dbDocument;
  }

  private static function findSongsNotPresentInDest($source, $dest) {
    $added = [];

    foreach($source as $s) {
      $isPresent = false;
      foreach($dest as $l) {
        if($s["track"]["id"] == $l["track"]["id"]) {
          $isPresent = true;
          break;
        }
      }
      if(! $isPresent) {
        array_push($added, $s);
      }
    }
    return $added;
  }

  private static function merge($a, $b) {
    foreach($b as $i) {
      array_push($a, $i);
    }
    return $a;
  }

  public function update($srcApiResponse, $destApiResponse) {
    $this->dbDocument = WatchedPlaylist::writeMetadata($this->dbDocument, $srcApiResponse, $destApiResponse);
    $currentSourceTracks = $srcApiResponse["tracks"]["items"];
    $destTracks = $destApiResponse["tracks"]["items"];

    $addedSongs = WatchedPlaylist::findSongsNotPresentInDest($currentSourceTracks, $this->dbDocument["lastSourceTracks"]);
    $allChanges = WatchedPlaylist::merge($addedSongs, $this->dbDocument["trackedChanges"]);
    $allChanges = WatchedPlaylist::findSongsNotPresentInDest($allChanges, $destTracks);

    $this->dbDocument["lastSourceTracks"] = $currentSourceTracks;
    $this->dbDocument["trackedChanges"] = $allChanges;
  }

  public function removeSongFromChanges($songUri) {
    $newChanges = [];
    foreach($this->dbDocument["trackedChanges"] as $change) {
      if($change["track"]["uri"] != $songUri) {
        array_push($newChanges, $change);
      }
    }
    $this->dbDocument["trackedChanges"] = $newChanges;
  }

  public function removeAllChangesAndGetUris() {
    $uris = [];
    foreach($this->dbDocument["trackedChanges"] as $change) {
      array_push($uris, $change["track"]["uri"]);
    }
    $this->dbDocument["trackedChanges"] = [];

    return $uris;
  }

  public function delete() {
    $this->dbDocument["wasDeleted"] = true;
  }

  public function restore() {
    $this->dbDocument["wasDeleted"] = false;
  }

  public function getArtistString($playlistItem) {
    $str = "";
    $artists = $playlistItem["track"]["artists"];
    $len = count($artists);
    foreach($artists as $index => $p) {
      $str = $str . $p["name"];

      if($index != $len - 1) {
        $str = $str . ", ";
      }
    }
    return $str;
  }

  public function getAddedSongs() {
    return $this->dbDocument["trackedChanges"];
  }

  public function getDbId() {
    return $this->isPresentInDb ? $this->dbDocument["_id"] : null;
  }

  public function getSourceId() {
    return $this->dbDocument["sourceId"];
  }

  public function getDestId() {
    return $this->dbDocument["destId"];
  }

  public function getDocument() {
    return $this->dbDocument;
  }
  

}
