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

  // type is either "playlist" or "user" for the favourite songs
  public static function withApiResponse($src, $dest, $srcType, $destType = "playlist") {
    $dbDocument = [
      "sourceType" => $srcType,
      "destType" => "playlist",
      "lastSourceTracks" => [],
      "trackedChanges" => [],
      "isSourceAuthorized" => $srcType == "playlist"
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

  /*
   * in order to make sure that the links for the album cover and the preview don't expire,
   * we overwrite them every time in case they have changed
   */
  private static function updateChangeLinks($dbDocument, $srcApiResponse) {
    foreach($dbDocument["trackedChanges"] as $key => $s) {
      foreach($srcApiResponse["tracks"]["items"] as $r) {
        if($s["track"]["id"] == $r["track"]["id"]) {
          $songs["trackedChanges"][$key] = $r;
          break;
        }
      }
    }
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

  private static function filterDuplicates($arr) {
    $noDuplicates = [];

    foreach($arr as $keyA => $a) {
      $isInArray = false;
      foreach($noDuplicates as $keyB => $b) {
        if($a["track"]["id"] == $b["track"]["id"]) {
          $isInArray = true;
          break;
        }
      }

      if(! $isInArray) {
        array_push($noDuplicates, $a);
      }
    }

    return $noDuplicates;
  }

  private static function merge($a, $b) {
    foreach($b as $i) {
      array_push($a, $i);
    }
    return $a;
  }

  public function update($srcApiResponse, $destApiResponse) {
    $this->dbDocument = WatchedPlaylist::writeMetadata($this->dbDocument, $srcApiResponse, $destApiResponse);
    $this->dbDocument = WatchedPlaylist::updateChangeLinks($this->dbDocument, $srcApiResponse);

    $currentSourceTracks = $srcApiResponse["tracks"]["items"];
    $destTracks = $destApiResponse["tracks"]["items"];

    $addedSongs = WatchedPlaylist::findSongsNotPresentInDest($currentSourceTracks, $this->dbDocument["lastSourceTracks"]);
    $allChanges = WatchedPlaylist::merge($addedSongs, $this->dbDocument["trackedChanges"]);

    // filter songs that are present in destination playlist
    $allChanges = WatchedPlaylist::findSongsNotPresentInDest($allChanges, $destTracks);

    // filter duplicates
    $allChanges = WatchedPlaylist::filterDuplicates($allChanges);

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

  public function authorizeSource() {
    $this->dbDocument["isSourceAuthorized"] = true;
  }

  public function isSourceAuthorized() {
    if(! isset($this->dbDocument["isSourceAuthorized"])) return true;
    else return $this->dbDocument["isSourceAuthorized"];
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

  public function getSourceOwnerId() {
    return $this->dbDocument["sourceOwner"]["id"];
  }

  public function getDestOwnerId() {
    return $this->dbDocument["destOwner"]["id"];
  }

  public function getSourceOwnerName() {
    return $this->dbDocument["sourceOwner"]["display_name"];
  }

  public function getDestOwnerName() {
    return $this->dbDocument["destOwner"]["display_name"];
  }

  public function getSourceType() {
    if(! isset($this->dbDocument["sourceType"])) return "playlist";
    else return $this->dbDocument["sourceType"];
  }

  public function getDestType() {
    if(! isset($this->dbDocument["destType"])) return "playlist";
    else return $this->dbDocument["destType"];
  }

  public function getDocument() {
    return $this->dbDocument;
  }
  

}
