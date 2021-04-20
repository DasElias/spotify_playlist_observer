<?php
namespace App\Controllers;


class ViewChangesController extends AbstractController {

  public function __construct() {
    parent::__construct();

  }


  public function show() {
    $params = [
      
    ];
    echo $this->twig->render("pages/p-changes.twig", $params);
  }

}

?>