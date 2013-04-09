<?php
require_once('OAuth.php');

class ToopherWeb
{
    public static function pair_iframe_url($username, $ttl, $baseUrl, $key, $secret)
    {
        return ToopherWeb::_pair_iframe_url($username, $ttl, 'pair', $baseUrl, $key, $secret);
    }
    public static function unpair_iframe_url($username, $ttl, $baseUrl, $key, $secret)
    {
        return ToopherWeb::_pair_iframe_url($username, $ttl, 'unpair', $baseUrl, $key, $secret);
    }
    private static function _pair_iframe_url($username, $ttl, $path, $baseUrl, $key, $secret)
    {
        $params = array(
            'username' => $username
        );

        return ToopherWeb::getOAuthUrl($baseUrl . 'web/' . $path, $params, $ttl, $key, $secret);
    }
    public static function auth_iframe_url($username, $action, $ttl, $automation_allowed=True, $baseUrl, $key, $secret)
    {
        $params = array(
            'username' => $username,
            'action' => $action,
            'automation_allowed' => $automation_allowed ? 'True' : 'False'
        );
        return ToopherWeb::getOAuthUrl($baseUrl . 'web/auth', $params, $ttl, $key, $secret);
    }

    private static function getOAuthUrl($url, $getParams, $ttl, $key, $secret)
    {
        $expiresAt = 1000 * (time() + $ttl);
        $getParams['ttl'] = (string)$expiresAt;
        $oauth = new OAuthConsumer($key, $secret);
        $req = OAuthRequest::from_consumer_and_token($oauth, NULL, 'GET', $url, $getParams);
        $req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $oauth, null);
        return $req->to_url();
    }
}

?>
