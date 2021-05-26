<?php
namespace App\Models;

class User {
  private $userId;
  private $accessToken;
  private $refreshToken;

  public function __construct($userId, $accessToken, $refreshToken) {
    $this->userId = $userId;
    $this->accessToken = $accessToken;
    $this->refreshToken = $refreshToken;
  }

  public function getUserId() {
    return $this->userId;
  }
  
  public function getAccessToken() {
    return $this->accessToken;
  }

  public function setAccessToken($accessToken) {
    $this->accessToken = $accessToken;
  }

  public function getRefreshToken() {
    return $this->refreshToken;
  }

  public function setRefreshToken($refreshToken) {
    $this->refreshToken = $refreshToken;
  }

  public function isRefreshTokenSet() {
    return isset($this->refreshToken);
  }

}