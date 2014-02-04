<?php

add_filter('authenticate', 'toopher_begin_authenticate_login', 100, 1);
add_filter('authenticate', 'toopher_finish_authenticate_login', 0, 1);

/**
 * run last in the authenticate chain.  If user has passed previous auth, see if they
 * need to be toopher-authenticated and handle it.  Else is a no-op.
 **/

function toopher_begin_authenticate_login($user){
    error_log('toopher_begin_authenticate_login');
    if (is_a($user, 'WP_User')){
          if(isset($_POST['toopher_authentication_successful']) && ($_POST['toopher_authentication_successful'] === 'true')){
              return $user;
          } else {
              error_log('user should be toopher-authenticated');
              if(defined('XMLRPC_REQUEST') && XMLRPC_REQUEST){
                  setcookie('wp-xmlrpc-t2s-terminal-id', 'test');
                  require_once('toopher_api.php');
                  $api = new ToopherAPI(get_option('toopher_api_key'), get_option('toopher_api_secret'), get_option('toopher_api_url'));
                  $startTime = time();
                  $authStatus = $api->authenticate(get_user_option('t2s_pairing_id', (int)$user->ID), '', 'XML-RPC Access', array('automation_allowed' => 'false'));
                  while($authStatus['pending']){
                      if ((time() - $startTime) > 60) {
                          error_log('Timeout while waiting for XMLRPC authentication from Toopher!');
                          $user = new WP_Error('Toopher Authentication Failure', __('Timeout waiting for response to Toopher authentication request'));
                          return $user;
                      }
                      $authStatus = $api->getAuthenticationStatus($authStatus['id']);
                      sleep(1);
                  }
                  if(!$authStatus['granted']){
                      $user = new WP_Error('Toopher Authentication Failure', __('Unable to authenticate user through Toopher API'));
                  }
              } else {
                  error_log('beginning toopher interactive login');
                  toopher_login_pending($user);
                  exit();
              }
          }
    } else {
        error_log('not a WP_User');
    }

    return $user;
}

function toopher_finish_authenticate_login($user){
    // make sure someone isn't trying to circumvent toopher-auth by submitting the authentication success flag through the browser
    if(isset($_POST['toopher_authentication_successful'])){
        unset($_POST['toopher_authentication_successful']);
    }

    if(isset($_POST['toopher_sig'])){
        $pending_user_id = $_POST['pending_user_id'];
        $redirect_to = $_POST['redirect_to'];
        unset($_POST['pending_user_id']);
        unset($_POST['redirect_to']);
        $secret = get_option('toopher_api_secret');
        foreach(array('terminal_name', 'reason') as $toopher_key){
            if (array_key_exists($toopher_key, $_POST)) {
                $_POST[$toopher_key] = strip_wp_magic_quotes($_POST[$toopher_key]);
            }
        }

        $pending_session_token = get_transient($pending_user_id . '_t2s_authentication_session_token');
        delete_transient($pending_user_id . '_t2s_authentication_session_token');
        if(($pending_session_token === $_POST['session_token']) && ToopherWeb::validate($secret, $_POST, 100)){
            error_log('toopher signature validates');
            $auth_granted = false;
            if (array_key_exists('error_code', $_POST)){
                $error_code = $_POST['error_code'];
                $error_message = $_POST['error_message'];
                error_log('Received error response ' . $error_code . ' from Toopher API: ' . $error_message);

                # three specific errors will be allowed to fail open, corresponding to allowing users
                # to opt-in to Toopher (instead of requiring all users to participate)
                if ($error_code === '707') { # pairing deactivated - allow in
                    $auth_granted = true;
                } elseif ($error_code === '704') { # user opt-out - allow in
                    $auth_granted = true;
                } elseif ($error_code === '705') { # unknown user - allow in
                    $auth_granted = true;
                }
            } else {
                $auth_granted = ($_POST['pending'] === 'false') && ($_POST['granted'] === 'true');
            }
            if($auth_granted){
                error_log('auth granted');
                $user = get_user_by('id', $pending_user_id);
                $_POST['redirect_to'] = $redirect_to;
            }
            $_POST['toopher_authentication_successful'] = $auth_granted ? 'true' : 'false';
        } else {
            error_log('toopher signature does not validate!');
            $user = new WP_Error('Toopher Authentication Failure', __('Toopher API Signature did not match expected value'));
        }
    }
    return $user;
}

function toopher_login_pending($user){
    $key = get_option('toopher_api_key');
    $secret = get_option('toopher_api_secret');
    $baseUrl = get_option('toopher_api_url');
    $automatedLoginAllowed = get_option('toopher_allow_automated_login', 1);
    $session_token = wp_generate_password(12, false);
    set_transient($user->ID . '_t2s_authentication_session_token', $session_token, 20 * MINUTE_IN_SECONDS);
    $signed_url = ToopherWeb::auth_iframe_url($user->user_login, 'Log In', 100, $automatedLoginAllowed, $baseUrl, $key, $secret, $session_token);

    $toopher_finish_authenticate_parameters = array(
        'pending_user_id' => $user->ID,
        'redirect_to' => $_POST['redirect_to']
    );
    wp_enqueue_script('jquery');
?>
<html>
    <head>
        <?php wp_head(); ?>
    </head>
    <body>
        <div style="width:100%; text-align:center; padding:50px;">
        <iframe id='toopher_iframe' style="display: inline-block; height:100%; width:100%; border: 1 px dashed red; padding: 10px;"  toopher_postback='<?php echo wp_login_url() ?>' framework_post_args='<?php echo json_encode($toopher_finish_authenticate_parameters) ?>' toopher_req='<?php echo $signed_url ?>'></iframe>
        </div>
        <script>
<?php  include('jquery.cookie.min.js') ?>
<?php  include('toopher-web/toopher-web.js'); ?>

    toopher.init('#toopher_iframe');
        </script>
<?php get_footer(); wp_footer(); ?>
    </body>
</html>
<?php
}

?>
