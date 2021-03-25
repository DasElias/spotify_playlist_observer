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

$state = $session->generateState();
$options = [
    'scope' => [
        'playlist-modify-public',
        'playlist-modify-private',
        'playlist-read-collaborative',
        'playlist-read-private',
    ],
    'state' => $state,
];

header('Location: ' . $session->getAuthorizeUrl($options));
die();