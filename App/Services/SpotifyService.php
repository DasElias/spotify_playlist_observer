<?php
namespace App\Services;

class SpotifyService {
  protected function __construct() {
    
  }

  protected function isRefreshTokenSet() {
    return isset($_SESSION["refreshToken"]);
  }

  protected function getAccessToken() {
    return $_SESSION["accessToken"];
  }

  protected function getRefreshToken() {
    return $_SESSION["refreshToken"];
  }

  protected function saveAccessToken($accessToken) {
    $_SESSION["accessToken"] = $accessToken;
  }

  protected function saveRefreshToken($refreshToken) {
    $_SESSION["refreshToken"] = $refreshToken;
  }

 
}