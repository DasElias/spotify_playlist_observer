<?php
namespace App\Controllers;


class InsertPlaylistController extends AbstractController {

  public function __construct() {
    parent::__construct();

  }


  public function show() {
    $params = [
      
    ];
    echo $this->twig->render("pages/p-insert.twig", $params);
  }

}

?>