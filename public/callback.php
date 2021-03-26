<?php
require __DIR__ . '/../vendor/autoload.php';
require "utils.php";

session_start();
setDefaultErrorHandler();
loadDotenv();

$session = new SpotifyWebAPI\Session(
    $_ENV["CLIENT_ID"],
    $_ENV["CLIENT_SECRET"],
    $_ENV["REDIRECT_URI"]
);

if(! (isset($_GET["state"]) && isset($_SESSION["state"]))) {
    header('Location: index.php');
    exit(0);
}

$state = $_GET['state'];
$storedState = $_SESSION["state"];
if ($state !== $storedState) {
    // The state returned isn't the same as the one we've stored, we shouldn't continue
    die('State mismatch');
}

// Request a access token using the code from Spotify
$session->requestAccessToken($_GET['code']);

$_SESSION["accessToken"] = $session->getAccessToken();
$_SESSION["refreshToken"] = $session->getRefreshToken();

// Send the user along and fetch some data!
header('Location: app.php');