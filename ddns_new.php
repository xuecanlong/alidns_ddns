<?php
require './vendor/autoload.php';
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

// Download：https://github.com/aliyun/openapi-sdk-php
// Usage：https://github.com/aliyun/openapi-sdk-php/blob/master/README.md

$accessKeyId = '<accessKeyId>';
$accessSecret = '<accessSecret>';
AlibabaCloud::accessKeyClient($accessKeyId, $accessSecret)
                        ->regionId('cn-hangzhou') // replace regionId as you need
                        ->asDefaultClient();
//用户发来的参数
$Domain = ReqStr('Domain'); //域名
$RR = ReqStr('RR'); //RR
$Ip = get_client_ip();


$Records = DescribeDomainRecords($Domain, $RR);
UpdateDomainRecord($Records, $Domain, $RR, $Ip);



function UpdateDomainRecord($Records, $Domain, $RR, $Ip)
{
    $Record = $Records->DomainRecords->Record;
    $flag = false;
    $oldIp = "";
    for ($i=0; $i < sizeof($Record); $i++) { 
        $variable = $Record[$i];
        if ($variable->RR == $RR) {
            $flag = true;
            $oldIp = $variable->Value;
        }
    }

    if ($flag) {
        if ($oldIp != $Ip) {
            $result = AlibabaCloud::rpc()
                ->product('Alidns')
                // ->scheme('https') // https | http
                ->version('2015-01-09')
                ->action('UpdateDomainRecord')
                ->method('POST')
                ->host('alidns.aliyuncs.com')
                ->options([
                            'query' => [
                                'RegionId' => "default",
                                'RecordId' => "a",
                                'RR' => $Record[0]->RecordId,
                                'Type' => "A",
                                'Value' => $Ip,
                            ],
                        ])
                ->request();
            echo "update success";
        } else {
            echo "not need update";
        }
    }else{
        $result = AlibabaCloud::rpc()
            ->product('Alidns')
            // ->scheme('https') // https | http
            ->version('2015-01-09')
            ->action('AddDomainRecord')
            ->method('POST')
            ->host('alidns.aliyuncs.com')
            ->options([
                        'query' => [
                            'RegionId' => "default",
                            'DomainName' => $Domain,
                            'RR' => $RR,
                            'Type' => "A",
                            'Value' => $Ip,
                        ],
                    ])
            ->request();
        echo "add success";
    }
    
}

function DescribeDomainRecords($DomainName, $RR)
{
    $response = AlibabaCloud::rpc()
        ->product('Alidns')
        // ->scheme('https') // https | http
        ->version('2015-01-09')
        ->action('DescribeDomainRecords')
        ->method('POST')
        ->host('alidns.aliyuncs.com')
        ->options([
                    'query' => [
                        'RegionId' => "default",
                        'DomainName' => $DomainName,
                        'RRKeyWord' => $RR,
                    ],
                ])
        ->request();
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