<?php
require __DIR__ . '/../vendor/autoload.php';
require "utils.php";

session_start();
setDefaultErrorHandler();
loadDotenv();
$session = startSpotifySession();
$api = createSpotifyApi($session);

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
stopSpotifySession($session);