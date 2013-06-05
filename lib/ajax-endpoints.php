<?php
require_once('toopher-web/toopher-web.php');



function toopherGetPairUrlForCurrentUser() {
    $key = get_option('toopher_api_key');
    $secret = get_option('toopher_api_secret');
    $baseUrl = get_option('toopher_api_url');
    error_log('in ajax handler - key, secret is ' . $key . ', ' . $secret);
    $user = wp_get_current_user();
    
    $url = ToopherWeb::pair_iframe_url($user->data->user_login, 60, $baseUrl, $key, $secret);
    $postback = 'toopher_finish_pairing';
    echo json_encode(array('toopher_req'=> $url, 'toopher_postback'=>$postback));

    die();  // wordpress sucks.
}

add_action('wp_ajax_toopher_get_pair_url_for_current_user', 'toopherGetPairUrlForCurrentUser');
error_log('added ajax handler');
//add_action('all', create_function('', 'error_log(current_filter());'));
?>
