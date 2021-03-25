<?php

function setDefaultErrorHandler() {
  set_error_handler(function($level, $message, $file, $line, $context) {
    die("Ein unbekannter Fehler ist aufgetreten!");
  });
}

function loadDotenv() {
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
  $dotenv->load();
}

function startSpotifySession() {
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
  return $session;
}

function createSpotifyApi($session) {
  $options = [
    'auto_refresh' => true,
  ];
  
  $api = new SpotifyWebAPI\SpotifyWebAPI($options, $session);
  return $api;
}

function stopSpotifySession($session) {
  // tokens might've been updated
  $_SESSION["accessToken"] = $session->getAccessToken();
  $_SESSION["refreshToken"] = $session->getRefreshToken();
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