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

$api = new SpotifyWebAPI\SpotifyWebAPI();

if (isset($_GET['code'])) {
    $session->requestAccessToken($_GET['code']);
    $api->setAccessToken($session->getAccessToken());

    $sourcePlaylist = $api->getPlaylist($_ENV["SOURCE_PLAYLIST"]);
    $sourceSongs = [];
    foreach($sourcePlaylist->tracks->items as $i) {
      array_push($sourceSongs, [
        "name" => $i->track->name,
        "uri" => $i->track->uri
      ]);
    }
    print_r($sourceSongs);
} else {
    $options = [
      'scope' => [
        'playlist-modify-public',
        'playlist-modify-private',
        'playlist-read-collaborative',
        'playlist-read-private',
      ],
    ];

    header('Location: ' . $session->getAuthorizeUrl($options));
    die();
}
?>