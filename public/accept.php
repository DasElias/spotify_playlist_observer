<?php
require __DIR__ . '/../vendor/autoload.php';

/*
 * Load dotenv
 */
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();
session_start();

$api = new SpotifyWebAPI\SpotifyWebAPI();

$accessToken = $_SESSION["accessToken"];
$api->setAccessToken($accessToken);

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

if(! $success) {
  echo("An error has occured!");
  print_r($success);
} else {
  /*
  // remove from changes
  $changesFilePath = $_ENV["CHANGES_SAVE_FILE"];

  if(file_exists($changesFilePath)) {
    $changes = json_decode(file_get_contents($changesFilePath) ,true);
    $filteredChanges = array_filter($changes, function($elem) use ($uri) {
      return $uri != $elem["uri"];
    });
    file_put_contents($changesFilePath, json_encode($filteredChanges));
  }
  
*/
  // redirect to main page
  header('Location: app.php');

}

