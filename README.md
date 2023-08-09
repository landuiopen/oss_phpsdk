# oss_phpsdk

<?php
require './vendor/autoload.php';/*此处为直接调用不依托框架时使用*/
use landui\oss\Auth;
use landui\oss\Config;
use landui\oss\Storage\UploadManager;
use landui\oss\Storage\BucketManager;

//从蓝队云云账号上面分别复制这几个参数进来(测试)

$accessKey = 'ACCESS_KEY';//请在蓝队云管理面板中获取
$secretKey = 'SECRET_KEY';//请在蓝队云管理面板中获取
$bucket = 'BUCKET';//存储空间 蓝队云中创建好存储空间后将名称替换

//实例化蓝队云云auth类
$auth = new Auth($accessKey, $secretKey);
//生成token
$token = $auth->uploadToken($bucket);
$config = new Config();
//上传服务器地址获取
$upHost = $config->getUpHost($accessKey, $bucket);
/*文件上传*/
$uploadMgr = new UploadManager();
$rets = [];//返回参数
$err = [];//返回错误
// 要上传文件的本地路径
$filePath = './test.pdf';
// 上传到存储后保存的文件名
$key = "test".time().".pdf";
// 调用 UploadManager 的 putFile 方法进行文件的上传。
list($ret[], $err[]) = $uploadMgr->putFile(
    $token, /*token*/
    $key, /*要上传文件的本地路径*/
    $filePath, /*上传到存储后保存的文件名*/
    null,
    'application/octet-stream',
    true,
    null,
    'v2'
);

/**
 * 资源管理
 * 获取文件信息
 * 修改文件MimeType
 * 修改文件存储类型
 * 移动或重命名文件
 * 复制文件副本
 * 删除空间中的文件
 * 设置或更新文件的生存时间
 * 获取指定空间的文件列表
 * 抓取网络资源到空间
 * 更新镜像空间中存储的文件内容
 * 资源管理批量操作
 *   批量获取文件信息
 *   批量修改文件类型
 *   批量删除文件
 *   批量复制文件
 *   批量移动或重命名文件
 *   批量更新文件的有效期
 *   批量更新文件存储类型
 * 等控制方法都在BucketManager中实现  下面有简单列举
 *
 */
$bucketManager = new BucketManager($auth, $config);
$days = 10;
list($rets[], $errs[]) = $bucketManager->deleteAfterDays($bucket, $key, $days);/*设置或更新文件的生存时间*/
list($rets[], $errs[]) = $bucketManager->delete($bucket, $key);/*删除文件*/



/*断点打印结果*/
landui\oss\dd($ret,$token,$err,$errs);
