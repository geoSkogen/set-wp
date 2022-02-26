<?php
/*
Plugin Name:  Set
Description:  The Game of Set
Version:      0
Author:       geoskogen
Author URI:   https://joseph-scoggins.com
Text Domain:  set
*/

defined( 'ABSPATH' ) or die( 'We make the path by walking.' );

if (is_admin()) {

  if ( !class_exists( 'Set_Admin' ) ) {
     include_once 'classes/set_admin.php';
  }

  $admin= new Set_Admin(
    ['main'],
    ['main']
  );

} else {
  // frontend resources
  if ( !class_exists( 'Set_Templater' ) ) {
     include_once 'classes/set_templater.php';
  }

  if ( !class_exists( 'Set_Router' ) ) {
     include_once 'classes/set_router.php';
  }
  // inject the subdomain of your app here:
  $router = new Set_Router('set');

  // add names of main css and js files
  $frontend = new Set_Templater(
    $router,
    ['main'],
    ['main'],
    'set.png',
    'child-style'
  );

  add_action( 'wp_head', [$router,'favicon_tag'], 2, null );

}

?>
