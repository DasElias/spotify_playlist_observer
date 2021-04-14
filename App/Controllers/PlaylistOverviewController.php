<?php
namespace App\Controllers;


class PlaylistOverviewController extends AbstractController {

  public function __construct() {
    parent::__construct();

  }


  public function show() {
    $params = [

    ];
    echo $this->twig->render("pages/p-playlistoverview.twig", $params);
  }

}

?>