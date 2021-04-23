<?php
namespace App\Models;

class ImageChooser {
  public function getCoverUrl($images, $targetSize) {
    $minDelta = INF;
    $minDeltaUrl = null;

    foreach($images as $i) {
      $delta = abs($i["width"] - $targetSize);
      if($delta < $minDelta && $i["width"] > $targetSize) {
        $minDelta = $delta;
        $minDeltaUrl = $i["url"];
      }
    }

    // only return smaller images when there are no bigger ones
    if($minDeltaUrl == null) {
      foreach($images as $i) {
        $delta = abs($i["width"] - $targetSize);
        if($delta < $minDelta) {
          $minDelta = $delta;
          $minDeltaUrl = $i["url"];
        }
      }
    }

    return $minDeltaUrl;
  }

}
