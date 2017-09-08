<?php
/**
 * Template Loader for Components
 *
 * @package mediapress
 */

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base Template Loader for Components
 */
abstract class MPP_Gallery_Template_Loader {
	/**
	 * Unique id for the loader.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Path to the directory.
	 *
	 * @var string
	 */
	protected $path = '';

	/**
	 * Just to show it for child. Should never be used directly.
	 *
	 * @param array $args args.
	 */
	protected function __construct( $args = null ) {
	}

	// we could implement singleton here for child using static keyword
	// but that won't work for < php 5.4 and self is not good idea here, so are the traits
	// moving singleton out to individual loader.
	/**
	 * Get unique view id
	 *
	 * @return string unique view ID
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get relative path for component template directory
	 *
	 * @return string
	 */
	public function get_path() {
		return $this->path;
	}

	/**
	 * Load template.
	 * Override in child.
	 */
	abstract public function load_template();
}
