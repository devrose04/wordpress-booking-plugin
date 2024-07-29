<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <header>
        <nav class="main-banner">
            <!-- <?php wp_nav_menu(array('theme_location' => 'primary')); ?> -->
            <div class="container main-nav">
                <div class="nav-item1">
                    <p id="mark1">ALTERNATIVE</p>
                    <p id="mark2">VILLA</p>
                </div>
                <div class="nav-item2">
                    <p>VILLAS</p>
                    <p>BE INSPIRED</p>
                    <p>ENQUIRE</p>
                </div>
            </div>
            <div class="contain-text">
                <h1>Our Villas</h1>
                <p>Welcome to Villa Amedee, your serene sanctuary in the heart of Penestanan Village, Ubud. Surrounded by the enchanting beauty of Bali, our charming 2-bedroom Balinese-style villa is an ideal retreat for couples, families, or friends. Its convenient location offers easy access to this world-renowned area's vibrant cultural, culinary, and wellness treasures. Discover the magical essence of Bali with us, where relaxation, personal transformation, and adventure await.</p>
            </div>
        </nav>
        <?php
        // Output the quote form using the shortcode
        echo do_shortcode('[guesty_booking_quote_form]');
        ?>
    </header>
</body>

</html>