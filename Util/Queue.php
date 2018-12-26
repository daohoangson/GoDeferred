<?php

namespace GoDeferred\Util;

class Queue
{
    protected static $_latestTimestamp = null;

    public static function canEnqueueViaHeader()
    {
        if (empty($_SERVER['REQUEST_METHOD']) ||
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            empty($_SERVER['HTTP_X_GO_DEFERRED_VERSION'])
        ) {
            return false;
        }

        $requesterVersion = $_SERVER['HTTP_X_GO_DEFERRED_VERSION'];
        if ($requesterVersion < 2018061901) {
            return false;
        }

        return true;
    }

    public static function enqueue($timestamp)
    {
        $delay = $timestamp - time();

        if (self::$_latestTimestamp !== null &&
            self::$_latestTimestamp <= $timestamp
        ) {
            if (\XF::$debugMode) {
                \XF\Util\File::log(__CLASS__, sprintf('delay=%d -> skipped', $delay));
            }
            return true;
        }

        if (self::enqueueViaHeader($timestamp, $delay)) {
            return true;
        }

        return self::enqueueViaHttp($timestamp, $delay);
    }

    protected static function enqueueViaHeader($timestamp, $delay)
    {
        if (headers_sent() || !self::canEnqueueViaHeader()) {
            return false;
        }

        $header = 'X-Go-Deferred-Enqueue: ' . $delay;
        header($header, true);
        self::$_latestTimestamp = $timestamp;

        if (\XF::$debugMode) {
            \XF\Util\File::log(__CLASS__, sprintf('header(%s)', $header));
        }

        return true;
    }

    protected static function enqueueViaHttp($timestamp, $delay)
    {
        $xfOptions = \XF::app()->options();
        $url = $xfOptions->GoDeferred_url;
        if (empty($url)) {
            return false;
        }


        $urlWithDelay = sprintf('%s&delay=%d', $url, $delay);
        $result = self::httpGet($urlWithDelay);

        $queued = $result === 202;
        if ($queued) {
            self::$_latestTimestamp = $timestamp;
        }

        if (\XF::$debugMode || !$queued) {
            \XF\Util\File::log(__CLASS__, sprintf('%s -> %d', $urlWithDelay, $result));
        }

        return true;
    }

    protected static function httpGet($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $responseStatus = 0;
        try {
            curl_exec($ch);
            $curlInfo = curl_getinfo($ch);
            curl_close($ch);
            if (isset($curlInfo['http_code'])) {
                $responseStatus = $curlInfo['http_code'];
            }
        } catch (\Exception $e) {
            // ignore
        }

        return $responseStatus;
    }
}
