<?php
namespace App\Services;
use \Exception as Exception;


class UnauthorizedException extends Exception {
  public function __construct($message, $code = 0, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}