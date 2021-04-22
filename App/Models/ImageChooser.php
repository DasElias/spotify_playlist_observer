<?php
namespace App\Models;

class ImageChooser {
  public function getCoverUrl($images, $targetSize) {
    $minDelta = INF;
    $minDeltaUrl = null;

    foreach($images as $i) {
      $delta = abs($i["width"] - $targetSize);
      if($delta < $minDelta) {
        $minDelta = $delta;
        $minDeltaUrl = $i["url"];
      }
    }

    return $minDeltaUrl;
  }

}
