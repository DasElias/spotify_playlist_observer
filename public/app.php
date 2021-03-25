<?php
require __DIR__ . '/../vendor/autoload.php';

function getSongsInPlaylist($api, $playlistId) {
  $playlist = $api->getPlaylist($playlistId);
  $songs = [];
  foreach($playlist->tracks->items as $i) {
    array_push($songs, [
      "name" => $i->track->name,
      "uri" => $i->track->uri
    ]);
  }
  return $songs;
}

function getMissingElementsInDest($source, $dest) {
  $missingElementsInDest = [];

  foreach($source as $s) {
    $isPresent = false;
    foreach($dest as $d) {
      if($s == $d) {
        $isPresent = true;
      }
    }
    if(! $isPresent) {
      array_push($missingElementsInDest, $s);
    }
  }

  return $missingElementsInDest;
}

function mergeCurrentWithOldChanges($current, $destinationPlaylistSongs) {
  $changesFilePath = $_ENV["CHANGES_SAVE_FILE"];

  $changes = [];
  if(file_exists($changesFilePath)) {
    $changes = json_decode(file_get_contents($changesFilePath) ,true);
  }

  $changes = array_merge($changes, $current);
  // filter songs that were added and removed from the source playlist, but never accepted to the destination playlist
  $filteredChanges = array_filter($changes, function($elem) use ($changes) {
    $count = 0;
    foreach($changes as $c) {
      if($c["uri"] == $elem["uri"]) $count++;
    }
    return $count == 1;
  });

  // filter changes that are already present in the destination playlist
  $filteredChanges = array_filter($filteredChanges, function($elem) use ($destinationPlaylistSongs) {
    if($elem["type"] == "added") {
      foreach($destinationPlaylistSongs as $d) {
        if($d["uri"] == $elem["uri"]) return false;
      }
      return true;
    } else if($elem["type"] == "removed") {
      $isPresent = false;
      foreach($destinationPlaylistSongs as $d) {
        if($d["uri"] == $elem["uri"]) $isPresent = true;
      }
      return $isPresent;
    } else return false;
  });

  file_put_contents($changesFilePath, json_encode($filteredChanges));
  return $filteredChanges;
}

/*
 * Load dotenv
 */
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();
session_start();

$session = new SpotifyWebAPI\Session(
  $_ENV["CLIENT_ID"],
  $_ENV["CLIENT_SECRET"]
);

$accessToken = $_SESSION["accessToken"];
$refreshToken = $_SESSION["refreshToken"];
if($accessToken) {
  $session->setAccessToken($accessToken);
  $session->setRefreshToken($refreshToken);
} else {
  // Or request a new access token
  $session->refreshAccessToken($refreshToken);
}

$options = [
  'auto_refresh' => true,
];

$api = new SpotifyWebAPI\SpotifyWebAPI($options, $session);

// get songs in source playlist
$sourceSongs = getSongsInPlaylist($api, $_ENV["SOURCE_PLAYLIST"]);

$saveFilePath = $_ENV["PLAYLIST_SAVE_FILE"];
if(file_exists($saveFilePath)) {
  $savedSourceSongs = json_decode(file_get_contents($saveFilePath) ,true);

  $missingElementsInSaved = getMissingElementsInDest($sourceSongs, $savedSourceSongs);
  $missingElementsInFetched = getMissingElementsInDest($savedSourceSongs, $sourceSongs);

  $changes = [];
  foreach($missingElementsInSaved as $m) {
    array_push($changes, [
      "type" => "added",
      "uri" => $m["uri"],
      "name" => $m["name"]
    ]);
  }
  foreach($missingElementsInFetched as $m) {
    array_push($changes, [
      "type" => "removed",
      "uri" => $m["uri"],
      "name" => $m["name"]
    ]);
  }  

  $songsInDestinationPlaylist = getSongsInPlaylist($api, $_ENV["DEST_PLAYLIST"]);
  $changesToDisplay = mergeCurrentWithOldChanges($changes, $songsInDestinationPlaylist);
  if(empty($changesToDisplay)) {
    echo("Keine Veränderungen anzuzeigen.");
  } else {
    echo("<table><tr><th>Operation</th><th>Lied</th><th></th></tr>");
    foreach($changesToDisplay as $c) {
      $name = $c["name"];
      $uri = $c["uri"];
      $operation = $c["type"];
      $operationString = ($operation == "added" ? "Hinzufügen" : "Entfernen");
      echo("<tr><td>$operationString</td><td>$name</td><td><a href='accept.php?uri=$uri&operation=$operation'>Übernehmen</a></td></tr>");
    }
    echo("</table>");
  }
} else {
  echo("Keine Veränderungen anzuzeigen, da erster Aufruf.");
}
file_put_contents($saveFilePath, json_encode($sourceSongs));

// tokens might've been updated
$_SESSION["accessToken"] = $session->getAccessToken();
$_SESSION["refreshToken"] = $session->getRefreshToken();
