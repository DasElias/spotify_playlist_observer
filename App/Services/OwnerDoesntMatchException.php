<?php
namespace App\Services;
use \Exception as Exception;


class OwnerDoesntMatchException extends Exception {
    private $isDestCollaborative;

  public function __construct($message, $isDestCollaborative, $code = 0, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }

    public function isDestCollaborative() {
        return $this->isDestCollaborative;
    }
}