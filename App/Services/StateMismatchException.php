<?php
namespace App\Services;
use \Exception as Exception;


class StateMismatchException extends Exception {
  public function __construct($code = 0, Exception $previous = null) {
    parent::__construct("State mismatch", $code, $previous);
  }
}