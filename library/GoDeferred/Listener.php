<?php

class GoDeferred_Listener
{
    /**
     * @param string $class
     * @param array $extend
     */
    public static function load_class_XenForo_Model_DataRegistry($class, array &$extend)
    {
        if ($class === 'XenForo_Model_DataRegistry') {
            $extend[] = 'GoDeferred_XenForo_Model_DataRegistry';
        }
    }

    /**
     * @param XenForo_ControllerAdmin_Abstract $controller
     * @param array $hashes
     */
    public static function file_health_check($controller, array &$hashes)
    {
        $hashes += GoDeferred_FileSums::getHashes();
    }

    /**
     * @param XenForo_FrontController $fc
     * @param XenForo_ControllerResponse_Abstract $controllerResponse
     * @param XenForo_ViewRenderer_Abstract $viewRenderer
     * @param array $containerParams
     */
    public static function front_controller_pre_view($fc, &$controllerResponse, &$viewRenderer, array &$containerParams)
    {
        if (!XenForo_Application::isRegistered('options')) {
            return;
        }

        $xfOptions = XenForo_Application::getOptions();
        $url = $xfOptions->get('GoDeferred_url');
        if (empty($url)) {
            return;
        }

        XenForo_Application::set('deferredRun', 0);
    }
}
