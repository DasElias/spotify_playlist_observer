<?php
namespace App\Controllers;
use App\Services\{ApiSpotifyService, DatabaseService, RefreshTokenNotSetException};
use App\Models\ImageChooser;

class PlaylistOverviewController extends AbstractController {

  public function __construct() {
    parent::__construct();

  }


  public function show() {
    $restoreableTask = null;

    if(isset($_GET["restoreableTask"])) {
      $_SESSION["restoreableTask"] = $_GET["restoreableTask"];
      $this->redirect("listPlaylists.php");
      die;
    } 

    if(isset($_SESSION["restoreableTask"])) {
      $restoreableTask = $_SESSION["restoreableTask"];
      unset($_SESSION["restoreableTask"]);
    }

    try {
      $spotifyService = new ApiSpotifyService();
      $dbService = new DatabaseService(); 
      $playlists = $dbService->getTasksAsDocuments($spotifyService->getUserId());

      $params = [
        "playlists" => $playlists,
        "imageChooser" => new ImageChooser(),
        "restoreableTask" => $restoreableTask
      ];
      
      $twig = $this->loadTwig();
      echo $twig->render("pages/p-playlistoverview.twig", $params);
    } catch(RefreshTokenNotSetException $e) {
      $this->redirect("index.php"); 
      return;
    }

    
  }

}

?>