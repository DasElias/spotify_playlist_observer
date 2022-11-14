<?php
namespace App\Controllers;
use App\Models\{WatchedPlaylist, ImageChooser};
use App\Services\{ApiSpotifyService, DatabaseService, UserDatabaseService, RetrieveChangesService,  RefreshTokenNotSetException};

class GenerateSongsController extends AbstractUserIdController {

  public function __construct() {
    parent::__construct();

  }

  public function show() {
    $taskId = $this->retrieveGetVar("taskId");

    try {
      $retreiveChangesService = new RetrieveChangesService($this->getUserId());
      $taskIntercepter = function($task) {
        $task->removeAllChangesAndGetUris();
      };
      $playlist = $retreiveChangesService->saveUpdatedPlaylist($taskId, $taskIntercepter);
      $this->ensureVarIsset($playlist);

     $this->redirectIfDesired("viewChanges.php?id=" . $taskId);
    } catch(RefreshTokenNotSetException $e) {
      $this->redirectIfDesired("index.php", 401); 
      return;
    }
  }
}

?>