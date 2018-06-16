<?php

class GoDeferred_Listener
{
    public static function load_class_XenForo_Model_DataRegistry($class, array &$extend)
    {
        if ($class === 'XenForo_Model_DataRegistry') {
            $extend[] = 'GoDeferred_XenForo_Model_DataRegistry';
        }
    }

    public static function file_health_check(XenForo_ControllerAdmin_Abstract $controller, array &$hashes)
    {
        $hashes += GoDeferred_FileSums::getHashes();
    }
}
