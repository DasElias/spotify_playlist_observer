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

  protected function isRedirectDesired() {
    $suppressRedirect = isset($_GET["suppressRedirect"]) && filter_var($_GET["suppressRedirect"], FILTER_VALIDATE_BOOLEAN);
    return !$suppressRedirect;
  }

  protected function redirectIfDesired($url, $responseCode = 200) {
    if($this->isRedirectDesired()) {
      $this->redirect($url);
    } else {
      http_response_code($responseCode);
      return;
    }

    
  }

  protected function retrieveGetVar($getName) {
    $this->ensureVarIsset($_GET[$getName]);

    return $_GET[$getName];
  }

  protected function ensureVarIsset($var) {
    if(! isset($var)) {
      $this->redirectIfDesired("listPlaylists.php", 400);
      die;
    }
  }

  protected function storeInSession($getName, $successRedirect, $failureStatusCode) {
    $isRedirectDesired = $this->isRedirectDesired();

    if(isset($_GET[$getName])) {
      $_SESSION[$getName] = $_GET[$getName];
      
      if($isRedirectDesired) {
        $this->redirect($successRedirect);
        die;
      }  
    } else if (! $isRedirectDesired) {
      http_response_code($failureStatusCode);
      die;
    } 
  }

  protected function getFromSession($getName, $callback) {
    if(isset($_SESSION[$getName])) {
      $var = $_SESSION[$getName];
      $callback($var);
    }
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
    } else {
      error_reporting(-1);
    }
  }  

}

?>