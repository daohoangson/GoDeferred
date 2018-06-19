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

        XenForo_Application::defer('GoDeferred_Deferred_HealthCheck', ['url' => $url]);
    }
}
