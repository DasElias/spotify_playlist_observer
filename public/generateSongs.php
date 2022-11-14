<?php
require __DIR__ . '/../vendor/autoload.php';

$c = new App\Controllers\GenerateSongsController();
$c->show();