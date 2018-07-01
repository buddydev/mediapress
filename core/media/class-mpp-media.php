<?php
/**
 * MPP_Media
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over .
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MediaPress Media class.
 *
 * Please do not use this class directly instead use mpp_get_media
 *
 * @see mpp_get_media
 *
 * @since 1.0.0
 *
 * @property string $type Media Type ( e.g photo|audio|video etc )
 * @property string $status Media Status( e.g public|private| friendsonly etc )
 * @property string $component Associated component name( e.g members|groups etc )
 * @property int $component_id Associated component object id( e.g group id or user id )
 * @property int $cover_id The attachment/media id for the cover of this media(applies to non photo media)
 * @property bool $is_orphan Is the media marked as orphan?
 * @property bool $is_oembed Is the media Oembed
 * @property bool $is_remote Is the file stored at remote location. (have we used ftp|cdn for storing files?).
 * @property bool $is_raw Is the file raw link.
 * @property string $oembed_content Oembed content.
 * @property string $source Source link(if any in case of remote media).
 */
class MPP_Media {

	/**
	 * Used as private data store to store dynamic property/values.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Media id.
	 *
	 * @var int mapped to post ID/Attachment ID
	 */
	public $id;

	/**
	 * Parent Gallery Id.
	 *
	 * @var int post id for the gallery
	 */
	public $gallery_id;

	/**
	 * Id of media uploader user.
	 *
	 * A numeric.
	 *
	 * @var int creator id
	 */
	public $user_id = 0;

	/**
	 * The media's local publication time.
	 *
	 * @var string
	 */
	public $date_created = '0000-00-00 00:00:00';

	/**
	 * The media's GMT publication time.
	 *
	 * @var string
	 */
	public $date_created_gmt = '0000-00-00 00:00:00';

	/**
	 * The Media title.
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * The media slug
	 * mapped to post_name
	 *
	 * @var string
	 */
	public $slug = '';

	/**
	 * The media description.
	 * mapped to post_content.
	 *
	 * @var string
	 */
	public $description = '';

	/**
	 * The media excerpt.
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
	 * The media's local modified time.
	 *
	 * @var string
	 */
	public $date_updated = '0000-00-00 00:00:00';

	/**
	 * The Media's GMT modified time.
	 *
	 * @var string
	 */
	public $date_updated_gmt = '0000-00-00 00:00:00';

	/**
	 * A utility DB field for media description content.
	 *
	 * @var string
	 */
	public $content_filtered = '';

	/**
	 * A field used for ordering posts.
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
	 * Was the file actually uploaded by the user?
	 *
	 * @var bool true if uploaded to local or remote location by the user
	 */
	public $is_uploaded = 0;

	/**
	 * Which remote Service Is being Used (id will dep[end on type of service, It uniquely identifies the remote)
	 *
	 * @var int
	 */
	public $remote_service_id = 0;

	/**
	 * Is imported file, in this case we treat it as local file but store the original url from where it was imported
	 *
	 * @var boolean true if file is imported from somewhere else
	 */
	public $is_imported = 0;

	/**
	 * In case of imported file, from where it was imported?
	 *
	 * @var int
	 */
	public $imported_url = 0;

	/**
	 * In case of embedded content, from where it originates?
	 *
	 * @var string
	 */
	public $embed_url = 0;

	/**
	 * The html content of the embedded thing?
	 *
	 * @var string
	 */
	public $embed_html = 0;

	/**
	 * Storage manager.
	 *
	 * @var MPP_Storage_Manager
	 */
	public $storage = null;

	/**
	 * Constructor.
	 *
	 * @param int|MPP_Media|array|null $media medi id, array or object.
	 */
	public function __construct( $media = null ) {

		$_media = null;

		if ( ! $media ) {
			return;
		}

		if ( is_numeric( $media ) ) {
			$_media = $this->get_row( $media );
		} else {
			// assuming object.
			$_media = $media;
		}

		if ( empty( $_media ) || ! $_media->ID ) {
			return;
		}

		$this->map_object( $_media );
	}

	/**
	 * Get a row from db.
	 *
	 * @param int $media_id media id.
	 *
	 * @return WP_POST|null
	 */
	public function get_row( $media_id ) {
		return get_post( $media_id );
	}

	/**
	 * Map the plain object to this class's object.
	 *
	 * @param Object $media db row.
	 */
	public function map_object( $media ) {

		// media could be a db row or a self object or a WP_Post object.
		if ( is_a( $media, 'MPP_Media' ) ) {
			// should we map or should we throw exception and ask them to use the mpp_get_media.
			_doing_it_wrong( 'MPP_Media::__construct', __( 'Please do not call the constructor directly, Instead the recommended way is to use mpp_get_media', 'mediapress' ), '1.0' );
			return;
		}

		$field_map = $this->get_field_map();

		foreach ( get_object_vars( $media ) as $key => $value ) {

			if ( isset( $field_map[ $key ] ) ) {
				$this->{$field_map[ $key ]} = $value;
			}
		}
	}

	/**
	 * Get field map
	 *
	 * Maps WordPress post table fields to MPP_Media field
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
			'post_parent'           => 'gallery_id',
			'menu_order'            => 'sort_order',
			'comment_count'         => 'comment_count',
		);
	}

	/**
	 * Get reverse field map
	 * Maps gallery variables to WordPress post table fields
	 *
	 * @return array
	 */
	private function get_reverse_field_map() {
		return array_flip( $this->get_field_map() );
	}

	/**
	 * Check if a property is set?
	 *
	 * @param string $key property name.
	 *
	 * @return bool
	 */
	public function __isset( $key ) {

		$exists = false;

		if ( isset( $this->data[ $key ] ) ) {
			return true;
		}

		if ( 'component' == $key ) {
			$this->set( $key, mpp_get_object_component( $this->id ) );
			$exists = true;
		} elseif ( 'type' == $key ) {
			$this->set( $key, mpp_get_object_type( $this->id ) );
			$exists = true;
		} elseif ( 'status' == $key ) {
			$this->set( $key, mpp_get_object_status( $this->id ) );
			$exists = true;
		}

		if ( $exists ) {
			return $exists;
		}

		// eg _mpp_is_remote etc on call of $obj->is_remote.
		return metadata_exists( 'post', $this->id, '_mpp_' . $key );
	}

	/**
	 * Get a dynamic property.
	 *
	 * @param string $key property name.
	 *
	 * @return mixed
	 */
	public function __get( $key ) {

		if ( isset( $this->data[ $key ] ) ) {
			return $this->data[ $key ];
		}

		if ( 'component' == $key ) {
			$this->set( $key, mpp_get_object_component( $this->id ) );

			return $this->data[ $key ];
		} elseif ( 'type' == $key ) {

			$this->set( $key, mpp_get_object_type( $this->id ) );

			return $this->data[ $key ];
		} elseif ( 'status' == $key ) {
			$this->set( $key, mpp_get_object_status( $this->id ) );

			return $this->data[ $key ];
		}

		$value = mpp_get_media_meta( $this->id, '_mpp_' . $key, true );

		return $value;
	}

	/**
	 * Set a dynamic property.
	 *
	 * @param string $key property name.
	 * @param mixed  $value value.
	 */
	public function __set( $key, $value ) {

		$this->set( $key, $value );
	}

	/**
	 * Convert Object to array
	 *
	 * @return array
	 */
	public function to_array() {

		$post = get_object_vars( $this );

		foreach ( array( 'ancestors' ) as $key ) {

			if ( $this->__isset( $key ) ) {
				$post[ $key ] = $this->__get( $key );
			}
		}

		return $post;
	}

	/**
	 * Special set method.
	 *
	 * @param string $key property name.
	 * @param mixed  $value value.
	 */
	private function set( $key, $value ) {

		$this->data[ $key ] = $value;
		// update cache.
		mpp_add_media_to_cache( $this );
	}
}

/**
 * Retrieves Media data given a media id or media object.
 *
 * @param int|object $media media id or media object. Optional, default is the current media from the loop.
 * @param string     $output Optional, default is Object. Either OBJECT, ARRAY_A, or ARRAY_N.
 * @param string     $filter Optional, default is raw.
 *
 * @return MPP_Media|null MPP_Media on success or null on failure
 */
function mpp_get_media( $media = null, $output = OBJECT ) {

	$_media        = null;
	$needs_caching = false;

	// if a media is not given but we are inside the media loop.
	if ( empty( $media ) && mediapress()->current_media ) {
		$media = mediapress()->current_media;
	}

	if ( ! $media ) {
		return null;
	}

	// if already an instance of gallery object.
	if ( is_a( $media, 'MPP_Media' ) ) {
		$_media = $media;
	} elseif ( is_numeric( $media ) ) {
		$_media = mpp_get_media_from_cache( $media );

		if ( ! $_media ) {
			$_media        = new MPP_Media( $media );
			$needs_caching = true;
		}
	} elseif ( is_object( $media ) ) {
		$_media = mpp_get_media_from_cache( $media->ID );

		if ( ! $_media ) {
			$_media        = new MPP_Media( $media );
			$needs_caching = true;
		}
	}

	// save to cache if not already in cache.
	if ( $needs_caching && ! empty( $_media ) && $_media->id ) {
		mpp_add_media_to_cache( $_media );
	}

	if ( empty( $_media ) ) {
		return null;
	}

	if ( ! $_media->id ) {
		return;
	}

	if ( $output == ARRAY_A ) {
		return $_media->to_array();
	} elseif ( $output == ARRAY_N ) {
		return array_values( $_media->to_array() );
	}

	return $_media;
}

/**
 * Retrieve Media object from cache
 *
 * @access  private
 *
 * @param int $media_id media id.
 *
 * @return MPP_Media
 */
function mpp_get_media_from_cache( $media_id ) {
	return wp_cache_get( 'mpp_gallery_media_' . $media_id, 'mpp' );
}

/**
 * Adds a Media object to cache
 *
 * @param MPP_Media $media media object.
 */
function mpp_add_media_to_cache( $media ) {
	wp_cache_set( 'mpp_gallery_media_' . $media->id, $media, 'mpp' );
}

/**
 * Clear media cache
 *
 * @param int $media_id media id.
 */
function mpp_delete_media_cache( $media_id ) {

	global $_wp_suspend_cache_invalidation;

	if ( ! empty( $_wp_suspend_cache_invalidation ) ) {
		return;
	}

	if ( mpp_get_media_from_cache( $media_id ) ) {
		wp_cache_delete( 'mpp_gallery_media_' . $media_id, 'mpp' );
	}
}
