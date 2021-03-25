<?php
require __DIR__ . '/../vendor/autoload.php';
require "utils.php";

setDefaultErrorHandler();
loadDotenv();
$session = startSpotifySession();
$api = createSpotifyApi($session);

if(!isset($_GET["uri"]) || !isset($_GET["operation"])) {
  die("Not all required parameters were supplied.");
}
$uri = $_GET["uri"];
$operation = $_GET["operation"];

// perform operation
$success = false;
if($operation == "added") {
  $success = $api->addPlaylistTracks($_ENV["DEST_PLAYLIST"], [
    $uri
  ]);    
} else if($operation == "removed") {
  $success = $api->deletePlaylistTracks($_ENV["DEST_PLAYLIST"], [
    "tracks" => [
      [
        "uri" => $uri
      ]
    ]
  ]);  
}

stopSpotifySession($session);

if(! $success) {
  echo("An error has occured!");
  print_r($success);
} else {
  // redirect to main page
  header('Location: app.php');

}

