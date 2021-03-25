<?php

require __DIR__ . '/../vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

function req($method, $url) {
  $response = $client->request($method, $url);

  if($response->getStatusCode() != 200) {
    throw new RestException($response);
  }
  
  $body = $response->getBody();
  return json_decode($body);
}

function handleRequestError($response) {
  if($response->getStatusCode() != 200) {
    throw new RestException($response);
  }
  
}

/*
 * Load dotenv
 */
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();

$client = new Client();

// the playlist id can be obtained by taking the part between the "playlist/" and the "?si=" of the playlist URL
//$sourcePlaylistId = "33Es5qQSGM9upHSRx6JV4x";
$sourcePlaylistId = "1BJufThnh6omiDp3I9fZjS";
$destinationPlaylistId = "2bE9CRAlzhwFKL7MepYnaU";

try {
  // obtain access token
  $response = $client->request("POST", "https://accounts.spotify.com/api/token", 
    [
      'form_params' => [
        "grant_type" => "client_credentials"
      ],
      "headers" => [
        'Authorization' => "Basic " . base64_encode($_ENV["CLIENT_ID"] . ":" . $_ENV["CLIENT_SECRET"])
      ]
    ]
  );
  $body = json_decode($response->getBody());
  $token = $body->access_token;
  echo $token;

  // query source playlist
  $response = $client->request("GET", "https://api.spotify.com/v1/playlists/" . $sourcePlaylistId, 
    [
      "headers" => [
        'Authorization' => "Bearer " . $token
      ]
    ]
  );
  $sourcePlaylist = $response->getBody();  
  echo($sourcePlaylist);

  // insert item in destination playlist
  $urisToInsert = [
    "spotify:track:4h8VwCb1MTGoLKueQ1WgbD"
  ];
  $response = $client->request("POST", "https://api.spotify.com/v1/playlists/" . $destinationPlaylistId . "/tracks", 
    [
      'body' => json_encode([
        "uris" => $urisToInsert
      ]),
      "headers" => [
        'Authorization' => "Bearer " . $token
      ]
    ]
  );


} catch(ClientException $e) {
  print_r($e->getMessage());
  exit(-1);
}

?>