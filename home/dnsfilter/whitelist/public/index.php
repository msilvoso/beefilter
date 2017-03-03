<?php
    require "../auth.php";
?>
<!DOCTYPE html>
<html>
<head>
	<title>Edition de la liste blanche</title>
</head>
<body>
	<h1>Liste blanche des sites autorisés</h1>
<?php 
	chdir(__DIR__);
	$displayMessage = false;
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['whitelist'])) {
		// sanitize
		$whitelistArr = explode("\n", $_POST['whitelist']);
		foreach($whitelistArr as $key => $line) {
			$line = trim($line);
			if (strlen($line) === 0 || substr($line,0,1) === '#') continue; //comment or empty

			$line = preg_replace('#^https?://#', '', $line);
			$line = preg_replace('#^www\.#', '', $line);
			$line = preg_replace('#^[/ ]*#', '', $line);
			$line = preg_replace('#/.*$#', '', $line);
			$line = preg_replace('/[^\w#.-]/', '', $line);
			$whitelistArr[$key] = $line;
		}
		$whitelist = implode("\n", $whitelistArr);
		file_put_contents('../whitelist', $whitelist);
		exec('sudo /usr/local/bin/dnsmasqconfig');
		$displayMessage = true;
	} else {
		$whitelist = file_get_contents('../whitelist');
		if (!empty($_GET['host'])) {
			$host = filter_var($_GET['host'], FILTER_SANITIZE_URL);
			$whitelist = $host . "\n" . $whitelist;
		} 
	}
	$default = file_get_contents('../default.whitelist');
	if ($displayMessage) {
		echo '<p style="color:green;">Liste enregistrée avec succès</p>';
	} else {
		echo '<p></p>';
	}
?>
	<form action="/index.php" method="post">
		<input type="submit" name="submit" value="appliquer"/>
		<a href="/?logout=1">Déconnexion</a><br/>
		<textarea name="whitelist" rows="40" cols="80"><?php echo $whitelist;?></textarea>
	</form>
	<h2>Liste de sites permis par défaut</h2>
	<pre>
		<?php echo $default; ?>
	</pre>
</body>
