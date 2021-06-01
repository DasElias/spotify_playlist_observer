<?php
namespace App\Controllers;
use App\Models\{WatchedPlaylist, ImageChooser};
use App\Services\{DatabaseService, UserDatabaseService};

class AuthorizeSourceController extends AbstractUserIdController {

  public function __construct() {
    parent::__construct();

  }

  public function show() {
    if(! isset($_GET["id"])) {
      $this->redirect("listPlaylists.php");
      return;
    }

    $dbService = new DatabaseService(); 
    $playlist = $dbService->getTaskSuppressOwnerCheck($_GET["id"]);
    if($playlist->getSourceOwnerId() == $this->getUserId()) {
      $playlist->authorizeSource();
      $dbService->saveTask($playlist);
    }

    $this->redirect("listPlaylists.php");
  }
}

?>