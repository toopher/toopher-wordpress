<?php

add_filter('authenticate', 'toopher_begin_authenticate_login', 100, 1);
add_filter('authenticate', 'toopher_finish_authenticate_login', 0, 1);

/**
 * run last in the authenticate chain.  If user has passed previous auth, see if they
 * need to be toopher-authenticated and handle it.  Else is a no-op.
 **/

function toopher_begin_authenticate_login($user){
    error_log('toopher_begin_authenticate_login: post is ' . var_export($_POST, true));
    if (is_a($user, 'WP_User')){
        error_log('user is ' . var_export(get_object_vars($user), true));

        if (get_user_meta((int)$user->ID, 't2s_user_paired', true)){
            error_log('user should be toopher-authenticated');
            toopher_login_pending($user);
            exit();
        }
    }

    return $user;
}

function toopher_finish_authenticate_login($user){
    if(isset($_POST['toopher_sig'])){
        error_log('toopher_finish_authenticate_login: post is ' . var_export($_POST, true));
        $pending_user_id = $_POST['pending_user_id'];
        $redirect_to = $_POST['redirect_to'];
    }
    return $user;
}

function toopher_login_pending($user){
    $key = get_option('toopher_api_key');
    $secret = get_option('toopher_api_secret');
    $baseUrl = get_option('toopher_api_url');
    $automatedLoginAllowed = get_option('toopher_allow_automated_login', true);

    $signed_url = ToopherWeb::auth_iframe_url($user->user_login, 'Log In', 100, $automatedLoginAllowed, $baseUrl, $key, $secret);

    $toopher_finish_authenticate_parameters = array(
        'pending_user_id' => $user->ID,
        'redirect_to' => $_POST['redirect_to']
    );

?>
<html>
    <body>
    <iframe id='toopher_iframe' toopher_postback='<?php echo wp_login_url() ?>' framework_post_args='<?php echo json_encode($toopher_finish_authenticate_parameters) ?>' toopher_req='<?php echo $signed_url ?>'></iframe>
        <script src='//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.2/jquery.min.js'> </script>
        <script src='//cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.3.1/jquery.cookie.min.js'> </script>
        <script>
            <?php include('toopher-web/toopher-web.js'); ?>;
        </script>
    </body>
</html>
<?php
}

?>
