<?php
require __DIR__ . '/../vendor/autoload.php';

/*
 * Load dotenv
 */
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();

$session = new SpotifyWebAPI\Session(
    $_ENV["CLIENT_ID"],
    $_ENV["CLIENT_SECRET"],
    $_ENV["REDIRECT_URI"]
);

$state = $_GET['state'];

// Fetch the stored state value from somewhere. A session for example

if ($state !== $storedState) {
    // The state returned isn't the same as the one we've stored, we shouldn't continue
    die('State mismatch');
}

// Request a access token using the code from Spotify
$session->requestAccessToken($_GET['code']);

$accessToken = $session->getAccessToken();
$refreshToken = $session->getRefreshToken();

// Store the access and refresh tokens somewhere. In a session for example

// Send the user along and fetch some data!
header('Location: app.php');
die();