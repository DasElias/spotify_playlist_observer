<?php
namespace App\Services;
use \Exception as Exception;


class RefreshTokenNotSetException extends Exception {
  public function __construct($code = 0, Exception $previous = null) {
    parent::__construct("Not set.", $code, $previous);
  }
}