<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings panel.
 */
class MPP_Admin_Settings_Panel {
	/**
	 * Panel id(should be unique for the page).
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Panel title.
	 *
	 * @var string
	 */
	private $title;

	/**
	 * Panel description.
	 *
	 * @var string
	 */
	private $desc = '';

	/**
	 * Sections array.
	 *
	 * @var array
	 */
	private $sections = array();


	/**
	 * Constructor.
	 *
	 * @param string $id Section Id.
	 * @param string $title Section Title.
	 * @param string $desc Section description.
	 */
	public function __construct( $id, $title, $desc = '' ) {

		$this->id    = $id;
		$this->title = $title;
		$this->desc  = $desc;

	}

	/**
	 * Add new Setting Section
	 *
	 * @param  string $id section id.
	 * @param  string $title section title.
	 * @param  string $desc Section description.
	 *
	 * @return MPP_Admin_Settings_Section
	 */
	public function add_section( $id, $title, $desc = '' ) {

		$section_id = $id;

		$this->sections[ $section_id ] = new MPP_Admin_Settings_Section( $id, $title, $desc );

		return $this->sections[ $section_id ];

	}

	/**
	 * Add a sections.
	 *
	 * @param array $sections sections array.
	 *
	 * @return MPP_Admin_Settings_Panel
	 */
	public function add_sections( $sections ) {

		foreach ( $sections as $id => $title ) {
			$this->add_section( $id, $title );
		}

		return $this;
	}

	/**
	 * Get section.
	 *
	 * @param string $id section id.
	 *
	 * @return MPP_Admin_Settings_Section| false
	 */
	public function get_section( $id ) {
		return isset( $this->sections[ $id ] ) ? $this->sections[ $id ] : false;
	}

	/**
	 * Get all sections.
	 *
	 * @return MPP_Admin_Settings_Section[]
	 */
	public function get_sections() {
		return $this->sections;
	}

	/**
	 * Setters
	 */

	/**
	 * Set panel id.
	 *
	 * @param string $id panel id.
	 *
	 * @return $this
	 */
	public function set_id( $id ) {

		$this->id = $id;

		return $this;
	}

	/**
	 * Set title.
	 *
	 * @param string $title title.
	 *
	 * @return $this
	 */
	public function set_title( $title ) {

		$this->title = $title;

		return $this;
	}

	/**
	 * Set description.
	 *
	 * @param string $desc description.
	 *
	 * @return $this
	 */
	public function set_description( $desc ) {

		$this->desc = $desc;

		return $this;
	}


	/**
	 * Get panel id.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 *  Get Section title
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Get Section Description
	 *
	 * @return string
	 */
	public function get_disc() {

		return $this->desc;
	}

	/**
	 * Is this panel empty?
	 *
	 * A panel is considered empty if it is registered but have no sections added
	 *
	 * @return boolean
	 */
	public function is_empty() {

		if ( empty( $this->sections ) ) {
			return true;
		}

		return false;
	}

}
