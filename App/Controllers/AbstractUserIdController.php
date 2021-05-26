<?php
namespace App\Controllers;
use App\Services\UserIdService;

abstract class AbstractUserIdController extends AbstractController {
  private $userId;

  public function __construct($options = []) {
    parent::__construct();

    $this->userId = $this->isUserIdSet() ? $_SESSION["userId"] : null;
    if(((! array_key_exists("suppressUserIdCheck", $options)) || (! $options["suppressUserIdCheck"])) && $this->userId == null) {
      $this->redirect("index.php");
      die;
    }
  }

  protected function getUserId() {
    return $this->userId;
  }

  public function writeUserId($userId) {
    $_SESSION["userId"] = $userId;
    $this->userId = $userId;
  }

  private function isUserIdSet() {
    return isset($_SESSION["userId"]);
  }
}

?>