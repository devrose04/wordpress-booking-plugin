<?php
/* Template Name: Reservation Quotes */
get_header(); ?>

<main>
    <section id="reservation-quotes">
        <h1><?php _e('Reservation Quotes', 'my-new-theme'); ?></h1>
        <form id="reservation-form">
            <label for="name"><?php _e('Name:', 'my-new-theme'); ?></label>
            <input type="text" id="name" name="name" required>

            <label for="email"><?php _e('Email:', 'my-new-theme'); ?></label>
            <input type="email" id="email" name="email" required>

            <label for="date"><?php _e('Date:', 'my-new-theme'); ?></label>
            <input type="date" id="date" name="date" required>

            <label for="message"><?php _e('Message:', 'my-new-theme'); ?></label>
            <textarea id="message" name="message" required></textarea>

            <button type="submit"><?php _e('Submit', 'my-new-theme'); ?></button>
        </form>
    </section>
</main>

<?php get_footer(); ?>
