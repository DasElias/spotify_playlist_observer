<?php
namespace App\Services;
use App\Models\{WatchedPlaylist, User};
use MongoDB\Client;
use MongoDB\BSON\ObjectID;

class UserDatabaseService {
  private $collection;

  public function __construct() {
    $this->collection = (new Client)->selectDatabase($_ENV["MONGODB_DATABASE"])->users;

  }

  public function getUser($userId) {
    $user = $this->collection->findOne([
      "userId" => $userId
    ]);

    if($user) return new User(
      $userId,
      $user["accessToken"],
      $user["refreshToken"]
    );
    else return null;
  }

  public function saveUser($user) {
    $this->collection->replaceOne(
      ["userId" => $user->getUserId() ],
      $this->generateDocumentForUser($user),
      ["upsert" => true]
    );
  }

  private function generateDocumentForUser($user) {
    return [
      "userId" => $user->getUserId(),
      "accessToken" => $user->getAccessToken(),
      "refreshToken" => $user->getRefreshToken()
    ];
  }
}