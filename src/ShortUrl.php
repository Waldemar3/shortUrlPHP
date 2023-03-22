<?php
class ShortUrl
{
	protected static $shortUrlLength = 6;
    protected static $chars = "123456789bcdfghjkmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ";
    protected static $URLregexp = "/^https?:\\/\\/(?:www\\.)?[-a-zA-Z0-9@:%._\\+~#=]{1,256}\\.[a-zA-Z0-9()]{1,6}\\b(?:[-a-zA-Z0-9()@:%_\\+.~#?&\\/=]*)$/";

    protected static $table = "short_urls";

    protected $timestamp;
    protected $pdo;

    public function __construct(PDO $pdo) {
    	$this->timestamp = $_SERVER["REQUEST_TIME"];
        $this->pdo = $pdo;

        $this->migrate();
    }

    public function urlToShortCode($url) {
        if (!$this->validateUrl($url) || empty($url)) {
            throw new \Exception("Неправильный формат URL");
        }

        if (!$this->urlExistsInNetwork($url)) {
            throw new \Exception("Данный URL не существует");
        }

        $shortCode = $this->urlExistsInDb($url);

        if (!$shortCode) {
            $shortCode = $this->createShortCode($url);
        }

        return $shortCode;
    }

    protected function validateUrl($url) {
        return preg_match(self::$URLregexp, $url);
    }

    protected function urlExistsInNetwork($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $response != 0 && $response <= 400;
    }

    protected function urlExistsInDb($url) {
        $query = "SELECT short_code FROM " . self::$table . " WHERE long_url = :long_url LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $params = [
        	"long_url" => $url,
        ];
        $stmt->execute($params);

        $result = $stmt->fetch();
        return (empty($result)) ? false : $result["short_code"];
    }

    protected function createShortCode($url) {
    	$code = '';

    	$intval = time();
    	$count = strlen(self::$chars);

    	for($i=0;$i<self::$shortUrlLength;$i++){
    		$last = $intval%$count;
    		$intval = ($intval-$last)/$count;
    		$code.=self::$chars[$last];
    	}

    	$query = "INSERT INTO " . self::$table . " (long_url, short_code, date_created) " . " VALUES (:long_url, :short_code, :timestamp)";
        $stmnt = $this->pdo->prepare($query);
        $params = [
            "long_url" => $url,
            "short_code" => $code,
            "timestamp" => $this->timestamp,
        ];
        $stmnt->execute($params);

        return $code;
    }

    public function shortCodeToUrl($code) {
        if (!$this->validateShortCode($code) || empty($code)) {
            throw new \Exception("Неправильный формат кода");
        }

        $url = $this->getUrlFromDb($code);

        if (empty($url)) {
            throw new \Exception("Код отсутствует в базе данных");
        }

        return $url["long_url"];
    }

    protected function validateShortCode($code) {
        return preg_match("|[" . self::$chars . "]+|", $code);
    }

    protected function getUrlFromDb($code) {
        $query = "SELECT long_url FROM " . self::$table . " WHERE short_code = :short_code LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $params = [
            "short_code" => $code,
        ];
        $stmt->execute($params);

        $result = $stmt->fetch();
        return (empty($result)) ? false : $result;
    }

    protected function migrate(){
	    $query = "create table if not exists ". self::$table . " (
				id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
				long_url VARCHAR(255) NOT NULL,
				short_code VARBINARY(6) NOT NULL,
				date_created INTEGER UNSIGNED NOT NULL,

				PRIMARY KEY (id),
	        )
	    ";

	    $this->pdo->exec($query);   	
	}
}