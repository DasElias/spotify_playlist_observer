<?php
namespace App\Services;

class DeepCloneService {
    public function clone($obj) {
        return json_decode(json_encode($obj), true);
    }

}