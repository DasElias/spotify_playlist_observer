<?php
namespace App\Services;
use App\Models\User;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;

class LoginSpotifyService {
  private $session;

  public function __construct() {
    $this->session = new Session(
      $_ENV["CLIENT_ID"],
      $_ENV["CLIENT_SECRET"],
      $this->getRedirectUri()
    );
  }

  public function authorize() {
    $state = $this->session->generateState();
    $options = [
      'scope' => [
          'playlist-modify-public',
          'playlist-modify-private',
          'playlist-read-collaborative',
          'playlist-read-private',
          'user-read-private',
          'user-library-read'
      ],
      'state' => $state,
    ];
    $_SESSION["state"] = $state;

    return $this->session->getAuthorizeUrl($options);
  }

  public function authorizeCallbackAndGetUserId($userDatabaseService) {
    if(! (isset($_GET["state"]) && isset($_SESSION["state"]))) {
      throw new RefreshTokenNotSetException();
    }

    $state = $_GET['state'];
    $storedState = $_SESSION["state"];
    if ($state !== $storedState) {
      // The state returned isn't the same as the one we've stored, we shouldn't continue
      throw new StateMismatchException();
    }

    // Request a access token using the code from Spotify
    $this->session->requestAccessToken($_GET['code']);

    $options = [
      'auto_refresh' => true,
      'return_assoc' => true
    ];
    $api = new SpotifyWebAPI($options, $this->session);
    $userId = $api->me()["id"];

    $user = new User(
        $userId, 
        $this->session->getAccessToken(),
        $this->session->getRefreshToken());

    $userDatabaseService->saveUser($user);
    return $userId;
  }

  private function getRedirectUri() {
    return $_ENV["BASE_URL"] . "callback.php";
  }
}