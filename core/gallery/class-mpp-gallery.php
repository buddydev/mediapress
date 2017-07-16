<?php

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MediaPress Gallery class.
 *
 * @since 1.0.0
 *
 * @property string $type Gallery Type ( e.g photo|audio|video etc )
 * @property string $status Gallery Status( e.g public|private| friendsonly etc )
 * @property string $component Associated component name( e.g members|groups etc )
 * @property int $component_id Associated component object id( e.g group id or user id )
 * @property int $cover_id The attachment/media id for the cover of this gallery
 * @property int $media_count number of media in this gallery ( It gives count of all media, does not look at the privacy of media )
 */
class MPP_Gallery {

	/**
	 * Container for arbitrary data props.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Gallery id.
	 *
	 * Numeric gallery id mapped to the actual post id.
	 *
	 * @var int
	 */
	public $id;

	/**
	 * User ID who created this gallery.
	 *
	 * It maps to the post_author in the posts table.
	 *
	 * @var string
	 */
	public $user_id = 0;

	/**
	 * Gallery create date.
	 *
	 * Mapped to post_date column in the posts table.
	 *
	 * @var string
	 */
	public $date_created = '0000-00-00 00:00:00';

	/**
	 * Gallery's GMT publication time.
	 *
	 * Mapped to post_date_gmt in the posts column.
	 *
	 * @var string
	 */
	public $date_created_gmt = '0000-00-00 00:00:00';

	/**
	 * Gallery Title
	 *
	 * Mapped to post_title in the posts table.
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * Gallery slug.
	 *
	 * Mapped to post_name in the posts table.
	 *
	 * @var string
	 */
	public $slug = '';

	/**
	 * Gallery description.
	 *
	 * Mapped to post_content in the posts table.
	 *
	 * @var string
	 */
	public $description = '';

	/**
	 * Gallery excerpt/short description.
	 *
	 * Mapped to post_excerpt
	 *
	 * @var string
	 */
	public $excerpt = '';


	/**
	 * Whether comments are allowed.
	 *
	 * @var string
	 */
	public $comment_status = 'open';

	/**
	 * The post's password in plain text.
	 *
	 * @var string
	 */
	public $password = '';

	/**
	 * The gallery's local modified time.
	 *
	 * @var string
	 */
	public $date_updated = '0000-00-00 00:00:00';

	/**
	 * The Gallery's GMT modified time.
	 *
	 * @var string
	 */
	public $date_updated_gmt = '0000-00-00 00:00:00';

	/**
	 * A utility DB field for gallery content.
	 *
	 * @var string
	 */
	public $content_filtered = '';

	/**
	 * ID of parent gallery if hierarchies are allowed.
	 *
	 * @var int
	 */
	public $parent = 0;

	/**
	 * A field used for ordering gallery.
	 *
	 * @var int
	 */
	public $sort_order = 0;

	/**
	 * Cached comment count.
	 *
	 * A numeric string, for compatibility reasons.
	 *
	 * @var string
	 */
	public $comment_count = 0;

	/**
	 * MPP_Gallery constructor.
	 *
	 * @param int|Object|null $gallery gallery id or row object.
	 */
	public function __construct( $gallery = null ) {

		$_gallery = null;

		if ( ! $gallery ) {
			return;
		}
		// If $gallery is numeric, assume it to be the ID.
		if ( is_numeric( $gallery ) ) {
			$_gallery = $this->get_row( $gallery );
		} else {
			$_gallery = $gallery;
		}

		if ( empty( $_gallery ) || ! $_gallery->ID ) {
			return;
		}

		$this->map_object( $_gallery );
	}

	/**
	 * Get teh DB row corresponding to this post id
	 *
	 * @param int $id Gallery id(internal post id).
	 *
	 * @return WP_Post
	 */
	private function get_row( $id ) {
		return get_post( $id );
	}

	/**
	 * Maps a DB Object to MPP_Gallery
	 *
	 * @param WP_Post|Object $_gallery gallery row object.
	 */
	private function map_object( $_gallery ) {

		$field_map = $this->get_field_map();

		foreach ( get_object_vars( $_gallery ) as $key => $value ) {

			if ( isset( $field_map[ $key ] ) ) {
				$this->{$field_map[ $key ]} = $value;
			}
		}
		// Cache the gallery posts, meta, terms.
		_prime_post_caches( (array) $_gallery->ID, true, true );
	}

	/**
	 * Get field map
	 *
	 * Maps WordPress post table fields to gallery field
	 *
	 * @return array
	 */
	private function get_field_map() {

		return array(
			'ID'                    => 'id',
			'post_author'           => 'user_id',
			'post_title'            => 'title',
			'post_content'          => 'description',
			'post_excerpt'          => 'excerpt',
			'post_name'             => 'slug',
			'post_password'         => 'password',
			'post_date'             => 'date_created',
			'post_date_gmt'         => 'date_created_gmt',
			'post_modified'         => 'date_updated',
			'post_modified_gmt'     => 'date_updated_gmt',
			'comment_status'        => 'comment_status',
			'post_content_filtered' => 'content_filtered',
			'post_parent'           => 'parent',
			'menu_order'            => 'sort_order',
			'comment_count'         => 'comment_count',
		);
	}

	/**
	 * Get reverse field map
	 *
	 * Maps gallery variables to WordPress post table fields
	 *
	 * @return array
	 */
	private function get_reverse_field_map() {
		return array_flip( $this->get_field_map() );
	}

	/**
	 * Check if a property is set.
	 *
	 * @param string $key name of the property.
	 *
	 * @return bool
	 */
	public function __isset( $key ) {

		if ( isset( $this->data[ $key ] ) ) {
			return true;
		}

		// Check for our special dynamic properties first.
		if ( 'component' === $key ) {
			$this->set( $key, mpp_get_object_component( $this->id ) );
			return true;
		} elseif ( 'type' === $key ) {
			$this->set( $key, mpp_get_object_type( $this->id ) );
			return true;
		} elseif ( 'status' === $key ) {
			$this->set( $key, mpp_get_object_status( $this->id ) );
			return true;
		}

		return metadata_exists( 'post', $this->id, '_mpp_' . $key );
	}

	/**
	 * Get a dynamic property.
	 *
	 * @param string $key name of the property.
	 *
	 * @return mixed
	 */
	public function __get( $key ) {

		if ( isset( $this->data[ $key ] ) ) {
			return $this->data[ $key ];
		}

		if ( 'component' === $key ) {
			$this->set( $key, mpp_get_object_component( $this->id ) );
			return $this->data[ $key ];
		} elseif ( 'type' === $key ) {
			$this->set( $key, mpp_get_object_type( $this->id ) );
			return $this->data[ $key ];
		} elseif ( 'status' === $key ) {
			$this->set( $key, mpp_get_object_status( $this->id ) );
			return $this->data[ $key ];
		}

		// If not one of our special property, check for the property in meta data.
		$value = mpp_get_gallery_meta( $this->id, '_mpp_' . $key, true );

		return $value;
	}

	/**
	 * Set a property on the object.
	 *
	 * @param string $key name of the property.
	 * @param mixed  $value value of the property.
	 */
	public function __set( $key, $value ) {
		$this->set( $key, $value );
	}

	/**
	 * Converts Gallery object to associative array of field=>val
	 *
	 * @return array
	 */
	public function to_array() {

		$data = get_object_vars( $this );

		foreach ( array( 'ancestors' ) as $key ) {
			if ( $this->__isset( $key ) ) {
				$data[ $key ] = $this->__get( $key );
			}
		}

		return $data;
	}

	/**
	 * Save data.
	 *
	 * @param string $key name of the property.
	 * @param mixed  $value value.
	 */
	private function set( $key, $value ) {
		$this->data[ $key ] = $value;
		// update cache.
		mpp_add_gallery_to_cache( $this );
	}

}

/**
 * Retrieves gallery data given a gallery id or gallery object.
 *
 * @param int|object $gallery gallery id or gallery object. Optional, default is the current gallery from the loop.
 * @param string     $output Optional, default is Object. Either OBJECT, ARRAY_A, or ARRAY_N.
 *
 * @return MPP_Gallery|array|null MPP_Gallery on success or null on failure
 */
function mpp_get_gallery( $gallery = null, $output = OBJECT ) {

	$_gallery      = null;
	$needs_caching = false;

	// if gallery is not given, but we do have current_gallery setup.
	if ( empty( $gallery ) && mediapress()->current_gallery ) {
		$gallery = mediapress()->current_gallery;
	}

	if ( ! $gallery ) {
		return null;
	}

	// if already an instance of gallery object.
	if ( is_a( $gallery, 'MPP_Gallery' ) ) {
		$_gallery = $gallery;
	} elseif ( is_numeric( $gallery ) ) {
		$_gallery = mpp_get_gallery_from_cache( $gallery );

		if ( ! $_gallery ) {
			$_gallery      = new MPP_Gallery( $gallery );
			$needs_caching = true;
		}
	} elseif ( is_object( $gallery ) ) {

		// first check if we already have it cached.
		$_gallery = mpp_get_gallery_from_cache( $gallery->ID );

		if ( ! $_gallery ) {
			$_gallery      = new MPP_Gallery( $gallery );
			$needs_caching = true;
		}
	}
	// save to cache if not already in cache.
	if ( $needs_caching && ! empty( $_gallery ) && $_gallery->id ) {
		mpp_add_gallery_to_cache( $_gallery );
	}

	if ( ! $_gallery ) {
		return null;
	}

	// if the gallery has no id set.
	if ( ! $_gallery->id ) {
		return null;
	}

	if ( ARRAY_A === $output ) {
		return $_gallery->to_array();
	} elseif ( ARRAY_N === $output ) {
		return array_values( $_gallery->to_array() );
	}

	return $_gallery;
}

// mind it, mpp is not global group.
/**
 * Fetch a gallery object from cache.
 *
 * @param int $gallery_id numeric gallery id.
 *
 * @return bool|mixed
 */
function mpp_get_gallery_from_cache( $gallery_id ) {
	return wp_cache_get( 'mpp_gallery_' . $gallery_id, 'mpp' );
}

/**
 * Save gallery object to cache.
 *
 * @param MPP_Gallery $gallery the gallery object to save to cache.
 */
function mpp_add_gallery_to_cache( $gallery ) {
	wp_cache_set( 'mpp_gallery_' . $gallery->id, $gallery, 'mpp' );
}

/**
 * Delete gallery from cache.
 *
 * @param int $gallery_id numeric gallery id.
 */
function mpp_delete_gallery_cache( $gallery_id ) {
	global $_wp_suspend_cache_invalidation;

	if ( ! empty( $_wp_suspend_cache_invalidation ) ) {
		return;
	}

	if ( mpp_get_gallery_from_cache( $gallery_id ) ) {
		wp_cache_delete( 'mpp_gallery_' . $gallery_id, 'mpp' );
	}
}
