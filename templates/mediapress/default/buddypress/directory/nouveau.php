<?php
/**
 * MediaPress directory template for BP Nouveau template pack.
 */
// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;
?>
<nav class="mpp-type-navs main-navs bp-navs dir-navs " role="navigation" >
	<ul class="component-navigation mpp-nav">
		<li id="mpp-all" class="selected" data-bp-scope="all" data-bp-object="mpp">
			<a href="<?php echo esc_url( get_permalink( buddypress()->pages->mediapress->id ) ); ?>">
				<?php printf( __( 'All Galleries <span class="count">%s</span>', 'mediapress' ), mpp_get_total_gallery_count() ) ?>
			</a>
		</li>
		<?php do_action( 'mpp_directory_types' ) ?>

	</ul><!-- .component-navigation -->
</nav><!-- end of nav -->

<div class="screen-content">

	<div class="subnav-filters filters no-ajax" id="subnav-filters">

		<div class="subnav-search clearfix">
			<div id="mpp-dir-search" class="dir-search mpp-search bp-search" data-bp-search="mpp">
				<form action="" method="get" class="bp-dir-search-form" id="dir-mpp-search-form" role="search">

					<label for="dir-mpp-search" class="bp-screen-reader-text"><?php _e( 'Search Galleries...', 'mediapress' );?></label>

					<input id="dir-mpp-search" name="mpp_search" type="search" placeholder="<?php echo  esc_attr( __( 'Search Galleries...', 'mediapress' ) );?>">

					<button type="submit" id="dir-mpp-search-submit" class="nouveau-search-submit" name="dir_mpp_search_submit">
						<span class="dashicons dashicons-search" aria-hidden="true"></span>
						<span id="button-text" class="bp-screen-reader-text"><?php _e( 'Search', 'mediapress' );?></span>
					</button>

				</form>
			</div><!-- #mpp-dir-search -->

		</div>

		<div id="comp-filters" class="component-filters clearfix">
			<div id="mpp-order-select" class="last filter">
				<label for="mpp-order-by" class="bp-screen-reader-text">
					<span><?php _e( 'Filter By:', 'mediapress' ) ?></span>
				</label>
				<div class="select-wrap">

						<select id="mpp-order-by" data-bp-filter="mpp">
							<option value=""><?php _e( 'All Galleries', 'mediapress' ) ?></option>

							<?php $active_types = mpp_get_active_types(); ?>

							<?php foreach( $active_types as $type => $type_object ):?>
								<option value="<?php echo esc_attr( $type );?>"><?php echo $type_object->get_label();?> </option>
							<?php endforeach;?>

							<?php do_action( 'mpp_gallery_directory_order_options' ) ?>
						</select>

					<span class="select-arrow" aria-hidden="true"></span>
				</div>
			</div>
		</div><!-- end of filter -->

	</div><!-- search & filters -->

	<div id="mpp-dir-list" class="mpp dir-list" data-bp-list="mpp">
		<?php mpp_get_template( 'gallery/loop-gallery.php' ); ?>
	</div><!-- #mpp-dir-list -->

</div>