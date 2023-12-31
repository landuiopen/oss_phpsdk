<?php
namespace landui\oss;

use landui\oss\Http\Client;
use landui\oss\Http\Error;
use landui\oss\Http\Middleware\RetryDomainsMiddleware;
use landui\oss\Http\RequestOptions;

class Region
{

    //源站上传域名
    public $srcUpHosts;
    //CDN加速上传域名
    public $cdnUpHosts;
    //资源管理域名
    public $rsHost;
    //资源列举域名
    public $rsfHost;
    //资源处理域名
    public $apiHost;
    //IOVIP域名
    public $iovipHost;
    // TTL
    public $ttl;

    //构造一个Region对象
    public function __construct(
        $srcUpHosts = array(),
        $cdnUpHosts = array(),
        $rsHost = "rs-z0.landuiyuapi.com",
        $rsfHost = "rsf-z0.landuiyuapi.com",
        $apiHost = "api.landuiyuapi.com",
        $iovipHost = null,
        $ttl = null
    ) {

        $this->srcUpHosts = $srcUpHosts;
        $this->cdnUpHosts = $cdnUpHosts;
        $this->rsHost = $rsHost;
        $this->rsfHost = $rsfHost;
        $this->apiHost = $apiHost;
        $this->iovipHost = $iovipHost;
        $this->ttl = $ttl;
    }

    //华东机房
    public static function regionHuadong()
    {
        $regionHuadong = new Region(
            array("up.landuiyun.cn"),
            array('upload.landuiyun.cn'),
            'rs-z0.landuiyuapi.com',
            'rsf-z0.landuiyuapi.com',
            'api.landuiyuapi.com',
            'iovip.qbox.me'
        );
        return $regionHuadong;
    }

    //华东机房内网上传
    public static function qvmRegionHuadong()
    {
        $qvmRegionHuadong = new Region(
            array("free-qvm-z0-xs.landuiyun.cn"),
            'rs-z0.landuiyuapi.com',
            'rsf-z0.landuiyuapi.com',
            'api.landuiyuapi.com',
            'iovip.qbox.me'
        );
        return $qvmRegionHuadong;
    }

    //华北机房内网上传
    public static function qvmRegionHuabei()
    {
        $qvmRegionHuabei = new Region(
            array("free-qvm-z1-zz.landuiyun.cn"),
            "rs-z1.landuiyuapi.com",
            "rsf-z1.landuiyuapi.com",
            "api-z1.landuiyuapi.com",
            "iovip-z1.qbox.me"
        );
        return $qvmRegionHuabei;
    }

    //华北机房
    public static function regionHuabei()
    {
        $regionHuabei = new Region(
            array('up-z1.landuiyun.cn'),
            array('upload-z1.landuiyun.cn'),
            "rs-z1.landuiyuapi.com",
            "rsf-z1.landuiyuapi.com",
            "api-z1.landuiyuapi.com",
            "iovip-z1.qbox.me"
        );

        return $regionHuabei;
    }

    //华南机房
    public static function regionHuanan()
    {
        $regionHuanan = new Region(
            array('up-z2.landuiyun.cn'),
            array('upload-z2.landuiyun.cn'),
            "rs-z2.landuiyuapi.com",
            "rsf-z2.landuiyuapi.com",
            "api-z2.landuiyuapi.com",
            "iovip-z2.qbox.me"
        );
        return $regionHuanan;
    }

    //华东2 机房
    public static function regionHuadong2()
    {
        return new Region(
            array('up-cn-east-2.landuiyun.cn'),
            array('upload-cn-east-2.landuiyun.cn'),
            "rs-cn-east-2.landuiyuapi.com",
            "rsf-cn-east-2.landuiyuapi.com",
            "api-cn-east-2.landuiyuapi.com",
            "iovip-cn-east-2.landuiyuio.com"
        );
    }

    //北美机房
    public static function regionNorthAmerica()
    {
        //北美机房
        $regionNorthAmerica = new Region(
            array('up-na0.landuiyun.cn'),
            array('upload-na0.landuiyun.cn'),
            "rs-na0.landuiyuapi.com",
            "rsf-na0.landuiyuapi.com",
            "api-na0.landuiyuapi.com",
            "iovip-na0.qbox.me"
        );
        return $regionNorthAmerica;
    }

    //新加坡机房
    public static function regionSingapore()
    {
        //新加坡机房
        $regionSingapore = new Region(
            array('up-as0.landuiyun.cn'),
            array('upload-as0.landuiyun.cn'),
            "rs-as0.landuiyuapi.com",
            "rsf-as0.landuiyuapi.com",
            "api-as0.landuiyuapi.com",
            "iovip-as0.qbox.me"
        );
        return $regionSingapore;
    }

    /*
     * GET /v4/query?ak=<ak>&bucket=<bucket>
     **/
    public static function queryRegion($ak, $bucket, $ucHost = null, $backupUcHosts = array(), $retryTimes = 2)
    {
        $region = new Region();
        if (!$ucHost) {
            $ucHost = "https://" . Config::QUERY_REGION_HOST;
        }
        $url = $ucHost . '/v4/query' . "?ak=$ak&bucket=$bucket";
        $reqOpt = new RequestOptions();
        $reqOpt->middlewares = array(
            new RetryDomainsMiddleware(
                $backupUcHosts,
                $retryTimes
            )
        );
        $ret = Client::Get($url, array(), $reqOpt);
        if (!$ret->ok()) {
            return array(null, new Error($url, $ret));
        }
        $r = ($ret->body === null) ? array() : json_decode($ret->body,true);
        if (!is_array($r["hosts"]) || count($r["hosts"]) == 0) {
            return array(null, new Error($url, $ret));
        }

        // parse region;
        $regionHost = $r["hosts"][0];
        $region->cdnUpHosts = array_merge($region->cdnUpHosts, $regionHost['up']['domains']);
        $region->srcUpHosts = array_merge($region->srcUpHosts, $regionHost['up']['domains']);

        // set specific hosts
        $region->iovipHost = $regionHost['io']['domains'][0];
        if (isset($regionHost['rs']['domains']) && count($regionHost['rs']['domains']) > 0) {
            $region->rsHost = $regionHost['rs']['domains'][0];
        } else {
            $region->rsHost = Config::RS_HOST;
        }
        if (isset($regionHost['rsf']['domains']) && count($regionHost['rsf']['domains']) > 0) {
            $region->rsfHost = $regionHost['rsf']['domains'][0];
        } else {
            $region->rsfHost = Config::RSF_HOST;
        }
        if (isset($regionHost['api']['domains']) && count($regionHost['api']['domains']) > 0) {
            $region->apiHost = $regionHost['api']['domains'][0];
        } else {
            $region->apiHost = Config::API_HOST;
        }

        // set ttl
        $region->ttl = $regionHost['ttl'];

        return $region;
    }
}
