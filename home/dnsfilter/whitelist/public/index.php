<?php
    require __DIR__.'/../auth.php';
    require __DIR__.'/../whitelist.php';

    $whiteList = new whitelist();
    $displayMessage = false;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['whitelist'])) {
        $whiteListContent = $whiteList->save($_POST['whitelist']);
        $displayMessage = true;
    } else {
        $whiteListContent = $whiteList->load();
        if (!empty($_GET['host'])) {
            $host = filter_var($_GET['host'], FILTER_SANITIZE_URL);
            $whiteListContent = $host . "\n" . $whiteListContent;
        }
    }

    require __DIR__.'/../template.phtml';
