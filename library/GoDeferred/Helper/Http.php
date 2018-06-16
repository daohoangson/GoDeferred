<?php

class GoDeferred_Helper_Http
{
    /**
     * @param string $url
     * @return int
     */
    public static function get($url)
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
        } catch (Exception $e) {
            // ignore
        }

        return $responseStatus;
    }
}
