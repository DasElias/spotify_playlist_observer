<?php
  
namespace App;

use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

class TwigFactory {
    public function create(string $templatePath, ?string $cachePath = null): Environment {
        $debug = $_ENV['ENVIRONMENT'] != "production";
        $twigLoader = new FilesystemLoader([$templatePath]);

        $options = [
            'debug' => $debug,
            'cache' => $cachePath ?? false,
        ];

        $twig = new Environment($twigLoader, $options);
        $twig->addExtension(new TwigEnvExtension());

        if ($debug) {
            $twig->addExtension(new DebugExtension());
        }

        return $twig;
    }
}


?>