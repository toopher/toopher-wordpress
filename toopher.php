<?php
/* 
Plugin Name: Toopher Two-Factor Authentication
 */

define ('TOOPHER_PLUGIN_ID', 'ToopherForWordpress');

require('lib/ajax-endpoints.php');
require('lib/options.php');
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
