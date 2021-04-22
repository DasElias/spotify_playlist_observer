<?php
namespace App\Controllers;
use App\Services\DatabaseService;
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

    $dbService = new DatabaseService(); 
    $playlists = $dbService->getPlaylistsAsDocuments();

    $params = [
      "playlists" => $playlists,
      "imageChooser" => new ImageChooser(),
      "restoreableTask" => $restoreableTask
    ];
    
    $twig = $this->loadTwig();
    echo $twig->render("pages/p-playlistoverview.twig", $params);
  }

}

?>