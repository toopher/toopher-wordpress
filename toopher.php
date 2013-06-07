<?php
/* 
Plugin Name: Toopher Two-Factor Authentication
 */

define ('TOOPHER_PLUGIN_ID', 'ToopherForWordpress');

function print_filters_for( $hook = '' ) {
    global $wp_filter;
    if( empty( $hook ) || !isset( $wp_filter[$hook] ) )
        return;

    print '<pre>';
    print_r( $wp_filter[$hook] );
    print '</pre>';
}

function enqueue_jquery_cookie(){
    wp_enqueue_script('jquery-cookie', '//cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.3.1/jquery.cookie.min.js');
}
add_action('wp_enqueue_scripts', 'enqueue_jquery_cookie');
add_action('admin_enqueue_scripts', 'enqueue_jquery_cookie');

require('lib/ajax-endpoints.php');
require('lib/toopher-authenticate-login.php');
require('lib/toopher-user-options.php');
require('lib/toopher-settings.php');

if(!function_exists('_log')){
  function _log( $message ) {
    if( WP_DEBUG === true ){
      if( is_array( $message ) || is_object( $message ) ){
        error_log('dumping an object...');
        echo('dumping an object');
        error_log(print_r( $message , true));
        echo(var_export( $message , true));
      } else {
        error_log( $message );
      }
    }
  }
}

if(!class_exists('ToopherWordpress') && !isset($toopherWordpress)) :
    class ToopherWordpress
    {
        public function __construct()
        {
        }
    }
    $toopherWordpress = new ToopherWordpress();


endif;


?>
