<!DOCTYPE html>
<html>
<head>
	<title>Interdit!</title>
</head>
<body>
	<h1>Site bloqué!</h1>
	<a href="http://<?php echo $_SERVER['SERVER_ADDR'];?>:8080?host=<?php echo urlencode($_SERVER['HTTP_HOST'])?>" target="_blank">Ajouter à la liste blanche</a>
</body>
