<?php

/*
 * This file is part of the nilsir/curlify.
 * (c) nilsir <nilsir@qq.com>
 * This source file is subject to the MIT license that is bundled.
 */

namespace Nilsir\Curlify;

class Curlify
{
    public function toCurl($compressed = false, $verify = true)
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $headers = $this->getApacheRequestHeaders();
        $requestType = $_SERVER['CONTENT_TYPE'];


        $parts = [
            'curl',
            '--location --request ' . $requestMethod,
        ];

        foreach ($headers as $key => $val) {
            if ('content-length' == strtolower($key)) {
                continue;
            }
            $parts[] = sprintf('--header "%s:%s"', $key, $val);
        }

        if (isset($requestType) && $requestType == 'application/json') {
            $body = file_get_contents('php://input');
            $parts[] = '--data-raw ' . "'{$body}'";
        }

        if ($compressed) {
            $parts[] = '--compressed';
        }

        if (!$verify) {
            $parts[] = '--insecure';
        }

        $parts[] = $this->getUrl();

        return join(' ', $parts);
    }

    private function getUrl()
    {
        $pageURL = 'http';
        if (isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }

    private function getApacheRequestHeaders()
    {
        if (!function_exists('apache_request_headers')) {
            $arh = [];
            $rx_http = '/\AHTTP_/';
            foreach ($_SERVER as $key => $val) {
                if (preg_match($rx_http, $key)) {
                    $arh_key = preg_replace($rx_http, '', $key);
                    $rx_matches = [];
                    // do some nasty string manipulations to restore the original letter case
                    // this should work in most cases
                    $rx_matches = explode('_', strtolower($arh_key));
                    if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
                        foreach ($rx_matches as $ak_key => $ak_val) {
                            $rx_matches[$ak_key] = ucfirst($ak_val);
                        }
                        $arh_key = implode('-', $rx_matches);
                    }
                    $arh[$arh_key] = $val;
                }
            }
            if (isset($_SERVER['CONTENT_TYPE'])) {
                $arh['Content-Type'] = $_SERVER['CONTENT_TYPE'];
            }
            if (isset($_SERVER['CONTENT_LENGTH'])) {
                $arh['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
            }
            return $arh;
        }

        return apache_request_headers();
    }
}
