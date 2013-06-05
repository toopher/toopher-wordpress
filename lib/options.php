<?php
function enqueue_jquery_cookie(){
    wp_enqueue_script('jquery-cookie', '//cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.3.1/jquery.cookie.min.js');
}
add_action('admin_enqueue_scripts', 'enqueue_jquery_cookie');
add_action('show_user_profile', 'toopher_user_options_menu');
add_action('edit_user_profile', 'toopher_edit_user_options_menu');

function toopher_user_options_menu(){
    $user = wp_get_current_user();
    $pairedWithToopher = get_user_meta((int)$user->ID, 't2s_user_paired', true);
?>
<div class="wrap">
    <h3>Toopher User Options</h3>
    <table>
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
<div>
<?php
}

?>
