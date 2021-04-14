<?php
namespace App;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigEnvExtension extends AbstractExtension {
    public function getFunctions() {
      return array(
          new TwigFunction('getenv', [$this, 'getEnvironmentVar']),
        );
    }

    public function getEnvironmentVar($var) {
      return $_ENV[$var];
    }

}