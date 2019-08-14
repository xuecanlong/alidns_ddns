<?php
require './vendor/autoload.php';
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

// Download：https://github.com/aliyun/openapi-sdk-php
// Usage：https://github.com/aliyun/openapi-sdk-php/blob/master/README.md

$accessKeyId = '<accessKeyId>';
$accessSecret = '<accessSecret>';
//用户发来的参数
$mobile = ReqStr('mobile'); //手机号
$code = ReqStr('code'); //验证码
$type = ReqStr('type'); //类型
$Ip = get_client_ip();
if ($Ip == "") { //ip白名单

    AlibabaCloud::accessKeyClient($accessKeyId, $accessSecret)
                        ->regionId('cn-hangzhou') // replace regionId as you need
                        ->asDefaultClient();

    $PhoneNumbers = $mobile;
    $SignName = "一起玩玩";
    $TemplateCode = "SMS_172356863"; //注册
    if ($type == "pwd") {
        $TemplateCode = "SMS_172351676"; // 忘记密码
    }
    SendSms($PhoneNumbers, $SignName, $TemplateCode, "{\"code\":\"". $code . "\"}");
} else {
    print_r("error");
}


function SendSms($PhoneNumbers, $SignName, $TemplateCode, $TemplateParam)
{
    $result = AlibabaCloud::rpc()
                          ->product('Dysmsapi')
                          // ->scheme('https') // https | http
                          ->version('2017-05-25')
                          ->action('SendSms')
                          ->method('POST')
                          ->host('dysmsapi.aliyuncs.com')
                          ->options([
                                        'query' => [
                                          'RegionId' => "cn-hangzhou",
                                          'PhoneNumbers' => $PhoneNumbers,
                                          'SignName' => $SignName,
                                          'TemplateCode' => $TemplateCode,
                                          'TemplateParam' => $TemplateParam,
                                        ],
                                    ])
                          ->request();
    print_r("[success] => true");
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