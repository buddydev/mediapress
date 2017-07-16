<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

/**
 * Base class used for storing basic details
 * name may change in future
 */
class MPP_Taxonomy {
	/**
	 * Term id.
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Term label.
	 *
	 * @var string
	 */
	public $label;

	/**
	 * Term Singular Name.
	 *
	 * @var string
	 */
	public $singular_name = '';

	/**
	 * Term plural name.
	 *
	 * @var string
	 */
	public $plural_name = '';

	/**
	 * Term Taxonomy id
	 *
	 * @var int
	 */
	public $tt_id; // term_taxonomy_id.

	/**
	 * Term Slug(without underscore(_) )
	 *
	 * @var string
	 */
	public $slug;

	/**
	 * MPP_Taxonomy constructor.
	 *
	 * @param array  $args array of args.
	 * @param string $taxonomy name of taxonomy.
	 */
	public function __construct( $args, $taxonomy ) {

		$term = null;

		if ( isset( $args['key'] ) ) {
			$term = _mpp_get_term( $args['key'], $taxonomy );
		} elseif ( isset( $args['id'] ) ) {
			$term = _mpp_get_term( $args['id'], $taxonomy );
		}

		if ( $term && ! is_wp_error( $term ) ) {

			$this->id    = $term->term_id;
			$this->tt_id = $term->term_taxonomy_id;

			// to make it truly multilingual, do not use the term name instead use the registered label if available.
			if ( isset( $args['label'] ) ) {
				$this->label = $args['label'];
			} else {
				$this->label = $term->name;
			}

			// remove _ from the slug name to make it private/public etc.
			$this->slug = str_replace( '_', '', $term->slug );

			if ( isset( $args['labels']['singular_name'] ) ) {
				$this->singular_name = $args['labels']['singular_name'];
			} else {
				$this->singular_name = $this->label;
			}

			if ( isset( $args['labels']['plural_name'] ) ) {
				$this->plural_name = $args['labels']['plural_name'];
			} else {
				$this->plural_name = $this->label;
			}
		}
	}

	/**
	 * Get term label.
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Get term ID.
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get term_taxonomy_id for the current tax term
	 *
	 * @return int
	 */
	public function get_tt_id() {
		return $this->tt_id;
	}

	/**
	 * Get term slug(without underscores).
	 *
	 * @return string, slug (It has underscores removed)
	 */
	public function get_slug() {
		return $this->slug;
	}
}

/**
 * Gallery|Media Status class
 *
 * @property string $activity_privacy Status mapped to activity privacy(supports BuddyPress Activity privacy plugin ).
 * @property callable $callback Callback function to check for the current user's access to gallery/media with this privacy.
 */
class MPP_Status extends MPP_Taxonomy {

	/**
	 * MPP_Status constructor.
	 *
	 * @param array $args array of args.
	 */
	public function __construct( $args ) {
		parent::__construct( $args, mpp_get_status_taxname() );
	}
}

/**
 * Gallery|Media Type class
 *
 * It is used for representing individual type objects.
 */
class MPP_Type extends MPP_Taxonomy {
	/**
	 * An array of allowed file extensions for the type.
	 *
	 * @var array file extensions for the media type array('jpg', 'gif', 'png');
	 */
	private $extensions;

	/**
	 * These are the initial registered extension for this type.
	 *
	 * Registered extensions are provided by developer. The actual extension may be different from it
	 *  as selected in the MediaPress settings.
	 *
	 * @var array of extensions e.g ( 'gif', 'png')
	 */
	private $registered_extensions = array();

	/**
	 * MPP_Type constructor.
	 *
	 * @param array $args array of args.
	 */
	public function __construct( $args ) {

		parent::__construct( $args, mpp_get_type_taxname() );

		$this->registered_extensions = $args['extensions'];

		$this->extensions = mpp_get_media_extensions( $this->get_slug() );
	}

	/**
	 * An array of allowed extensions( as updated by site admin in the MediaPress settings )
	 *
	 * @return array of file extensions e.g ( 'gif', 'png', 'jpeg' )
	 */
	public function get_allowed_extensions() {
		return $this->extensions;
	}

	/**
	 * An array of registered extensions( as registered by developer while using mpp_register_type)
	 *
	 * It may be different from the active extensions allowed.
	 *
	 * @return array of file extensions e.g ( 'gif', 'png', 'jpeg' )
	 */
	public function get_registered_extensions() {
		return $this->registered_extensions;
	}

}

/**
 * Gallery|Media Component
 */
class MPP_Component extends MPP_Taxonomy {
	/**
	 * Features associated with this component.
	 *
	 * @var MPP_Features
	 */
	private $features;

	/**
	 * MPP_Component constructor.
	 *
	 * @param array $args array of args.
	 */
	public function __construct( $args ) {

		parent::__construct( $args, mpp_get_component_taxname() );

		$this->features = new MPP_Features();
	}

	/**
	 * Check if component supports this feature
	 *
	 * @param string $feature feature name.
	 * @param mixed  $value optional. If given, check for the feature name and value combination.
	 *
	 * @return boolean
	 */
	public function supports( $feature, $value = false ) {
		return $this->features->supports( $feature, $value );
	}

	/**
	 * Add a feature support to component.
	 *
	 * @param string $feature name of the feature.
	 * @param mixed  $value value of the feature.
	 * @param bool   $single will this feature have only one value.
	 *
	 * @return MPP_Features
	 */
	public function add_support( $feature, $value, $single = false ) {
		return $this->features->register( $feature, $value, $single );
	}

	/**
	 * Remove support for a feature from the component.
	 *
	 * @param string $feature name of feature.
	 * @param string $value optional. Value of feature.
	 *
	 * @return MPP_Features
	 */
	public function remove_support( $feature, $value = null ) {
		return $this->features->deregister( $feature, $value );
	}

	/**
	 * Get an array of supported values.
	 *
	 * @param string $feature feature name.
	 *
	 * @return MPP_Features
	 */
	public function get_supported_values( $feature ) {
		return $this->features->get( $feature );
	}
}
