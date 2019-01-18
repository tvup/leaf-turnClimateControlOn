<?php

class LeafTurnOnCC
{
    private $userId = '';
    private $password = '';
    private $initialAppStrings = 'geORNtsZe5I4lRGjG9GZiA';
    private $lg = 'da-DK';
    private $regionCode = 'NE';
    private $dcmid = '';
    private $VIN;
    private $baseRPM = 'uyI5Dj9g8VCOFDnBRUbr3g';
    private $customSessionID = '';
    private $tz = 'Europe/Copenhagen';

    function __construct($userId, $password)
    {
        $this->userId = $userId;
        $this->password = $password;
    }

    public function fire()
    {
        $i = 0;
	do {
	$postBody = 'UserId=' . urlencode($this->userId) . '&cartype=&custom_sessionid=&initial_app_strings=' . $this->initialAppStrings . '&tz=&lg=' . $this->lg . '&DCMID=&VIN=&RegionCode=' . $this->regionCode . '&Password=' . urlencode($this->encryptPassword($this->password,$this->baseRPM));
	var_dump($postBody);

        $headerArray = array('Content-Type: application/x-www-form-urlencoded',
            'Charset: UTF-8',
            'User-Agent: Dalvik/2.1.0 (Linux; U; Android 5.1; Custom Phone - 5.1.0 - API 22 - 768x1280 Build/LMY47D)',
            'Connection: Keep-Alive',
            'Accept-Encoding: gzip');

        $curlOptions = array(
            CURLOPT_HTTPHEADER => $headerArray,
            CURLOPT_POSTFIELDS => $postBody,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_FOLLOWLOCATION => TRUE,
        );

        $url = "https://gdcportalgw.its-mo.com/gworchest_160803EC/gdc/UserLoginRequest.php";
        $handle = curl_init($url);
        curl_setopt_array($handle, $curlOptions);
        $content = curl_exec($handle);

        $response = json_decode($content);
	var_export($response);
	} while ($i<9 && $response->status != 200);
	if($response->status != 200)
	{
		die();
	}
        $custom_sessionid = $response->VehicleInfoList->vehicleInfo[0]->custom_sessionid;
        
        $this->dcmid = $response->vehicle->profile->dcmId;
        $this->VIN = $response->vehicle->profile->vin;
        
	$i = 0;
	do {
          //New request
          $postBody = 'UserId=' . urlencode($this->userId) . '&cartype=&custom_sessionid=' . urlencode($custom_sessionid) . '&tz=Europe%2FCopenhagen&lg=da-DK&DCMID=' . $this->dcmid . '&VIN=' . $this->VIN . '&RegionCode=' . $this->regionCode;
	  var_dump($postBody);

          $curlOptions = array(
              CURLOPT_HTTPHEADER => $headerArray,
              CURLOPT_POSTFIELDS => $postBody,
              CURLOPT_RETURNTRANSFER => TRUE,
              CURLOPT_FOLLOWLOCATION => TRUE,
          );

          $url = "https://gdcportalgw.its-mo.com/gworchest_160803EC/gdc/ACRemoteRequest.php";
          $handle = curl_init($url);
          curl_setopt_array($handle, $curlOptions);
          $content = curl_exec($handle);

          $response = json_decode($content);

	  //Dette er muligvis resultatet af 'morgentravlhed':
	  //array(
          //  'status' => '-2000',
          //  'ErrorCode' => '-2000',
          //  'ErrorMessage' => 'GDC Internal Error')

          var_export($response);
	} while ($i<9 && $response->status != 200);
        curl_close($handle);
    }

    public function encryptPassword($password, $key)
    {
        if (!extension_loaded('mcrypt')) {
            throw new Exception("mcrypt PHP extension is not loaded.");
        }
        $size = @call_user_func('mcrypt_get_block_size', MCRYPT_BLOWFISH);

        if (empty($size)) {
            $size = @call_user_func('mcrypt_get_block_size', MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        }
        $password = static::pkcs5_pad($password, $size);

        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB), MCRYPT_RAND);

        $encrypted_password = mcrypt_encrypt(MCRYPT_BLOWFISH, $key, $password, MCRYPT_MODE_ECB, $iv);
        return base64_encode($encrypted_password);
    }

    private static function pkcs5_pad ($text, $blocksize) { 
		$pad = $blocksize - (strlen($text) % $blocksize); 
		return $text . str_repeat(chr($pad), $pad); 
    } 

    private function encryptPassword2($password, $key) {
        if ($this->config->encryptionOption == static::ENCRYPTION_OPTION_WEBSERVICE) {
            return trim(file_get_contents("https://dataproxy.pommepause.com/nissan-connect-encrypt.php?key=" . urlencode($key) . "&password=" . urlencode($password)));
        }
        if (!function_exists('openssl_encrypt')) {
            throw new Exception("OpenSSL support in PHP is not available. Either use ENCRYPTION_OPTION_WEBSERVICE as the encryption option, to use a remote web-service to encrypt passwords, or compile PHP using --with-openssl.");
        }
        $method = 'bf-ecb';
        $encrypted_password = openssl_encrypt($password, $method, $key, TRUE);
        var_dump(base64_encode($encrypted_password));
        return base64_encode($encrypted_password);
    }

}

$obj = new LeafTurnOnCC($argv[1], $argv[2]);
$obj->fire();


