<?php

function guesty_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'my-new-theme'),
    ));
}
add_action('after_setup_theme', 'guesty_setup');

function guesty_scripts()
{
    wp_enqueue_style('reservatopm-style', get_stylesheet_uri());

    wp_enqueue_style('reservatopm-header-style', home_url() . '/wp-content/themes/alternate-villa/assets/css/header.css');
    wp_enqueue_style('reservatopm-main-style', home_url() . '/wp-content/themes/alternate-villa/assets/css/main.css');
    wp_enqueue_style('reservatopm-quote-style', home_url() . '/wp-content/themes/alternate-villa/assets/css/quote-form.css');
    wp_enqueue_style('plugin-style-handle', plugins_url('assets/style.css', 'guesty-integration'));

    // Add Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
    
    // Custom script to initialize date range picker
    wp_enqueue_script('jquery-standard', 'https://cdn.jsdelivr.net/jquery/latest/jquery.min.js');
    wp_enqueue_script('moment-js', 'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js');
    wp_enqueue_script('date-picker', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js');
    wp_enqueue_script('reservation-date-range', home_url() . '/wp-content/themes/alternate-villa/assets/js/date-range.js');
    wp_enqueue_script('reservation-guest-bedroom', home_url() . '/wp-content/themes/alternate-villa/assets/js/guest.js');
}
add_action('wp_enqueue_scripts', 'guesty_scripts');
