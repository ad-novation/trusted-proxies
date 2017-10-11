<?php

namespace MobileCashout\TrustedProxies;

use Composer\Script\Event;

class Generator
{
    const GOOGLE_NETBLOCKS = [
        '_netblocks.google.com',
        '_netblocks2.google.com',
        '_netblocks3.google.com',
    ];

    const TARGET_FORMAT = __DIR__ . '/Generated/%s.%s';
    const FILE_FORMAT = '<?php return %s;';

    public static function generate(Event $event)
    {
        $all = array_merge(
            self::getForGoogle(),
            self::getForOpera(),
            self::getStaticForOpera(),
            self::getStaticForOnavo(),
            self::getStaticForCloudmosa(),
            self::getStaticForTrueInternationalGateway(),
            self::getStaticForEmirnet(),
            self::getStaticForUnitedOnline(),
            self::getStaticForQuantil(),
            self::getStaticForYandex()
        );

        $all = array_unique($all);

        sort($all);

        file_put_contents(sprintf(self::TARGET_FORMAT, 'data', 'php'), sprintf(self::FILE_FORMAT, var_export($all, true)));
        file_put_contents(sprintf(self::TARGET_FORMAT, 'data', 'json'), json_encode($all));
    }

    private static function getForGoogle()
    {
        $cidrs = [];

        $processTextEntry = function ($entry) use (&$cidrs) {
            if ('v=spf1' != mb_substr($entry, 0, 6)) {
                return;
            }

            $records = explode(' ', mb_substr($entry, 6, -5));

            foreach ($records as $record) {
                $cidrs[] = mb_substr($record, 4);
            }
        };

        foreach (self::GOOGLE_NETBLOCKS as $netblockSource) {
            $records = dns_get_record($netblockSource, DNS_TXT);
            foreach ($records as $record) {
                if (isset($record['txt'])) {
                    $processTextEntry($record['txt']);
                }
            }
        }

        return array_filter(array_unique($cidrs));
    }

    private static function getForOpera()
    {
        $cidrs = [];

        $data = file_get_contents('http://wipmania.com/static/worldip.opera.conf');
        if (!$data) {
            return [];
        }

        $lines = explode("\n", $data);

        foreach ($lines as $line) {
            if ('#' == substr($line, 0, 1)) {
                continue;
            }

            $cidr = mb_substr($line, 6, -1);
            $forValidation = strpos($cidr, '/') ? mb_substr($cidr, 0, -3) : $cidr;

            if (filter_var($forValidation, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $cidrs[] = $cidr;
            }
        }

        return $cidrs;
    }

    private static function getStaticForOpera()
    {
        return [
            '37.228.104.0/21',
            '82.145.208.0/20',
            '91.203.96.0/22',
            '107.167.96.0/19',
            '119.81.80.64/27',
            '185.26.180.0/22',
            '2001:4c28::/32',
            '2620:117:c000::/40',
        ];
    }

    private static function getStaticForOnavo()
    {
        return [
            '147.75.208.0/20',
            '185.89.216.0/22',
            '2a03:83e0::/32',
        ];
    }

    // Cloudmosa Puffin accelerator
    private static function getStaticForCloudmosa()
    {
        return [
            '45.33.128.0/20',
            '107.178.32.0/20',
        ];
    }

    /**
     * TIG TH Gateway
     */
    private static function getStaticForTrueInternationalGateway()
    {
        return [
            '27.123.17.0/25',
        ];
    }

    private static function getStaticForEmirnet()
    {
        return [
            '195.229.242.52/30',
            '195.229.242.56/31',
            '195.229.242.58/32',
        ];
    }

    private static function getStaticForUnitedOnline()
    {
        return [
            '64.136.26.0/23',
            '64.136.47.12/32',
            '64.136.55.12/32',
        ];
    }

    /**
     * Quantil CDN, used by Mobile Carriers in India
     */
    private static function getStaticForQuantil()
    {
        return [
            '8.37.224.0/20',
            '157.185.128.0/21',
            '157.185.191.0/24',
            '168.235.192.0/20',
            '185.119.40.0/22',
        ];
    }

    /**
     * Yandex browser Turbo mode
     */
    private static function getStaticForYandex()
    {
        return [
            '37.140.189.192/26',
            '95.108.133.192/26',
            '95.108.141.32/27',
            '95.108.141.64/27',
            '95.108.225.192/26',
            '95.108.244.32/27',
            '95.108.244.64/27',
            '95.213.218.32/27',
            '95.213.218.64/28',
            '95.213.218.160/27',
            '95.213.218.192/26',
        ];
    }

}
