
<?php

/** Template Name: Liberdev Example Page */

/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package GeneratePress
 */
if (!defined('ABSPATH')) {
  exit;  // Exit if accessed directly.
}

get_header();
?>

	<div <?php generate_do_attr('content'); ?>>
		<main <?php generate_do_attr('main'); ?>>
      <?php

        /**
         * generate_before_main_content hook.
         *
         * @since 0.1
         */
        do_action('generate_before_main_content');

      ?>
        <article>
          <div class="inside-article">
            <h1><?php the_title(); ?></h1>
            <h2>Header from the template PHP file</h2>
            <p>Some text from the template PHP file.</p>
            <?php while (have_posts()): ?>
              <?php the_post(); ?>
              <p>
                <?php
                the_content();
                ?>
              </p>
            <?php endwhile; // end of the loop. ?>
          </div>
        </article>
      <?php

        /**
         * generate_after_main_content hook.
         *
         * @since 0.1
         */
        do_action('generate_after_main_content');
      ?>
		</main>
	</div>

	<?php

    /**
     * generate_after_primary_content_area hook.
     *
     * @since 2.0
     */
    do_action('generate_after_primary_content_area');
    generate_construct_sidebars();

    get_footer();
