<?php
/**
 * Remote Media Importer
 *
 * Allows analyzing remote urls.
 *
 * @package    MediaPress
 * @subpackage Core/Media
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit( 0 );

/**
 * Remote Media Importer
 *
 * @property-read string   $url Remote url.
 * @property-read bool     $is_oembed is oembed media.
 * @property-read bool     $is_raw is raw media url.
 * @property-read string   $type media type.
 * @property-read string   $extension media extension.
 * @property-read stdClass $data Oembed data.
 * @property-read string   $title title.
 */
class MPP_Remote_Media_Importer {

	/**
	 * Remote URL.
	 *
	 * @var string
	 */
	private $url = '';

	/**
	 * Is Oembed.
	 *
	 * @var bool
	 */
	private $is_oembed = false;

	/**
	 * Is raw.
	 *
	 * @var bool
	 */
	private $is_raw = false;

	/**
	 * Media Type.
	 *
	 * @var string
	 */
	private $type = '';

	/**
	 * Extension if any.
	 *
	 * @var string
	 */
	private $extension = '';

	/**
	 * Oembed data.
	 *
	 * @var null
	 */
	private $data = null;

	/**
	 * Media Title.
	 *
	 * @var string
	 */
	private $title = '';

	/**
	 * MPP_Remote_Media_Importer constructor.
	 *
	 * @param string $url remote url.
	 */
	public function __construct( $url ) {
		$this->url = $url;
	}

	/**
	 * Get a property.
	 *
	 * @param string $name name.
	 *
	 * @return null
	 */
	public function __get( $name ) {
		return isset( $this->{$name} ) ? $this->{$name} : null;
	}

	/**
	 * Is the value set.
	 *
	 * @param string $name property name.
	 *
	 * @return bool
	 */
	public function __isset( $name ) {
		return property_exists( $this, $name );
	}

	/**
	 * Parse URL.
	 */
	public function parse() {
		$this->parse_raw();

		// If not raw, try for oembed.
		if ( ! $this->is_raw ) {
			$this->parse_oembed();
		}
	}

	/**
	 * Parse to see if it is a raw url.
	 */
	private function parse_raw() {

		$this->extension = mpp_get_file_extension( $this->url );
		$this->type      = mpp_get_media_type_from_extension( $this->extension );
		// If the type is supported. let us set the flag.
		if ( $this->type ) {
			$this->is_raw    = true;
			$this->is_oembed = false;
			$this->title     = wp_basename( $this->url );
		} else {
			$this->is_raw    = false;
			$this->type      = '';
			$this->extension = '';
		}
	}

	/**
	 * Parse Oembed.
	 */
	private function parse_oembed() {
		$oembed = _wp_oembed_get_object();

		// discover, width.
		$args = wp_parse_args( array(), wp_embed_defaults( $this->url ) );

		$data = $oembed->get_data( $this->url, $args );

		if ( false === $data ) {
			$this->is_oembed = false;
			return ;
		}

		$this->is_oembed = true;
		$this->is_raw    = false;
		$this->data      = $data;
		$this->title     = $data->title;
	}
}
