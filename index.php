<?php get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <!-- Begin the loop -->
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <h2><?php the_title(); ?></h2>
            <?php the_content(); ?>
        <?php endwhile; endif; ?>
        <!-- End the loop -->
    </main>
</div>

<?php get_footer(); ?>
