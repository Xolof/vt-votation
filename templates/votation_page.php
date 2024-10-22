<?php
/** Template Name: Votation page */
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
          <?php while (have_posts()): ?>
            <?php the_field('explanation'); ?>
            <?php the_post(); ?>
            <?php
            $args = array(
              'post_type' => 'olamplig-bok',
              'post_status' => array('any')
            );
            $the_query = new WP_Query($args);
            ?>
              <?php if ($the_query->have_posts()): ?>
                <?php while ($the_query->have_posts()):
                  $the_query->the_post(); ?>
                  <h2><?php the_field('titel'); ?></h2>
                  <img 
                    style="height: 200px;"
                    src="<?php the_field('bild'); ?>"
                  />
                  <p><?php the_field('beskrivning'); ?></p>
                  <button
                    class="votationButton <?= sanitize_title(get_field('titel')) ?>"
                  >Lägg till i min lista</button>
                  <br>
                  <br>
                  <br>
                <?php endwhile; ?>
              <?php endif; ?>
            <?php
            wp_reset_query();  // Restore global post data stomped by the_post().
            ?>
          <?php endwhile; ?>
          <?php the_content(); ?>
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
