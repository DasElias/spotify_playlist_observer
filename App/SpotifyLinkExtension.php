<?php
namespace App;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SpotifyLinkExtension extends AbstractExtension {
    public function getFunctions() {
      return array(
          new TwigFunction('toSpotifyPlaylistLink', [$this, 'toSpotifyPlaylistLink']),
        );
    }

    public function toSpotifyPlaylistLink($playlistId) {
      return "https://open.spotify.com/playlist/" . $playlistId;
    }

}