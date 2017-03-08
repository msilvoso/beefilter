<?php
    require __DIR__.'/../auth.php';
    require __DIR__.'/../whitelist.php';

    $whiteList = new whitelist();
    $displayMessage = false;
    exec('sudo /usr/bin/at -l',$existingJobs);
    if (!empty($existingJobs[0])) {
        $job = preg_replace('/\D+.*/', '', $existingJobs[0]);
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['whitelist'])) {
        $whiteListContent = $whiteList->save($_POST['whitelist']);
        $displayMessage = true;
    } else {
        $whiteListContent = $whiteList->load();
        //Check if there are jobs scheduled
        if (!empty($_GET['host'])) {
            $host = filter_var($_GET['host'], FILTER_SANITIZE_URL);
            $whiteListContent = $host . "\n" . $whiteListContent;
        }
        if (!empty($_GET['bypass'])) {
             exec('sudo /usr/local/bin/dnsmasqconfig bypass');
             exec("echo '/usr/local/bin/dnsmasqconfig' | sudo /usr/bin/at 'now + 1 hours'");
        }
        if (!empty($_GET['block'])) {
             exec('sudo /usr/bin/at -r '.escapeshellarg($job)); 
             exec('sudo /usr/local/bin/dnsmasqconfig');
        }
    }

    require __DIR__.'/../template.phtml';
