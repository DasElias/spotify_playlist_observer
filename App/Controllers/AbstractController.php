<?php
namespace App\Controllers;

use App\TwigFactory;
use Dotenv\Dotenv;

abstract class AbstractController {
  protected $twig;

  public function __construct() {
    // Load dotenv
    $dotenv = Dotenv::createImmutable(__DIR__ . "/../..");
    $dotenv->load();

    // Load twig
    $dir = __DIR__ . "/../../views";
    $factory = new TwigFactory();
    $this->twig = $factory->create($dir, null);
  }

  protected function redirect($url) {
    header("Location: " . $url);
  }
}

?>