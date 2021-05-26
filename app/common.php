<?php
// 应用公共文件

/**
 *    加密算法
 * 　　$method 加密方法
 *　　　1、DES-ECB
 *　　　2、DES-CBC
 *　　　3、DES-CTR
 *　　　4、DES-OFB
 *　　　5、DES-CFB
 *
 *   $options 数据格式选项（可选）【选项有：】
 *　　　　1、0
 *　　　　2、OPENSSL_RAW_DATA=1
 *　　　　3、OPENSSL_ZERO_PADDING=2
 *　　　　4、OPENSSL_NO_PADDING=3
 */

use think\response\Json;
use \think\Response;

/**
 *  密码加密库
 * @param $data
 * @return false|string
 */
function passEncryption($data)
{
    $method = 'DES-CBC';//加密方法
    $options = 0;//数据格式选项（可选）
    $key = env('JWT_SECRET');
    $iv = '19860611';//加密初始化向量（可选）
    return openssl_encrypt($data, $method, $key, $options, $iv);
}

/**
 *   加入md5混淆的密码
 * @param $passWord
 * @return string
 */
function confusionPassword($passWord): string
{
    return md5(passEncryption($passWord), false);
}

/**
 *   密码解密函数
 * @param $data
 * @return false|string
 */
function passDecode($data)
{
    $method = 'DES-CBC';//加密方法
    $options = 0;//数据格式选项（可选）
    $key = env('JWT_SECRET');
    $iv = '19860611';//加密初始化向量（可选）
    return openssl_decrypt($data, $method, $key, $options, $iv);
}

/**
 *  使用外来key解密
 * @param $data
 * @param $key
 * @return false|string
 */
function keyPassDecode($data, $key)
{
    {
        $method = 'DES-CBC';//加密方法
        $options = 0;//数据格式选项（可选）
        $iv = '19860611';//加密初始化向量（可选）
        return openssl_decrypt($data, $method, $key, $options, $iv);
    }
}

/**
 *  使用外来key加密
 * @param $data
 * @param $key
 * @return false|string
 */
function keyPassEncry($data, $key)
{
    $method = 'DES-CBC';//加密方法
    $options = 0;//数据格式选项（可选）
    $iv = '19860611';//加密初始化向量（可选）
    return openssl_encrypt($data, $method, $key, $options, $iv);
}

/**
 * 随机字符
 * @return string
 * @throws Exception
 */
function RoundGenerate(): string
{
    // 验证码字符集合
    $codeSet = '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY';
    $bag = '';
    $length = 5;

    $characters = str_split($codeSet);
    for ($i = 0; $i < $length; $i++) {
        $bag .= $characters[rand(0, count($characters) - 1)];
    }
    return $bag;
}

/**
 *  生成随机key值
 * @return string
 */
function RoundLongGenerateKey(): string
{
    // 验证码字符集合
    $codeSet = 'ABCDEFGHJKLMNPQRTUVWXY';
    $bag = '';
    $length = 8;

    $characters = str_split($codeSet);
    for ($i = 0; $i < $length; $i++) {
        $bag .= $characters[rand(0, count($characters) - 1)];
    }
    return $bag;
}

/**
 *  生成长度为8的加密随机字符串
 * @return string
 */
function RoundLongGenerate(): string
{
    // 验证码字符集合
    $codeSet = '1234567890abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY';
    $bag = '';
    $length = 8;

    $characters = str_split($codeSet);
    for ($i = 0; $i < $length; $i++) {
        $bag .= $characters[rand(0, count($characters) - 1)];
    }
    return $bag;
}

/**
 *  失败时返回
 * @param $errMsg
 * @return
 */
function retFailure($errMsg)
{
    $data = [
        "code" => "3",
        "msg" => $errMsg,
        "result" => ""
    ];
    return Response::create($data, 'json', 200);
}

/**
 *  计算时间
 * @param null $time
 * @return false|string
 */
function humanDate($time = NULL)
{
    if (is_string($time)) {
        $time = strtotime($time);
    }

    $text = '';
    $time = $time === NULL || $time > time() ? time() : intval($time);
    $t = time() - $time; //时间差 （秒）
    $y = date('Y', $time) - date('Y', time());//是否跨年
    switch ($t) {
        case $t == 0:
            $text = '刚刚';
            break;
        case $t < 60:
            $text = $t . '秒前'; // 一分钟内
            break;
        case $t < 60 * 60:
            $text = floor($t / 60) . '分钟前'; //一小时内
            break;
        case $t < 60 * 60 * 24:
            $text = floor($t / (60 * 60)) . '小时前'; // 一天内
            break;
        case $t < 60 * 60 * 24 * 3:
            $text = floor($time / (60 * 60 * 24)) == 1 ? '昨天 ' . date('H:i', $time) : '前天 ' . date('H:i', $time); //昨天和前天
            break;
        case $t < 60 * 60 * 24 * 30:
            $text = date('m月d日 H:i', $time); //一个月内
            break;
        case $t < 60 * 60 * 24 * 365 && $y == 0:
            $text = date('m月d日', $time); //一年内
            break;
        default:
            $text = date('Y年m月d日', $time); //一年以前
            break;
    }

    return $text;
}
