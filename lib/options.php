<?php

add_action('admin_menu', 'toopher_plugin_admin_menu');
add_action('show_user_profile', 'toopher_user_options_menu');
add_action('edit_user_profile', 'toopher_edit_user_options_menu');

function toopher_plugin_admin_menu() {
    add_options_page('Toopher Plugin Options', 'Toopher Authentication', 'manage_options', TOOPHER_PLUGIN_ID, 'toopher_plugin_admin_options');
}
function toopher_plugin_admin_options(){
    if (!current_user_can('manage_options')) {
        wp_die( __('You do not have sufficient permissions to access this page.'));
    }
    echo '<div class="wrap">';
    echo '<h2>Toopher Authentication Admin Settings</h2>';
    echo '</div>';
}

function toopher_user_options_menu(){
    $user = wp_get_current_user();
    $pairedWithToopher = get_user_meta((int)$user->ID, 't2s_user_paired', true);
?>
<div class="wrap">
    <h3>Toopher User Options</h3>
    <table>
        <tr>
            <th><label for='toopher-pairing-status'><?php _e('Toopher Pairing Status') ?></label></th>
            <td><span id='toopher-pairing-status'><?php $pairedWithToopher ? _e('Paired') : _e('Not Paired') ?></span></td>
        </tr>
        <tr>
            <td></td>
            <td><div id='toopher-wordpress-user-options'></div></td>
        </tr>
    </table>
    <script>
var toopherWebApi = <?php include('toopher-web/toopher-web.js'); ?>;
var toopherUserOptions = <?php include('toopher-user-options.js'); ?>;
debugger;
toopherUserOptions.init(
    toopherWebApi,
    'toopher-wordpress-user-options', 
    'toopher-pairing-status', 
    <?php echo($pairedWithToopher ? "'paired'" : "'unpaired'") ?>
);
    </script>
<div>
<?php
}

?>
