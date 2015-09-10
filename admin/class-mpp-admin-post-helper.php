<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

/**
 * Handles gallery post type screen modification
 * 
 */
class MPP_Admin_Post_Helper {

	private static $instance;
	
	private function __construct () {

		add_action( 'admin_init', array( $this, 'init' ) );

		add_action( 'admin_init', array( $this, 'remove_upload_button' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'load_js' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'load_css' ) );
		
		add_action( 'save_post_' . mpp_get_gallery_post_type(), array( $this, 'update_gallery_details' ), 1, 3 );
	}

	/**
	 * 
	 * @return MPP_Admin_Post_Helper
	 */
	public static function get_instance () {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function is_gallery_type () {

		$screen = get_current_screen();

		if ( mpp_get_gallery_post_type() === $screen->post_type ) {
			return true;
		}

		return false;
	}

	public function is_gallery_edit () {
		
//		if( get_current_screen()->id == mpp_get_gallery_post_type() ) {
//			return true;
//		}
//		return false;
		
		$post_id = isset( $_GET['post'] ) ? $_GET['post'] : 0;

		if ( empty( $post_id ) ) {
			return false;
		}

		$post = get_post( $post_id );

		if ( mpp_get_gallery_post_type() == $post->post_type ) {
			return true;
		}

		return false;
	}

	private function get_component_id( $post_id ) {
		//if it is not gallery edit page, let us not worry
		if( ! $this->is_gallery_edit() ) {
			return mpp_get_current_component_id();
		}
		//we are on edit page, 
		//it can be either add new or edit gallery
		//we do not want to modify the component_id(associated component id) 
		
		$component_id = mpp_get_gallery_meta( $post_id, '_mpp_component_id', true );
		
		if( ! $component_id ) {
			$component_id = get_current_user_id();//
		}
		
		return $component_id;
		
	}
	public function init () {
		//we need to take these actions only on gallery post type
		//or should we do it at add_metboxes
		$pages = array( 'post.php', 'post-new.php' );

		foreach ( $pages as $page ) {
			add_action( "load-{$page}", array( $this, 'add_remove_metaboxes' ) );
			add_action( "admin_head-{$page}", array( $this, 'generate_css' ) );
		}
	}

	public function add_remove_metaboxes () {
		//remove metaboxes
		// add_action( 'add_meta_boxes', array( $this, 'remove_metaboxes' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );

		//add upload meta box
		add_action( 'add_meta_boxes', array( $this, 'add_upload_metaboxes' ) );
	}

	//make it clutter free
	public function remove_metaboxes () {

		$gallery_post_type = mpp_get_gallery_post_type();
		// remove_meta_box( 'tagsdiv-keywords', 'ticket', 'side' );
		remove_meta_box( 'gallery-typediv', $gallery_post_type, 'side' );
		remove_meta_box( 'gallery-componentdiv', $gallery_post_type, 'side' );
		remove_meta_box( 'gallery-statusdiv', $gallery_post_type, 'side' );
	}

	//add meta boxes
	public function add_metaboxes () {

		$taxonomies = mpp_get_all_taxonomies_info();


		//foreach( $taxonomies as $taxonomy => $info )
		$this->add_metabox( $taxonomies );
	}

	public function add_metabox ( $taxonomies ) {

		add_meta_box(
				'gallery-meta-advance', // Unique ID
				_x( 'Gallery Info', 'Gallery Meta box title', 'mediapress' ), // Title
				array( $this, 'generate_meta_box' ), // Callback function
				mpp_get_gallery_post_type(), // Admin 
				'side', // Context
				'default' // Priority
		);
	}

	public function add_upload_metaboxes () {

		add_meta_box(
				'gallery-meta-upload-advance', // Unique ID
				_x( 'Upload Media', 'Upload Media Box Title', 'mediapress' ), // Title
				array( $this, 'generate_upload_meta_box' ), // Callback function
				mpp_get_gallery_post_type(), // Admin 
				'advanced', // Context
				'default' // Priority
		);
	}

	//generate meta boxes

	public function generate_meta_box ( $post ) {

		$col = 0;
		$row = 0;

		$taxonomies = mpp_get_all_taxonomies_info();
		?>
		<div id="mpp-taxonomy-metabox" class="categorydiv mpp-taxonomy-list">
			<ul>
				<?php foreach ( $taxonomies as $taxonomy => $info ): ?>
					<li class="mpp-taxonomy mpp-taxonomy-<?php echo $taxonomy; ?>">
						<?php
							$name = 'mpp-gallery-' . str_replace( 'mpp-', '', $taxonomy );
							// echo "<input type='hidden' name='{$name}[]' value='0' />"; // Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.

							$tax = get_taxonomy( $taxonomy );
							//tax_input[milestone][]

							$selected = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );
							$selected = array_pop( $selected );
						?>

							<label><?php echo str_replace( 'Gallery', '', $tax->labels->singular_name ); ?></label> 
						<?php
							if ( $taxonomy == mpp_get_type_taxname() ) {

								//in case of type taxonomy, we need to show the details and not allow editing
								if ( ! $selected ) {
									$selected = 'N/A';
								} else {

									$type = mpp_get_type_object( $selected );

									if ( $type ) {
										$selected = $type->label;
									}
								}

								echo "<strong>{$selected}</strong>";
								echo "</li>";

								continue;
							}
						?>
						<?php wp_dropdown_categories( array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'name' => $name, 'id' => 'mpp-tax-' . $taxonomy, 'selected' => $selected, 'show_option_all' => sprintf( 'Choose %s', $tax->labels->singular_name ) ) );
						?>
					</li>

					<?php endforeach; ?>

			</ul> 
			<input type='hidden' name="mpp-gallery-component-id" value="<?php echo $this->get_component_id( $post->ID );?>" />
		</div>    




	<?php
	}

	public function generate_upload_meta_box ( $post ) { ?>

		<!-- append uploaded media here -->
		<div id="mpp-gallery-media-admin-list" class="mpp-uploading-media-list">
			<ul> 
				<?php
					$gallery_id = $post->ID;

					$mppq = new MPP_Media_Query( array( 'gallery_id' => $gallery_id, 'per_page' => -1, 'nopaging' => true ) );
				?>	
				<?php while ( $mppq->have_media() ): $mppq->the_media(); ?>
				
					<li id="mpp-uploaded-media-item-<?php mpp_media_id(); ?>" class="<?php mpp_media_class( 'mpp-uploaded-media-item' ); ?>">
						<img src="<?php mpp_media_src( 'thumbnail' ); ?>">
					</li>
				<?php endwhile; ?>
				<?php mpp_reset_media_data(); ?>
			</ul>
		</div>
		<!-- drop files here for uploading -->
		<div id="mpp-gallery-admin-dropzone" class="mpp-dropzone">
			<button id="mpp-add-gallery-admin-media">Add media</button>
		</div>
		<!-- show any feedback here -->
		<div id="mpp-gallery-upload-admin-feedback" class="mpp-feedback">
			<ul> </ul>
		</div>


	<?php
	}

	public function generate_css () {
		global $post_type;
		$gallery_post_type = mpp_get_gallery_post_type();
		
		if ( ! ( isset( $_GET['post_type'] ) && $_GET['post_type'] == $gallery_post_type || $post_type == $gallery_post_type ) )
			return;
		?>
		
		<style type='text/css'>
			#mpp-taxonomy-metabox label{
				display: inline-block;
				margin-top: 10px;
				text-align: center;
				width: 28%;
				font-weight: bold;
			}
			#mpp-taxonomy-metabox strong{
				display: inline-block;
				margin-top: 10px;
				text-align: center;
				vertical-align: middle;

				font-weight: bold;
			}

		</style>
		<?php
	}

	/**
	 * Remove the default Media Upload Button
	 * 
	 * @return type
	 */
	public function remove_upload_button () {

		if ( ! $this->is_gallery_edit() ) {
			return;
		}

		if ( has_action( 'media_buttons', 'media_buttons' ) ) {
			remove_action( 'media_buttons', 'media_buttons' );
		}
	}

	/**
	 * Load Uploader js on Add New/Edit Gallery page
	 * 
	 */
	public function load_js () {

		if ( ! $this->is_gallery_edit() )
			return;
		
		wp_enqueue_script( 'mpp-upload-js', mediapress()->get_url() . 'admin/assets/js/mpp-admin.js', array( 'jquery', 'mpp_uploader' ) );
	}
	/**
	 * Load CSS on Add New/Edit Gallery page in admin
	 * @return type
	 */
	public function load_css () {

		if ( ! $this->is_gallery_edit() )
			return;

		//wp_enqueue_style( 'mpp-upload-css', MPP_PLUGIN_URL . 'admin/assets/css/mpp-admin.css' );
		wp_enqueue_style( 'mpp-core-css', mediapress()->get_url() . 'assets/css/mpp-core.css' );
	}
	/**
	 * When a gallery is created/edit from dashboard, simulate the same behaviour as front end created galleries
	 * 
	 * @param type $post_id
	 * @param type $post
	 * @param type $update
	 * @return type
	 */
	public function update_gallery_details( $post_id, $post, $update ) {

		if( defined( 'DOING_AJAX' ) && DOING_AJAX || ! is_admin() ) {
			return;
		}
		/**
		 * On the front end, we are using the taxonomy term slugs as the value while on the backend we are using the term_id as the value
		 * 
		 * So, we are attaching this function only for the Dashboar created gallery
		 * 
		 * We do need to make it uniform in the futuer
		 * 
		 */
		//we need to set the object terms

		//we need to update the media count?


		//do we need to do anything else?
		//gallery-type
		//gallery-component
		//gallery status

		if( ! empty( $_POST['mpp-gallery-type'] ) ) {

			wp_set_object_terms( $post_id, absint( $_POST['mpp-gallery-type'] ), mpp_get_type_taxname() );
		}

		if( ! empty( $_POST['mpp-gallery-component'] ) ) {

			wp_set_object_terms( $post_id, absint( $_POST['mpp-gallery-component'] ), mpp_get_component_taxname() );

		}

		if( ! empty( $_POST['mpp-gallery-status'] ) ) {

			wp_set_object_terms( $post_id, absint( $_POST['mpp-gallery-status'] ), mpp_get_status_taxname() );

		}

		//update media cout or recount?
		if( ! empty( $_POST['mpp-gallery-component-id'] ) ) {

			mpp_update_gallery_meta( $post_id, '_mpp_component_id', absint( $_POST['mpp-gallery-component-id'] ) );

		} else {
			//if component id is not given, check if it is members gallery, if so, 
			
		}

		if( ! $update ) {

			do_action( 'mpp_gallery_created', $post_id );
		}

	}
	
}

//instantiate
MPP_Admin_Post_Helper::get_instance();
