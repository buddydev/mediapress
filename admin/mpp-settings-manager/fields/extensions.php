<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * For example
 * Here is the Multioption field rendering
 *
 */
class MPP_Admin_Settings_Field_Extensions extends MPP_Admin_Settings_Field {
	/**
	 * Field key(type key).
	 *
	 * @var string
	 */
	private $key = '';

	/**
	 * Option name.
	 *
	 * @var string
	 */
	private $_option_name;

	/**
	 * Extra field data.
	 *
	 * @var array
	 */
	private $extra;

	/**
	 * MPP_Admin_Settings_Field_Extensions constructor.
	 *
	 * @param array $field field setting.
	 */
	public function __construct( $field ) {

		parent::__construct( $field );
		$this->extra = $field['extra'];

		$this->key          = $this->extra['key'];
		$this->_option_name = $this->extra['name'];

	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function get_name() {
		return parent::get_name() . '-' . $this->key;
	}

	/**
	 * Render field.
	 *
	 * @param array $args field args.
	 */
	public function render( $args ) {
		$this->callback_text( $args );
	}

	/**
	 * Call to render text box.
	 *
	 * @param array $args args array.
	 */
	public function callback_text( $args ) {

		$value = esc_attr( $args['value'] );
		$size  = $this->get_size();

		$extra = $this->extra;

		$name = $extra['name'];
		if ( is_array( $value ) ) {
			$value = $value[ $name ];
		}

		$name = $args['base_name'] . "[{$name}][{$extra['key']}]";

		printf( '<input type="text" class="%1$s-text" id="%2$s" name="%2$s" value="%3$s"/>', $size, $name, $value );
		printf( '<span class="description"> %s </span>', $this->get_desc() );
	}

	/**
	 * Get the value.
	 *
	 * @param array $options options.
	 *
	 * @return mixed|string
	 */
	public function get_value( $options ) {

		$type = mpp_get_type_object( $this->key );

		$allowed_extensions = $type->get_allowed_extensions();

		if ( empty( $allowed_extensions ) ) {
			$allowed_extensions = $type->get_registered_extensions();
		}

		return join( ',', $allowed_extensions );

	}
}
