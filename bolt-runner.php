<?php

require __DIR__.'/vendor/autoload.php';

use Bolt\App;

$app = new App();
$app->runCommand($argv);