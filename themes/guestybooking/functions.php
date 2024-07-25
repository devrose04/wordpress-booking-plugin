<?php
function my_new_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'my-new-theme'),
    ));
}
add_action('after_setup_theme', 'my_new_theme_setup');

function my_new_theme_scripts() {
    wp_enqueue_style('reservatopm-style', get_stylesheet_uri());
    wp_enqueue_style('reservatopm-main-style', get_template_directory_uri() . '/assets/css/main.css');
    wp_enqueue_style('reservatopm-quote-style', get_template_directory_uri() . '/assets/css/quote-form.css');
}
add_action('wp_enqueue_scripts', 'my_new_theme_scripts');


