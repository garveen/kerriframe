<?php
define('KF_APP_PATH', __DIR__ . '/app/');

require ('kerriframe/kerriframe.php');

$app = KF::singleton('application');
$app->run();
