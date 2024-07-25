<?php
/*
Plugin Name: Guesty Integration
Description: Integrates Guesty with WordPress.
Version: 1.0
Author: Yurii
*/

// Basic security check
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

// Include Composer autoloader
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

// Include the settings page
// if ( is_admin() ) {
//     require_once plugin_dir_path( __FILE__ ) . 'admin/settings-page.php';
// }
require_once plugin_dir_path( __FILE__ ) . 'admin/settings-page.php';

// Include the Guesty API class
require_once plugin_dir_path( __FILE__ ) . 'includes/class-calendar.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-guesty-api.php';

// Include the custom CSS
function myplugin_enqueue_admin_styles() {
    // Define the path to your CSS file
    $css_calendar_path = plugin_dir_url( __FILE__ ) . 'assets/style-calendar.css';
    $css_main_path = plugin_dir_url( __FILE__ ) . 'assets/style.css';

    // Enqueue the CSS file
    wp_enqueue_style( 'myplugin-admin-styles', $css_main_path, array(), '1.0.0', 'all' );
    wp_enqueue_style( 'myplugin-admin-styles', $css_calendar_path, array(), '1.0.0', 'all' );
}
add_action('admin_enqueue_scripts', 'myplugin_enqueue_admin_styles');



