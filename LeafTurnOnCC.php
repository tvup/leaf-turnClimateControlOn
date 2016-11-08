<?php

class LeafTurnOnCC
{
    private $userId = '';
    private $password = '';
    private $initialAppStrings = '';
    private $lg = 'en-US';
    private $regionCode = 'NE';
    private $dcmid = '';
    private $VIN;

    function __construct($userId, $password, $initialAppStrings)
    {
        $this->userId = $userId;
        $this->password = $password;
        $this->initialAppStrings = $initialAppStrings;
    }

    public function fire()
    {
        $postBody = 'UserId=' . urlencode($this->userId) . '&cartype=&custom_sessionid=&initial_app_strings=' . $this->initialAppStrings . '&tz=&lg=' . $this->lg . '&DCMID=&VIN=&RegionCode=' . $this->regionCode . '&Password=' . urlencode($this->password);

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

        $url = "https://gdcportalgw.its-mo.com/gworchest_0405EC/gdc/UserLoginRequest.php";
        $handle = curl_init($url);
        curl_setopt_array($handle, $curlOptions);
        $content = curl_exec($handle);

        $response = json_decode($content);
        $custom_sessionid = $response->VehicleInfoList->vehicleInfo[0]->custom_sessionid;
        
        $this->dcmid = $response->vehicle->profile->dcmId;
        $this->VIN = $response->vehicle->profile->vin;
        

        //New request
        $postBody = 'UserId=' . urlencode($this->userId) . '&cartype=&custom_sessionid=' . urlencode($custom_sessionid) . '&tz=Europe%2FCopenhagen&lg=da-DK&DCMID=' . $this->dcmid . '&VIN=' . $this->VIN . '&RegionCode=' . $this->regionCode;

        $curlOptions = array(
            CURLOPT_HTTPHEADER => $headerArray,
            CURLOPT_POSTFIELDS => $postBody,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_FOLLOWLOCATION => TRUE,
        );

        $url = "https://gdcportalgw.its-mo.com/gworchest_0405EC/gdc/ACRemoteRequest.php";
        $handle = curl_init($url);
        curl_setopt_array($handle, $curlOptions);
        $content = curl_exec($handle);

        $response = json_decode($content);

        var_export($response);

        curl_close($handle);
    }

}

$obj = new LeafTurnOnCC($argv[1], $argv[2], $argv[3]);
$obj->fire();


