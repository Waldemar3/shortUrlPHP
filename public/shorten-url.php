<?php 

require_once './db-connection.php';
require_once '../src/ShortUrl.php';

$shortUrl = new ShortUrl($pdo);

try {
	$code = $shortUrl->urlToShortCode(htmlspecialchars($_POST['url']));
	header("Content-Type: text/plain");
	echo $code;
}catch (\Exception $e) {
    header('HTTP/1.0 400 Bad url');
    echo $e->getMessage();
}