<?php
namespace App\Controllers;

use App\TwigFactory;
use Dotenv\Dotenv;

abstract class AbstractController {
  public function __construct() {
    $this->loadDotenv();
    $this->setExceptionHandler();
    session_start();
  }

  protected function loadTwig() {
    $dir = __DIR__ . "/../../views";
    $factory = new TwigFactory();
    return $factory->create($dir, null);
  }

  protected function redirect($url) {
    header("Location: " . $url);
  }

  private function loadDotenv() {
    $dotenv = Dotenv::createImmutable(__DIR__ . "/../..");
    $dotenv->load();
  }

  private function setExceptionHandler() {
    if($_ENV["ENVIRONMENT"] == "production") {
      set_exception_handler(function() {
        require("500.php");
        exit(-1);
      });
      set_error_handler(function() {
        require("500.php");
        exit(-1);
      });
    }
  }  

}

?>