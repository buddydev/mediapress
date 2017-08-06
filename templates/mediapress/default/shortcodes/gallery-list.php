<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode Entry.
 *
 * mediapress/shortcodes/gallery-entry.php
 *
 * Single gallery entry for mpp-gallery shortcode
 */
//$query = mpp_shortcode_get_gallery_data( 'gallery_list_query' );
/**
 * @see mpp_shortcode_list_gallery() for the meaning of $query.
 */
if ( empty( $query ) ) {
	return;
}
?>
<?php if ( $query->have_galleries() ) : ?>
	<div class="mpp-container mpp-shortcode-wrapper mpp-shortcode-gallery-wrapper">
		<div class="mpp-g mpp-item-list mpp-gallery-list mpp-shortcode-item-list mpp-shortcode-list-gallery">

			<?php while ( $query->have_galleries() ) : $query->the_gallery(); ?>

				<div class="<?php mpp_gallery_class( mpp_get_grid_column_class( $shortcode_column ) ); ?>" id="mpp-gallery-<?php mpp_gallery_id(); ?>">

					<?php do_action( 'mpp_before_gallery_shortcode_entry' ); ?>

					<div class="mpp-item-meta mpp-gallery-meta mpp-gallery-shortcode-item-meta mpp-gallery-meta-top mpp-gallery-shortcode-item-meta-top">
						<?php do_action( 'mpp_gallery_shortcode_item_meta_top' ); ?>
					</div>

					<div class="mpp-item-entry mpp-gallery-entry">
						<a href="<?php mpp_gallery_permalink(); ?>" <?php mpp_gallery_html_attributes( array(
							'class'            => 'mpp-item-thumbnail mpp-gallery-cover',
							'data-mpp-context' => 'shortcode',
						) ); ?>>

							<img src="<?php mpp_gallery_cover_src( 'thumbnail' ); ?>" alt="<?php echo esc_attr( mpp_get_gallery_title() ); ?>"/>
						</a>
					</div>

					<?php do_action( 'mpp_before_gallery_title' ); ?>

					<a href="<?php mpp_gallery_permalink(); ?>" <?php mpp_gallery_html_attributes( array(
						'class'            => 'mpp-item-title mpp-gallery-title',
						'data-mpp-context' => 'shortcode',
					) );
					?> >
						<?php mpp_gallery_title(); ?>
					</a>

					<?php if ( $show_creator ) : ?>
                        <div class="mpp-gallery-creator-link mpp-shortcode-gallery-creator-link">
							<?php echo $before_creator; ?><?php mpp_gallery_creator_link(); ?><?php echo $after_creator; ?>
                        </div>
					<?php endif; ?>

					<?php do_action( 'mpp_before_gallery_type_icon' ); ?>

					<div class="mpp-type-icon"><?php do_action( 'mpp_type_icon', mpp_get_gallery_type(), mpp_get_gallery() ); ?></div>

					<div class="mpp-item-meta mpp-gallery-meta mpp-gallery-shortcode-item-meta mpp-gallery-meta-bottom mpp-gallery-shortcode-item-meta-bottom">
						<?php do_action( 'mpp_gallery_shortcode_item_meta' ); ?>
					</div>


					<?php do_action( 'mpp_after_gallery_shortcode_entry' ); ?>

				</div>
			<?php endwhile; ?>

			<?php mpp_reset_gallery_data(); ?>
		</div>

		<?php if ( $show_pagination ) : ?>
			<div class="mpp-paginator">
				<?php echo $query->paginate( false ); ?>
			</div>
		<?php endif; ?>

	</div>
<?php endif; ?>
