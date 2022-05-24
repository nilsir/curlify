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
        $headers = apache_request_headers();
        $requestType = $_SERVER['CONTENT_TYPE'];


        $parts = [
            'curl',
            '-X ' . $requestMethod,
        ];

        foreach ($headers as $key => $val) {
            if ('content-length' == strtolower($key)) {
                continue;
            }
            $parts[] = sprintf('-H "%s:%s"', $key, $val);
        }

        if (isset($requestType) && $requestType == 'application/json') {
            $body = file_get_contents('php://input');
            $parts[] = '-d ' . "'{$body}'";
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
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }
}
