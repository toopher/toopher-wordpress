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
            $userMeta = get_user_meta($uid, $key, true);
            if ($userMeta === ""){
                $userMeta = $toopherUserOptions[$key][1];
            }

            $toopherUserOptionVals[$key] = $userMeta;
        }
    }
}

function toopher_record_updated_settings_for_later_application($errors, $update, $user){
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
            update_user_meta((int)$user->ID, $key, $val);
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

    $pairedWithToopher = get_user_meta((int)$user->ID, 't2s_user_paired', true);
?>
<div class="wrap">
    <table class="form-table">
        <tr>
            <th><label for='toopher_pairing_status'><?php _e('Toopher Pairing Status') ?></label></th>
            <td><span id='toopher_pairing_status'><?php $pairedWithToopher ? _e('Paired') : _e('Not Paired') ?></span></td>
        </tr>
        <tr>
            <td></td>
            <td><div id='toopher_wordpress_user_options'></div></td>
        </tr>
    </table>
    <script>
var toopherWebApi = <?php include('toopher-web/toopher-web.js'); ?>;
var toopherUserOptions = <?php include('toopher-user-options.js'); ?>;

toopherUserOptions.init(
    toopherWebApi,
    'toopher_wordpress_user_options', 
    'toopher_pairing_status', 
    <?php echo($pairedWithToopher ? "'paired'" : "'unpaired'") ?>
);
    </script>
<?php
}

function toopher_edit_user_options_menu($user){
    $uid = (int)$user->ID;
    $pairedWithToopher = get_user_meta($uid, 't2s_user_paired', true);
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
