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

    const TARGET_FORMAT = __DIR__.'/Generated/%s.%s';
    const FILE_FORMAT = '<?php return %s;';

    public static function generate(Event $event)
    {
        $all = array_merge(
            self::getForGoogle(),
            self::getForOpera()
        );

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
}
