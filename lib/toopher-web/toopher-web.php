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
    public static function auth_iframe_url($username, $action, $ttl, $automation_allowed, $baseUrl, $key, $secret, $session_token=Null)
    {
        $params = array(
            'username' => $username,
            'action' => $action,
            'automation_allowed' => $automation_allowed ? 'True' : 'False'
        );
        return ToopherWeb::getOAuthUrl($baseUrl . 'web/auth', $params, $ttl, $key, $secret, $session_token);
    }

    public static function validate($secret, $data, $ttl=100)
    {
        $maybe_sig = $data['toopher_sig'];
        unset($data['toopher_sig']);
        $signature_valid = ToopherWeb::signature($secret, $data) === $maybe_sig;
        $ttl_valid = (time() - $ttl) < (int)$data['timestamp'];
        return $signature_valid && $ttl_valid;
    }

    public static function signature($secret, $data)
    {
        ksort($data);
        $to_sign = http_build_query($data);
        $result = base64_encode(hash_hmac('sha1', $to_sign, $secret, true));
        return $result;
    }

    private static function getOAuthUrl($url, $getParams, $ttl, $key, $secret, $session_token=Null)
    {
        $expiresAt = (time() + $ttl);
        $getParams['ttl'] = (string)$expiresAt;
        if ($session_token){
            $getParams['session_token'] = $session_token;
        }
        $oauth = new OAuthConsumer($key, $secret);
        $req = OAuthRequest::from_consumer_and_token($oauth, NULL, 'GET', $url, $getParams);
        $req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $oauth, null);
        return $req->to_url();
    }
}

?>
