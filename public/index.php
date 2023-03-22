<?php 

require_once './db-connection.php';
require_once '../src/ShortUrl.php';

$code = trim($_SERVER['REQUEST_URI'], '/');

$shortUrl = new ShortUrl($pdo);

try {
	if(!empty($code)){
		header('Location: ' . $shortUrl->shortCodeToUrl($code));
	}
}catch (\Exception $e) {
    echo $e->getMessage();
}

?>

 <!DOCTYPE html>
 <html lang="en">
 <head>
 	<meta charset="UTF-8">
 	<meta name="viewport" content="width=device-width, initial-scale=1.0">
 	<link rel="stylesheet" href="css/style.css">
 	<title>Short URL</title>
 </head>
 <body>
 	<div class="wrapper">
 		<header>
 			<div class="section">
 				<input type="text" placeholder="Вставьте ссылку для сокращения" />
				<button>Сократить</button>
 			</div>

			<div class="section links">
				<span id="originalUrl"></span>
				<span id="shortUrl"></span>
			</div>
 		</header>
 	</div>
 </body>
 <script src="https://code.jquery.com/jquery-3.6.4.min.js" integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8=" crossorigin="anonymous"></script>
 <script src="js/index.js" type="text/javascript"></script>
 </html>