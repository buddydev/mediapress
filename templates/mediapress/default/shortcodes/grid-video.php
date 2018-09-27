<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 * Video list in shortcode grid view
 * You can override it in yourtheme/mediapress/default/shortcodes/grid-video.php
 *
 */
$query = mpp_shortcode_get_media_data( 'query' );
?>
<?php if ( $query->have_media() ) : ?>
	<div class="mpp-container mpp-shortcode-wrapper mpp-shortcode-media-list-wrapper">
		<div class="mpp-g mpp-item-list mpp-media-list mpp-shortcode-item-list mpp-shortcode-list-media mpp-shortcode-list-media-video ">

			<?php while ( $query->have_media() ) : $query->the_media(); ?>

				<div class="<?php mpp_media_class( 'mpp-shortcode-item mpp-shortcode-video-item ' . mpp_get_grid_column_class( mpp_shortcode_get_media_data( 'column' ) ) ); ?>">
					<?php do_action( 'mpp_before_media_shortcode_item' ); ?>

					<div class="mpp-item-meta mpp-media-meta mpp-media-shortcode-item-meta mpp-media-meta-top mpp-media-shortcode-item-meta-top">
						<?php do_action( 'mpp_media_shortcode_item_meta_top' ); ?>
					</div>

					<?php
					$args = array(
						'src'      => mpp_get_media_src(),
						'loop'     => false,
						'autoplay' => false,
						'poster'   => mpp_get_media_src( 'thumbnail' ),
						'width'    => 320,
						'height'   => 180,
					);

					//$ids = mpp_get_all_media_ids();
					//echo wp_playlist_shortcode( array( 'ids' => $ids));
					?>
					<div class='mpp-item-entry mpp-media-entry mpp-audio-entry'>

					</div>

					<div class="mpp-item-content mpp-video-content mpp-video-player">
						<?php if ( mpp_is_oembed_media( mpp_get_media_id() ) ) : ?>
							<?php echo mpp_get_oembed_content( mpp_get_media_id(), 'mid' ); ?>
						<?php else : ?>
							<?php echo wp_video_shortcode( $args ); ?>
						<?php endif; ?>
					</div>

					<a href="<?php mpp_media_permalink(); ?>"
						<?php mpp_media_html_attributes( array(
							'class'            => 'mpp-item-title mpp-media-title mpp-video-title',
							'data-mpp-context' => 'shortcode',
						) ); ?> >
						<?php mpp_media_title(); ?>
					</a>
					<?php if ( $show_creator ) : ?>
                        <div class="mpp-media-creator-link mpp-shortcode-media-creator-link">
							<?php echo $before_creator; ?><?php mpp_media_creator_link(); ?><?php echo $after_creator; ?>
                        </div>
					<?php endif; ?>

					<div
						class="mpp-type-icon"><?php do_action( 'mpp_type_icon', mpp_get_media_type(), mpp_get_media() ); ?></div>

					<div
						class="mpp-item-meta mpp-media-meta mpp-media-shortcode-item-meta mpp-media-meta-bottom mpp-media-shortcode-item-meta-bottom">
						<?php do_action( 'mpp_media_shortcode_item_meta' ); ?>
					</div>

					<?php do_action( 'mpp_after_media_shortcode_item' ); ?>

				</div>

			<?php endwhile; ?>
			<?php mpp_reset_media_data(); ?>
		</div>

		<?php if ( $show_pagination ) : ?>
			<div class="mpp-paginator">
				<?php echo $query->paginate( false ); ?>
			</div>
		<?php endif; ?>

	</div>

<?php endif; ?>
