<?php
require __DIR__.'/digest.php';

session_start();

$realm = 'Restricted';
$users = include __DIR__.'/users.php';
$message = 'Autorisation requise';

$digest = new digest($realm, $users, $message);

if (isset($_GET['logout'])) {
    $digest->logout();
}

$digest->init();
$digest->checkAuth();
$digest->response();
