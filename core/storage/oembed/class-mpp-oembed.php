<?php
/**
 * Oembed (Not Implemented yet)
 *
 * @package mediapress.
 */
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MPP_oEmbed {
	/**
	 * Oembed instance.
	 *
	 * @var WP_oEmbed
	 */
	private static $oembed;

	/**
	 * Media url.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Json response data.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * MPP_oEmbed constructor.
	 *
	 * @param string $url media url.
	 */
	public function __construct( $url = '' ) {

		$this->url = $url;

		if ( ! isset( self::$oembed ) ) {
			$this->oembed = $this->get_wp_oembed();
		}

	}

	/**
	 * Get html for the embed.
	 *
	 * @param string $url media url.
	 * @param array  $args args.
	 *
	 * @return bool|mixed|void
	 */
	public function get_html( $url, $args = array() ) {

		$data = $this->get_data( $url, $args );
		if ( ! $data ) {
			return false;
		}

		return apply_filters( 'oembed_result', self::$oembed->data2html( $data, $this->url ), $this->url, $args );
	}

	/**
	 * Get data.
	 *
	 * @param string $url media url.
	 * @param array  $args args.
	 *
	 * @return array|bool|false|object
	 */
	public function get_data( $url = '', $args = array() ) {

		if ( $url ) {
			$this->url = $url;
		}

		if ( ! $this->url ) {
			return false;
		}

		// if it was already fetched.
		if ( $this->data ) {
			return $this->data;
		}

		$provider = self::$oembed->get_provider( $this->url, $args );

		if ( ! $provider ) {
			return false;
		}

		$data = self::$oembed->fetch( $provider, $this->url, $args );

		if ( ! $data ) {
			return false;
		}

		$this->data = $data;

		return $this->data;
	}

	/**
	 * Get the WP_oEmbed instance.
	 *
	 * @return WP_oEmbed
	 */
	public function get_wp_oembed() {

		return _wp_oembed_get_object();
	}
}
