<?php

class GoDeferred_Helper_Queue
{
    protected static $_latestTimestamp = null;

    public static function enqueue($timestamp)
    {
        $delay = $timestamp - time();

        if (self::$_latestTimestamp !== null &&
            self::$_latestTimestamp <= $timestamp
        ) {
            if (XenForo_Application::debugMode()) {
                XenForo_Helper_File::log(__CLASS__, sprintf('delay=%d -> skipped', $delay));
            }
            return true;
        }

        if (self::_enqueueViaHeader($timestamp, $delay)) {
            return true;
        }

        return self::_enqueueViaHttp($timestamp, $delay);
    }

    protected static function _enqueueViaHeader($timestamp, $delay)
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

        $header = 'X-Go-Deferred-Enqueue: ' . $delay;
        header($header, true);
        self::$_latestTimestamp = $timestamp;

        if (XenForo_Application::debugMode()) {
            XenForo_Helper_File::log(__CLASS__, sprintf('header(%s)', $header));
        }

        return true;
    }

    protected static function _enqueueViaHttp($timestamp, $delay)
    {
        if (!XenForo_Application::isRegistered('options')) {
            return false;
        }

        $xfOptions = XenForo_Application::getOptions();
        $url = $xfOptions->get('GoDeferred_url');
        if (empty($url)) {
            return false;
        }


        $urlWithDelay = sprintf('%s&delay=%d', $url, $delay);
        $result = GoDeferred_Helper_Http::get($urlWithDelay);

        $queued = $result === 202;
        if ($queued) {
            self::$_latestTimestamp = $timestamp;
        }

        if (XenForo_Application::debugMode() || !$queued) {
            XenForo_Helper_File::log(__CLASS__, sprintf('%s -> %d', $urlWithDelay, $result));
        }

        return true;
    }
}
