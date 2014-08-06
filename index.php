<?php
define('KF_APP_PATH', __DIR__ . '/demo/');

require ('kerriframe/kerriframe.php');

$app = KF::singleton('application');
$app->run();
