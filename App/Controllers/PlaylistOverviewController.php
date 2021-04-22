<?php
namespace App\Controllers;
use App\Services\DatabaseService;
use App\Models\ImageChooser;

class PlaylistOverviewController extends AbstractController {

  public function __construct() {
    parent::__construct();

  }


  public function show() {
    $dbService = new DatabaseService(); 
    $playlists = $dbService->getPlaylistsAsDocuments();

    $params = [
      "playlists" => $playlists,
      "imageChooser" => new ImageChooser()
    ];
    
    $twig = $this->loadTwig();
    echo $twig->render("pages/p-playlistoverview.twig", $params);
  }

}

?>