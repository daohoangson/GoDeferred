<?php

class GoDeferred_XenForo_Model_DataRegistry extends XFCP_GoDeferred_XenForo_Model_DataRegistry
{
    protected $_GoDeferred_queuedTimestamp = null;

    public function set($itemName, $value)
    {
        if ($itemName === 'deferredRun' && $this->_GoDeferred_queue($value) === true) {
            return;
        }

        parent::set($itemName, $value);
    }

    /**
     * @param int $timestamp
     * @return bool
     */
    protected function _GoDeferred_queue($timestamp)
    {
        if ($this->_GoDeferred_queuedTimestamp !== null && $this->_GoDeferred_queuedTimestamp <= $timestamp) {
            if (XenForo_Application::debugMode()) {
                XenForo_Helper_File::log(__CLASS__, sprintf('timestamp=%d -> skipped', $timestamp));
            }
            return true;
        }

        if (!XenForo_Application::isRegistered('options')) {
            return false;
        }

        $xfOptions = XenForo_Application::getOptions();
        $url = $xfOptions->get('GoDeferred_url');
        if (empty($url)) {
            return false;
        }

        $delay = $timestamp - time();

        $urlWithDelay = sprintf('%s&delay=%d', $url, $delay);
        $result = GoDeferred_Helper_Http::get($urlWithDelay);

        $queued = $result === 202;
        if ($queued) {
            $this->_GoDeferred_queuedTimestamp = $timestamp;
        }

        if (XenForo_Application::debugMode() || !$queued) {
            XenForo_Helper_File::log(__CLASS__, sprintf('%s -> %d', $urlWithDelay, $result));
        }

        return true;
    }
}
