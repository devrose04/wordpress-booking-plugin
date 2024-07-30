<?php

function guesty_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'my-new-theme'),
    ));
}
add_action('after_setup_theme', 'guesty_setup');

function guesty_scripts() {
    wp_enqueue_style('reservatopm-style', get_stylesheet_uri());

    wp_enqueue_style('reservatopm-header-style', get_template_directory_uri() . '/assets/css/header.css');
    wp_enqueue_style('reservatopm-main-style', get_template_directory_uri() . '/assets/css/main.css');
    wp_enqueue_style('reservatopm-quote-style', get_template_directory_uri() . '/assets/css/quote-form.css');
    wp_enqueue_style('plugin-style-handle', plugins_url('assets/style.css', 'guesty-integration'));

    // Add Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');

    // Add Date Range Picker
    wp_enqueue_style('daterangepicker-css', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css');
    wp_enqueue_script('moment-js', 'https://cdn.jsdelivr.net/npm/moment/moment.min.js', array(), null, true);
    wp_enqueue_script('daterangepicker-js', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', array('jquery', 'moment-js'), null, true);

    // Custom script to initialize date range picker
    wp_enqueue_script('reservation-date-range', get_template_directory_uri() . '/assets/js/date-range.js', array('jquery', 'daterangepicker-js'), null, true);
    wp_enqueue_script('reservation-guest-bedroom', get_template_directory_uri() . '/assets/js/guest.js');
}
add_action('wp_enqueue_scripts', 'guesty_scripts');

function enqueue_custom_payment_scripts() {
    // Enqueue the bundled custom JavaScript file
    wp_enqueue_script(
        'custom-payment-script',
        get_template_directory_uri() . '/assets/js/guesty-payment.bundle.js',
        array(), // Dependencies (add dependencies if needed)
        null, // Version number
        true // Load in footer
    );
}
add_action('wp_enqueue_scripts', 'enqueue_custom_payment_scripts');

// functions.php

function enqueue_guesty_scripts() {
    wp_enqueue_script(
        'guesty-tokenization-js',
        'https://pay.guesty.com/tokenization/v1/init.js',
        array(),
        null,
        true
    );
}
add_action('wp_enqueue_scripts', 'enqueue_guesty_scripts');

?>


