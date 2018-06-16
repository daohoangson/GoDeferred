<?php

class GoDeferred_CronEntry_HealthCheck
{
    public static function ping()
    {
        $xfOptions = XenForo_Application::getOptions();
        $url = $xfOptions->get('GoDeferred_healthCheck');
        if (empty($url)) {
            return;
        }

        $result = GoDeferred_Helper_Http::get($url);

        if (XenForo_Application::debugMode()) {
            XenForo_Helper_File::log(__CLASS__, sprintf('%s -> %d', $url, $result));
        }
    }
}
