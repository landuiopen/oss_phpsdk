<?php
namespace landuioss;

final class Config
{
    const SDK_VER = '1.0';

    const BLOCK_SIZE = 4194304;
    // 4*1024*1024 分块上传块大小，该参数为接口规格，不能修改
    const RSF_HOST = 'rsf.landuiyuapi.com';
    const API_HOST = 'api.landuiyuapi.com';
    // RS Host
    const RS_HOST  = 'rs.landuiyuapi.com';
    // UC Host
    const UC_HOST = 'uc.qbox.me';
    const QUERY_REGION_HOST = 'oss.landui.com';
    const ARGUS_HOST        = 'ai.landuiyuapi.com';
    const CASTER_HOST       = 'pili-caster.landuiyuapi.com';

    // Zone 空间对应的存储区域
    public $region;

    // BOOL 是否使用https域名
    public $useHTTPS;

    // BOOL 是否使用CDN加速上传域名
    public $useCdnDomains;

    /**
     * @var Region
     */
    public $zone;

    // Zone Cache
    private $regionCache;

    // UC Host
    private $ucHost;

    private $queryRegionHost;

    // backup UC Hosts
    private $backupQueryRegionHosts;

    // backup UC Hosts max retry time
    public $backupUcHostsRetryTimes;

    // 构造函数
    public function __construct(Region $z=null)
    {
        $this->zone                    = $z;
        $this->useHTTPS                = true;
        $this->useCdnDomains           = false;
        $this->regionCache             = [];
        $this->ucHost                  = self::UC_HOST;
        $this->queryRegionHost         = self::QUERY_REGION_HOST;
        $this->backupQueryRegionHosts  = [
            'uc.qbox.me',
            'api.landuiyu.com',
        ];
        $this->backupUcHostsRetryTimes = 2;

    }//end __construct()


    public function setUcHost($ucHost)
    {
        $this->ucHost = $ucHost;
        $this->setQueryRegionHost($ucHost);

    }//end setUcHost()


    public function getUcHost()
    {
        if ($this->useHTTPS === true) {
            $scheme = 'https://';
        } else {
            $scheme = 'http://';
        }

        return $scheme.$this->ucHost;

    }//end getUcHost()


    public function setQueryRegionHost($host, $backupHosts=[])
    {
        $this->queryRegionHost        = $host;
        $this->backupQueryRegionHosts = $backupHosts;

    }//end setQueryRegionHost()


    public function getQueryRegionHost()
    {
        if ($this->useHTTPS === true) {
            $scheme = 'https://';
        } else {
            $scheme = 'http://';
        }

        return $scheme.$this->queryRegionHost;

    }//end getQueryRegionHost()


    public function setBackupQueryRegionHosts($hosts=[])
    {
        $this->backupQueryRegionHosts = $hosts;

    }//end setBackupQueryRegionHosts()


    public function getBackupQueryRegionHosts()
    {
        return $this->backupQueryRegionHosts;

    }//end getBackupQueryRegionHosts()


    public function getUpHost($accessKey, $bucket)
    {
        $region = $this->getRegion($accessKey, $bucket);
        if ($this->useHTTPS === true) {
            $scheme = 'https://';
        } else {
            $scheme = 'http://';
        }

        $host = $region->srcUpHosts[0];
        if ($this->useCdnDomains === true) {
            $host = $region->cdnUpHosts[0];
        }

        return $scheme.$host;

    }//end getUpHost()


    public function getUpHostV2($accessKey, $bucket)
    {
        list($region, $err) = $this->getRegionV2($accessKey, $bucket);
        if ($err != null) {
            return [
                null,
                $err,
            ];
        }

        if ($this->useHTTPS === true) {
            $scheme = 'https://';
        } else {
            $scheme = 'http://';
        }

        $host = $region->srcUpHosts[0];
        if ($this->useCdnDomains === true) {
            $host = $region->cdnUpHosts[0];
        }

        return [
            $scheme.$host,
            null,
        ];

    }//end getUpHostV2()


    public function getUpBackupHost($accessKey, $bucket)
    {
        $region = $this->getRegion($accessKey, $bucket);
        if ($this->useHTTPS === true) {
            $scheme = 'https://';
        } else {
            $scheme = 'http://';
        }

        $host = $region->cdnUpHosts[0];
        if ($this->useCdnDomains === true) {
            $host = $region->srcUpHosts[0];
        }

        return $scheme.$host;

    }//end getUpBackupHost()


    public function getUpBackupHostV2($accessKey, $bucket)
    {
        list($region, $err) = $this->getRegionV2($accessKey, $bucket);
        if ($err != null) {
            return [
                null,
                $err,
            ];
        }

        if ($this->useHTTPS === true) {
            $scheme = 'https://';
        } else {
            $scheme = 'http://';
        }

        $host = $region->cdnUpHosts[0];
        if ($this->useCdnDomains === true) {
            $host = $region->srcUpHosts[0];
        }

        return [
            $scheme.$host,
            null,
        ];

    }//end getUpBackupHostV2()


    public function getRsHost($accessKey, $bucket)
    {
        $region = $this->getRegion($accessKey, $bucket);

        if ($this->useHTTPS === true) {
            $scheme = 'https://';
        } else {
            $scheme = 'http://';
        }

        return $scheme.$region->rsHost;

    }//end getRsHost()


    public function getRsHostV2($accessKey, $bucket)
    {
        list($region, $err) = $this->getRegionV2($accessKey, $bucket);
        if ($err != null) {
            return [
                null,
                $err,
            ];
        }

        if ($this->useHTTPS === true) {
            $scheme = 'https://';
        } else {
            $scheme = 'http://';
        }

        return [
            $scheme.$region->rsHost,
            null,
        ];

    }//end getRsHostV2()


    public function getRsfHost($accessKey, $bucket)
    {
        $region = $this->getRegion($accessKey, $bucket);

        if ($this->useHTTPS === true) {
            $scheme = 'https://';
        } else {
            $scheme = 'http://';
        }

        return $scheme.$region->rsfHost;

    }//end getRsfHost()


    public function getRsfHostV2($accessKey, $bucket)
    {
        list($region, $err) = $this->getRegionV2($accessKey, $bucket);
        if ($err != null) {
            return [
                null,
                $err,
            ];
        }

        if ($this->useHTTPS === true) {
            $scheme = 'https://';
        } else {
            $scheme = 'http://';
        }

        return [
            $scheme.$region->rsfHost,
            null,
        ];

    }//end getRsfHostV2()


    public function getIovipHost($accessKey, $bucket)
    {
        $region = $this->getRegion($accessKey, $bucket);

        if ($this->useHTTPS === true) {
            $scheme = 'https://';
        } else {
            $scheme = 'http://';
        }

        return $scheme.$region->iovipHost;

    }//end getIovipHost()


    public function getIovipHostV2($accessKey, $bucket)
    {
        list($region, $err) = $this->getRegionV2($accessKey, $bucket);
        if ($err != null) {
            return [
                null,
                $err,
            ];
        }

        if ($this->useHTTPS === true) {
            $scheme = 'https://';
        } else {
            $scheme = 'http://';
        }

        return [
            $scheme.$region->iovipHost,
            null,
        ];

    }//end getIovipHostV2()


    public function getApiHost($accessKey, $bucket)
    {
        $region = $this->getRegion($accessKey, $bucket);

        if ($this->useHTTPS === true) {
            $scheme = 'https://';
        } else {
            $scheme = 'http://';
        }

        return $scheme.$region->apiHost;

    }//end getApiHost()


    public function getApiHostV2($accessKey, $bucket)
    {
        list($region, $err) = $this->getRegionV2($accessKey, $bucket);
        if ($err != null) {
            return [
                null,
                $err,
            ];
        }

        if ($this->useHTTPS === true) {
            $scheme = 'https://';
        } else {
            $scheme = 'http://';
        }

        return [
            $scheme.$region->apiHost,
            null,
        ];

    }//end getApiHostV2()


    /**
     * 从缓存中获取区域
     *
     * @param  string $cacheId 缓存 ID
     * @return null|Region
     */
    private function getRegionCache($cacheId)
    {
        if (isset($this->regionCache[$cacheId])
            && isset($this->regionCache[$cacheId]['deadline'])
            && time() < $this->regionCache[$cacheId]['deadline']
        ) {
            return $this->regionCache[$cacheId]['region'];
        }

        return null;

    }//end getRegionCache()


    /**
     * 将区域设置到缓存中
     *
     * @param  string $cacheId 缓存 ID
     * @param  Region $region  缓存 ID
     * @return void
     */
    private function setRegionCache($cacheId, $region)
    {
        $this->regionCache[$cacheId] = ['region' => $region];
        if (isset($region->ttl)) {
            $this->regionCache[$cacheId]['deadline'] = (time() + $region->ttl);
        }

    }//end setRegionCache()


    /**
     * 从缓存中获取区域
     *
     * @param  string $accessKey
     * @param  string $bucket
     * @return Region
     *
     * @throws \Exception
     */
    private function getRegion($accessKey, $bucket)
    {
        if (isset($this->zone)) {
            return $this->zone;
        }

        $cacheId     = "$accessKey:$bucket";
        $regionCache = $this->getRegionCache($cacheId);
        if ($regionCache) {
            return $regionCache;
        }

        $region = Zone::queryZone(
            $accessKey,
            $bucket,
            $this->getQueryRegionHost(),
            $this->getBackupQueryRegionHosts(),
            $this->backupUcHostsRetryTimes
        );
        ;
        if (is_array($region)) {
            list($region, $err) = $region;
            if ($err != null) {
                throw new \Exception($err->message());
            }
        }

        $this->setRegionCache($cacheId, $region);
        return $region;

    }//end getRegion()


    private function getRegionV2($accessKey, $bucket)
    {
        if (isset($this->zone)) {
            return [
                $this->zone,
                null,
            ];
        }

        $cacheId     = "$accessKey:$bucket";
        $regionCache = $this->getRegionCache($cacheId);
        if (isset($regionCache)) {
            return [
                $regionCache,
                null,
            ];
        }

        $region = Zone::queryZone(
            $accessKey,
            $bucket,
            $this->getQueryRegionHost(),
            $this->getBackupQueryRegionHosts(),
            $this->backupUcHostsRetryTimes
        );
        if (is_array($region)) {
            list($region, $err) = $region;
            return [
                $region,
                $err,
            ];
        }

        $this->setRegionCache($cacheId, $region);
        return [
            $region,
            null,
        ];

    }//end getRegionV2()


}//end class
