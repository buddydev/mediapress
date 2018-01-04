<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings Section.
 */
class MPP_Admin_Settings_Section {
	/**
	 * Section id( Should be unique)
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Section title.
	 *
	 * @var string
	 */
	private $title;

	/**
	 * Section description.
	 *
	 * @var string
	 */
	private $desc = '';

	/**
	 * Fields in the section.
	 *
	 * @var array
	 */
	private $fields = array();

	/**
	 * Section constructor.
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
	 * Adds a field to this section
	 *
	 * We can use it to chain and add multiple fields in a go
	 *
	 * @param array $field field settings.
	 *
	 * @return MPP_Admin_Settings_Section
	 */
	public function add_field( $field ) {

		// check if a field class with name MPP_Admin_Settings_Field_$type exists, use it.
		$type = 'text';

		if ( isset( $field['type'] ) ) {
			$type = $field['type'];
		}// text/radio etc.

		$class_name = 'MPP_Admin_Settings_Field';
		// a field specific class can be declared as MPP_Admin_Settings_Field_typeName.
		$type             = join( '_', array_map( 'ucfirst', explode( '_', $type ) ) );
		$field_class_name = $class_name . '_' . $type;

		if ( class_exists( $field_class_name ) && is_subclass_of( $field_class_name, $class_name ) ) {
			$class_name = $field_class_name;
		}

		$field_object = new $class_name( $field );

		$id = $field_object->get_id();

		// let us store the field.
		$this->fields[ $id ] = $field_object;

		return $this;
	}

	/**
	 * Add Multiple Setting fields
	 *
	 * @see MPP_Admin_Settings_Section::add_field()
	 *
	 * @param array $fields array of field arrays.
	 *
	 * @return MPP_Admin_Settings_Section
	 */
	public function add_fields( $fields ) {

		foreach ( $fields as $field ) {
			$this->add_field( $field );
		}

		return $this;
	}

	/**
	 * Override fields
	 *
	 * @param array $fields fields array.
	 *
	 * @return MPP_Admin_Settings_Section
	 */
	public function set_fields( $fields ) {
		// if set fields is called, first reset fields.
		$this->reset_fields();

		$this->add_fields( $fields );

		return $this;
	}

	/**
	 * Resets fields
	 */
	public function reset_fields() {
		unset( $this->fields );

		$this->fields = array();

		return $this;
	}

	/**
	 * Setters
	 */

	/**
	 * Set section id.
	 *
	 * @param string $id section id.
	 *
	 * @return $this
	 */
	public function set_id( $id ) {

		$this->id = $id;

		return $this;
	}

	/**
	 * Set section title.
	 *
	 * @param string $title section title.
	 *
	 * @return $this
	 */
	public function set_title( $title ) {

		$this->title = $title;

		return $this;
	}

	/**
	 * Set section description.
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
	 * Get the Section ID
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get Section title
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
	 * Get a multidimensional array of the setting fields Objects in this section
	 *
	 * @return MPP_Admin_Settings_Field[]
	 */
	public function get_fields() {
		return $this->fields;
	}

	/**
	 * Get a field object.
	 *
	 * @param string $name field name.
	 *
	 * @return MPP_Admin_Settings_Field
	 */
	public function get_field( $name ) {
		return $this->fields[ $name ];
	}

}
