<?php
require_once('toopher-web/toopher-web.php');



function toopherGetPairUrlForCurrentUser() {
    $key = 'aaaaaaaaaa'; //getenv('TOOPHER_CONSUMER_KEY');
    $secret = 'aaaaaaaaaa'; //getenv('TOOPHER_CONSUMER_SECRET');
    $baseUrl = 'http://10.0.1.3:8000/v1/';
    error_log('in ajax handler - key, secret is ' . $key . ', ' . $secret);
    $user = wp_get_current_user();
    
    $url = ToopherWeb::pair_iframe_url($user->data->user_login, 60, $baseUrl, $key, $secret);
    $postback = 'toopher_finish_pairing';
    echo json_encode(array('toopher-req'=> $url, 'toopher-postback'=>$postback));

    die();  // wordpress sucks.
}

add_action('wp_ajax_toopher_get_pair_url_for_current_user', 'toopherGetPairUrlForCurrentUser');
error_log('added ajax handler');
//add_action('all', create_function('', 'error_log(current_filter());'));
?>
