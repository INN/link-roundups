<?php get_header(); ?>

		<?php if ( have_posts() ) : ?>

			<header class="page-header lr-arch-header">
				<?php
					the_archive_title( '<h1 class="page-title lr-arch-page-title">', '</h1>' );
					the_archive_description( '<div class="taxonomy-description lr-arch-page-description">', '</div>' );
				?>
			</header><!-- .page-header -->
			
			<div class="lr-arch-wrapper">

			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<?php the_content(); ?>

			<?php endwhile; ?>

			<?php the_posts_navigation(); ?>

		<?php else : ?>

			<?php _e('No roundup links to display','link-roundups'); ?>

		<?php endif; ?>
		
			</div>

<?php get_footer();
