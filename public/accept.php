<?php
require __DIR__ . '/../vendor/autoload.php';

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

// tokens might've been updated
$_SESSION["accessToken"] = $session->getAccessToken();
$_SESSION["refreshToken"] = $session->getRefreshToken();

if(! $success) {
  echo("An error has occured!");
  print_r($success);
} else {
  // redirect to main page
  header('Location: app.php');

}

