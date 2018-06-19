<?php

class GoDeferred_XenForo_Model_DataRegistry extends XFCP_GoDeferred_XenForo_Model_DataRegistry
{
    public function set($itemName, $value)
    {
        if ($itemName === 'deferredRun' &&
            GoDeferred_Helper_Queue::enqueue($value) === true
        ) {
            return;
        }

        parent::set($itemName, $value);
    }
}
