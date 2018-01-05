<?php
/**
 * Group Gallery extensions.
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * MediaPress Group extension.
 */
if ( class_exists( 'BP_Group_Extension' ) ) :
	/**
	 * Group extension for MediaPress to BuddyPress Group integration.
	 */
	class MPP_Group_Gallery_Extension extends BP_Group_Extension {

		/**
		 * MPP_Group_Gallery_Extension constructor.
		 */
		public function __construct() {
			$has_access = true;
			if ( bp_is_group() && groups_get_current_group() ) {
				$has_access = groups_get_current_group()->user_has_access;
			}

			$args = array(
				'slug'              => MPP_GALLERY_SLUG,
				'name'              => __( 'Gallery', 'mediapress' ),
				'visibility'        => 'public',
				'nav_item_position' => 80,
				'nav_item_name'     => __( 'Gallery', 'mediapress' ),
				'enable_nav_item'   => mpp_group_is_gallery_enabled() && $has_access,// true by default.
				//'display_hook' => 'groups_custom_group_boxes', // meta box hook.
				//'template_file'=> 'groups/single/plugins.php',.
				'screens'           => array(
					'create' => array(
						'enabled' => false,
					),
					'edit'   => array(
						'enabled' => false,
					),
					'admin'  => array(
						//'metabox_context' => normal,
						//'metabox_priority' => '',
						'enabled' => false,
						//'name'	=> 'Gallery Settings',
						//'slug'	=> MPP_GALLERY_SLUG,
						//'screen_callback' => '',
						//'screen_save_callback' => ''
					),
				),
			);
			parent::init( $args );


		}

		/**
		 * Render tab.
		 *
		 * @param int $group_id group id.
		 */
		public function display( $group_id = null ) {

			mpp_get_component_template_loader( 'groups' )->load_template();
		}

		/**
		 * The settings_screen() is the catch-all method for displaying the content
		 * of the edit, create, and Dashboard admin panels
		 */
		public function settings_screen( $group_id = null ) {

		}

		/**
		 * The settings_screen_save() contains the catch-all logic for saving
		 * settings from the edit, create, and Dashboard admin panels.
		 */
		public function settings_screen_save( $group_id = null ) {

		}
	}

	bp_register_group_extension( 'MPP_Group_Gallery_Extension' );

endif;

/**
 * Display form for enabling/disabling MediaPress
 */
function mppp_group_enable_form() {

	if ( ! mpp_is_active_component( 'groups' ) ) {
		return;// do not show if gallery is not enabled for group component.
	}
	?>
	<div class="checkbox mpp-group-gallery-enable">
		<label for="mpp-enable-gallery">
			<input type="checkbox" name="mpp-enable-gallery" id="mpp-enable-gallery" value="yes" <?php echo checked( 1, mpp_group_is_gallery_enabled() ); ?>/>
			<?php _e( 'Enable Gallery', 'mediapress' ) ?>
		</label>
	</div>
	<?php
}

add_action( 'bp_before_group_settings_admin', 'mppp_group_enable_form' );
add_action( 'bp_before_group_settings_creation_step', 'mppp_group_enable_form' );

/**
 * Save group Preference
 *
 * @param int $group_id group id.
 */
function mpp_group_save_preference( $group_id ) {

	$enabled = isset( $_POST['mpp-enable-gallery'] ) ? $_POST['mpp-enable-gallery'] : 'no';

	if ( $enabled != 'yes' && $enabled != 'no' ) {// invalid value.
		$enabled = 'no';// set it to no.
	}

	mpp_group_set_gallery_state( $group_id, $enabled );
}

add_action( 'groups_group_settings_edited', 'mpp_group_save_preference' );
add_action( 'groups_create_group', 'mpp_group_save_preference' );
add_action( 'groups_update_group', 'mpp_group_save_preference' );

