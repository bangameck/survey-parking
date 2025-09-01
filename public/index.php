<?php
require_once '../config/config.php';
require_once '../vendor/autoload.php';
require_once '../app/core/App.php';
require_once '../app/core/Controller.php';

define('APP_START_TIME', microtime(true));
$app = new App();
