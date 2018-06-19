<?php

class GoDeferred_Deferred_HealthCheck extends XenForo_Deferred_Abstract
{
    public function execute(array $deferred, array $data, $targetRunTime, &$status)
    {
        $data = array_merge(['url' => '', 'retries' => 3], $data);

        if (empty($data['url']) || empty($data['retries'])) {
            return true;
        }

        $url = $data['url'];
        $pinged = GoDeferred_Helper_Http::get($url);

        if (XenForo_Application::debugMode() || !$pinged) {
            XenForo_Helper_File::log(__CLASS__, sprintf('%s -> %d', $url, $pinged));
        }

        XenForo_Application::defer(
            'GoDeferred_Deferred_HealthCheck',
            [
                'url' => $url,
                'retries' => $data['retries'] - 1
            ],
            null,
            false,
            time() + 30
        );

        return true;
    }
}
