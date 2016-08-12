<?php

namespace MobileCashout\TrustedProxies;

use Symfony\Component\HttpFoundation\IpUtils;

class ProxyManager
{
    /**
     * @var array
     */
    private $cidrs = [];

    public function __construct($dataFile = __DIR__.'/Generated/data.php')
    {
        $this->cidrs = require $dataFile;
    }

    /**
     * @param string $ip
     *
     * @return bool
     */
    public function isTrusted($ip)
    {
        return IpUtils::checkIp($ip, $this->cidrs);
    }

    /**
     * @param string $ip
     *
     * @return bool
     */
    public function isIpv4($ip)
    {
        return (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    /**
     * @param string $ip
     *
     * @return bool
     */
    public function isIpv6($ip)
    {
        return (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    /**
     * @return array
     */
    public function getCidrs()
    {
        return $this->cidrs;
    }
}
