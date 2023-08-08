<?php

namespace landuioss\Storage;

use landuioss\Config;
use landuioss\Http\Error;
use landuioss\Http\Client;
use landuioss\Http\RequestOptions;
/**
 *表单上传器用于接受存储从前端直接上传的资源
 *
 * */
final class FormUploader
{

    /**
     * 上传二进制流到蓝队云, 内部使用
     *
     * @param string $upToken 上传凭证
     * @param string $key 上传文件名
     * @param string $data 上传二进制流
     * @param Config $config 上传配置
     * @param string $params 自定义变量，规格参考
     *                    {@link https://developer.landuiyu.com/kodo/manual/1235/vars#xvar}
     * @param string $mime 上传数据的mimeType
     * @param string $fname
     * @param RequestOptions $reqOpt
     *
     * @return array    包含已上传文件的信息，类似：
     *                                              [
     *                                                  "hash" => "<Hash string>",
     *                                                  "key" => "<Key string>"
     *                                              ]
     */
    public static function put(
        $upToken,
        $key,
        $data,
        $config,
        $params,
        $mime,
        $fname,
        $reqOpt = null
    ) {
        if ($reqOpt == null) {
            $reqOpt = new RequestOptions();
        }
        $fields = array('token' => $upToken);
        if ($key === null) {
        } else {
            $fields['key'] = $key;
        }

        //enable crc32 check by default
        $fields['crc32'] = \landuioss\crc32_data($data);

        if ($params) {
            foreach ($params as $k => $v) {
                $fields[$k] = $v;
            }
        }

        list($accessKey, $bucket, $err) = \landuioss\explodeUpToken($upToken);
        if ($err != null) {
            return array(null, $err);
        }

        list($upHost, $err) = $config->getUpHostV2($accessKey, $bucket);
        if ($err != null) {
            return array(null, $err);
        }


        $response = Client::multipartPost(
            $upHost,
            $fields,
            'file',
            $fname,
            $data,
            $mime,
            array(),
            $reqOpt
        );
        if (!$response->ok()) {
            return array(null, new Error($upHost, $response));
        }
        return array($response->json(), null);
    }

    /**
     * 上传文件到蓝队云，内部使用
     *
     * @param string $upToken 上传凭证
     * @param string $key 上传文件名
     * @param string $filePath 上传文件的路径
     * @param Config $config 上传配置
     * @param string $params 自定义变量，规格参考
     *                    https://developer.landuiyu.com/kodo/manual/1235/vars#xvar
     * @param string $mime 上传数据的mimeType
     *
     * @return array    包含已上传文件的信息，类似：
     *                                              [
     *                                                  "hash" => "<Hash string>",
     *                                                  "key" => "<Key string>"
     *                                              ]
     */
    public static function putFile(
        $upToken,
        $key,
        $filePath,
        $config,
        $params,
        $mime,
        $reqOpt = null
    ) {
        if ($reqOpt == null) {
            $reqOpt = new RequestOptions();
        }

        $fields = array('token' => $upToken, 'file' => self::createFile($filePath, $mime));
        if ($key !== null) {
            $fields['key'] = $key;
        }

        $fields['crc32'] = \landuioss\crc32_file($filePath);

        if ($params) {
            foreach ($params as $k => $v) {
                $fields[$k] = $v;
            }
        }
        $fields['key'] = $key;
        $headers = array('Content-Type' => 'multipart/form-data');

        list($accessKey, $bucket, $err) = \landuioss\explodeUpToken($upToken);
        if ($err != null) {
            return array(null, $err);
        }

        list($upHost, $err) = $config->getUpHostV2($accessKey, $bucket);
        if ($err != null) {
            return array(null, $err);
        }

        $response = Client::post($upHost, $fields, $headers, $reqOpt);
        if (!$response->ok()) {
            return array(null, new Error($upHost, $response));
        }
        return array($response->json(), null);
    }

    private static function createFile($filename, $mime)
    {
        // PHP 5.5 introduced a CurlFile object that deprecates the old @filename syntax
        // See: https://wiki.php.net/rfc/curl-file-upload
        if (function_exists('curl_file_create')) {
            return curl_file_create($filename, $mime);
        }

        // Use the old style if using an older version of PHP
        $value = "@{$filename}";
        if (!empty($mime)) {
            $value .= ';type=' . $mime;
        }

        return $value;
    }
}
