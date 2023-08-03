<?php
require './vendor/autoload.php';
use landuioss\Auth;
use landuioss\Storage\UploadManager;
use landuioss\Config;

//从七牛云账号上面分别复制这几个参数进来(线上)
//$accessKey = '67Z9KGMES-LDaO2d8B9dLuitulz-Rmq2ZXzzocvf';
//$secretKey = 'Fq4yDKG-y0x2tBQtsGVnqfU-qQ8ZrA73K5qu_kbu';


//从七牛云账号上面分别复制这几个参数进来(测试)
$accessKey = 'wapD0OFkMpJXBtKaG0UmhNwUBggrodBNeQKrF8Fj';
$secretKey = 'qlDBbh4fC592AYee5kdsYKZUv41lpw9VE3CoHxvl';
//$bucket = 'taotest';
$bucket = 'xiaotest1';
//实例化七牛云auth类
$auth = new Auth($accessKey, $secretKey);

//生成token
$token = $auth->uploadToken($bucket);
$config = new Config();
$upHost = $config->getUpHost($accessKey, $bucket);
$uploadMgr = new UploadManager();
// 要上传文件的本地路径
$filePath = './01-4A15T TE42 发动机管理系统.pdf';
// 上传到存储后保存的文件名
$key = "01-4A15T TE42 发动机管理系统".time().".pdf";
// 调用 UploadManager 的 putFile 方法进行文件的上传。
list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath, null, 'application/octet-stream', true, null, 'v2');
//echo "\n====> putFile result: \n";
//if ($err !== null) {
//    var_dump($err);
//} else {
//    var_dump($ret);
//}
//var_dump($upHost);
//var_dump($err);
echo($token);
