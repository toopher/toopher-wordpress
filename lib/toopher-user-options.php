<?php

add_action('show_user_profile', 'toopher_user_options_menu_container');
add_action('edit_user_profile', 'toopher_edit_user_options_menu_container');
add_filter('user_profile_update_errors', 'toopher_record_updated_settings_for_later_application', 20, 3);

$toopherUserOptions = array(
    "t2s_authenticate_login" => array("Logging In", '1'),
    "t2s_authenticate_profile_update" => array("Updating my User Profile", '1')
);
$toopherUserOptionVals = array();

$refreshToopherUserOptionsCalled = false;
function refresh_toopher_user_options($uid){
    global $toopherUserOptions;
    global $toopherUserOptionVals;
    global $refreshToopherUserOptionsCalled;
    if(!$refreshToopherUserOptionsCalled){
        $refreshToopherUserOptionsCalled = true;
        foreach($toopherUserOptions as $key => $val){
            $userMeta = get_user_option($key, $uid);
            if ($userMeta === false){
                // user has no setting for toopher options.  Set the default.
                $userMeta = $toopherUserOptions[$key][1];
                update_user_option($uid, $key, $userMeta);
            }

            $toopherUserOptionVals[$key] = $userMeta;
        }
    }
}

function toopher_record_updated_settings_for_later_application($errors, $update, $user){

    // only want to run if we're updating an existing user, not adding a new one
    if (!$update){
        return;
    }
    if(isset($_POST['toopher_sig'])){
        // ignoring toopher authentication postback
        return;
    }
    global $toopherUserOptions;
    global $toopherUserOptionVals;
    $updatedToopherUserOptionVals = array();
    refresh_toopher_user_options($user->ID);
    foreach ($toopherUserOptions as $key => $val){
        $newVal = '0';
        if(isset($_REQUEST[$key])){
            $newVal = '1';
        }
        if($toopherUserOptionVals[$key] !== $newVal){
            $updatedToopherUserOptionVals[$key] = $newVal;
        }
    }
    set_transient($user->ID . '_pending_toopher_profile_settings', $updatedToopherUserOptionVals, 2 * MINUTE_IN_SECONDS);
}

function toopher_apply_updated_user_settings($user){
    $updatedToopherUserOptionVals = get_transient($user->ID . '_pending_toopher_profile_settings');
    delete_transient($user->ID . '_pending_toopher_profile_settings');
    if ($updatedToopherUserOptionVals){
        foreach ($updatedToopherUserOptionVals as $key => $val){
            update_user_option((int)$user->ID, $key, $val);
            $toopherUserOptionVals[$key] = $val;
        }
    }
}

function toopher_user_options_menu_container($user){

    refresh_toopher_user_options($user->ID);
?>
<div class="wrap">
    <h3>Toopher Device Pairing</h3>
<?php
    toopher_user_options_menu($user);
    echo "<h3>Toopher User Authentication Options</h3>";
    toopher_edit_user_options_menu($user);
?>
</div>
<?php
}
function toopher_edit_user_options_menu_container($user){
?>
<div class="wrap">
    <h3>Toopher User Authentication Options</h3>
<?php
    toopher_edit_user_options_menu($user);
?>
</div>
<?php
}
function toopher_user_options_menu($user){
    $key = get_option('toopher_api_key');
    $secret = get_option('toopher_api_secret');
    $baseUrl = get_option('toopher_api_url');
    $toopherPairingIframeSrc = ToopherWeb::pair_iframe_url($user->data->user_login, 60, $baseUrl, $key, $secret);


?>
<div class="wrap" style="width: 100%; height:500px;">
  <iframe id="toopher-iframe" style="height:100%; width:100%; border: 1px dashed red; padding: 10px;" src='<?php echo($toopherPairingIframeSrc); ?>' />
</div>
<?php
}

function toopher_edit_user_options_menu($user){
    $uid = (int)$user->ID;
    $pairedWithToopher = get_user_option('t2s_user_paired', $uid);
    global $toopherUserOptions;
    global $toopherUserOptionVals;
    $headerText = IS_PROFILE_PAGE ? 'my account' : 'this user';
    refresh_toopher_user_options($uid);

?>
    <table class="form-table">
        <tbody>
        <tr>
        <th>Require Toopher Authentication for <?php echo $headerText ?> when:</th>
            <td>
<?php   foreach($toopherUserOptions as $key => $val){
            echo "<label for='" . $key . "'>";
            $checkedText = $toopherUserOptionVals[$key] ? "checked='checked'" : "";
            echo "<input type='checkbox' name='" . $key . "' id='" . $key . "' " . $checkedText . " />";
            echo "  " . $val[0] . "</label>";
            echo "<br />";
        }
?>
            </td>
        </tr>
        </tbody>
    </table>
<?php
}

?>
