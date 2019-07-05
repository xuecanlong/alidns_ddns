<?php
include_once 'aliyun-php-sdk-core/Config.php';
//用户发来的参数
$Domain = ReqStr('Domain'); //域名
$RR = ReqStr('RR'); //RR
$Ip = get_client_ip();

$accessKeyId = "<accessKeyId>";
$accessSecret = "<accessSecret>";

$iClientProfile = DefaultProfile::getProfile("cn-hangzhou", $accessKeyId, $accessSecret);
$client = new DefaultAcsClient($iClientProfile);

$Records = DescribeDomainRecords($Domain, $RR, $client);
UpdateDomainRecord($Records, $Domain, $RR, $Ip, $client);



function UpdateDomainRecord($Records, $Domain, $RR, $Ip, $client)
{
    $Record = $Records->DomainRecords->Record;
    if ($Record[0]) {
        if ($Record[0]->Value != $Ip) {
            class UpdateDomainRecordRequest extends \RpcAcsRequest{
                function  __construct() {
                    parent::__construct("Alidns", "2015-01-09", "UpdateDomainRecord");
                    $this->setMethod("POST");
                }
                function setQueryParameters($Key, $Value)
                {
                    $this->queryParameters[$Key] = $Value;
                }
            }

            $request = new UpdateDomainRecordRequest();
            $request->setQueryParameters("RecordId", $Record[0]->RecordId);
            $request->setQueryParameters("RR", $RR);
            $request->setQueryParameters("Type", "A");
            $request->setQueryParameters("Value", $Ip);

            $response = $client->getAcsResponse($request);
        }
    }else{
        class AddDomainRecordRequest extends \RpcAcsRequest{
            function  __construct() {
                parent::__construct("Alidns", "2015-01-09", "AddDomainRecord");
                $this->setMethod("POST");
            }
            function setQueryParameters($Key, $Value)
            {
                $this->queryParameters[$Key] = $Value;
            }
        }

        $request = new AddDomainRecordRequest();
        $request->setQueryParameters("DomainName", $Domain);
        $request->setQueryParameters("RR", $RR);
        $request->setQueryParameters("Type", "A");
        $request->setQueryParameters("Value", $Ip);

        $response = $client->doAction($request);
    }
    print_r("success");
}

function DescribeDomainRecords($DomainName, $RR, $client)
{
    class DescribeDomainRecordsRequest extends \RpcAcsRequest{
        function  __construct() {
            parent::__construct("Alidns", "2015-01-09", "DescribeDomainRecords");
            $this->setMethod("POST");
        }
        function setQueryParameters($Key, $Value)
        {
            $this->queryParameters[$Key] = $Value;
        }
    }

    $request = new DescribeDomainRecordsRequest();
    $request->setQueryParameters("DomainName", $DomainName);
    $request->setQueryParameters("RRKeyWord", $RR);

    $response = $client->getAcsResponse($request);
    return $response;
}

function get_client_ip(){  
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))  
        $ip = getenv("HTTP_CLIENT_IP");  
    else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))  
        $ip = getenv("HTTP_X_FORWARDED_FOR");  
    else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))  
        $ip = getenv("REMOTE_ADDR");  
    else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))  
        $ip = $_SERVER['REMOTE_ADDR'];  
    else  
        $ip = "unknown";


    // if (@$_SERVER["HTTP_X_FORWARDED_FOR"]) 
    //     $ip = $_SERVER["HTTP_X_FORWARDED_FOR"]; 
    // else if (@$_SERVER["HTTP_CLIENT_IP"]) 
    //     $ip = $_SERVER["HTTP_CLIENT_IP"]; 
    // else if (@$_SERVER["REMOTE_ADDR"]) 
    //     $ip = $_SERVER["REMOTE_ADDR"]; 
    // else if (@getenv("HTTP_X_FORWARDED_FOR")) 
    //     $ip = getenv("HTTP_X_FORWARDED_FOR"); 
    // else if (@getenv("HTTP_CLIENT_IP")) 
    //     $ip = getenv("HTTP_CLIENT_IP"); 
    // else if (@getenv("REMOTE_ADDR")) 
    //     $ip = getenv("REMOTE_ADDR"); 
    // else  
    //     $ip = "unknown";
    return $ip;  
}

function ReqStr($StrName)
{
    if (is_array($StrName)) {
        foreach ($StrName as $key => $val) {
            $StrName[$key] = ReqStr($val);
        }
    } else {
        $StrName = @get_magic_quotes_gpc() ? $_REQUEST[$StrName] : addslashes($_REQUEST[$StrName]);
    }
    return $StrName;
}

?>