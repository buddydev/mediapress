<?php
/**
 * For example
 * Here is the Multioption field rendering
 * 
 */
class OptionsBuddy_Settings_Field_Multioption extends OptionsBuddy_Settings_Field{
    
    private $key = '';
	private $_option_name ;
	
    public function __construct( $field ) {
		
        parent::__construct($field);
		$this->extra = $field['extra'];//text etc
		
		$this->key = $extra['key'];
		$this->_option_name = $extra['name'];
		
    }
    
    
    public function render($args) {
	
        $this->callback_text($args);
    }
	    
	function callback_text( $args ) {

        $value = esc_attr( $args['value'] );
        $size  = $this->get_size();
		
		$extra = $this->extra;
		
		$name = $extra['name'];
		if( is_array( $value ) )
			$value = $value[ $name ];
		
		$name = $args['base_name']. "[{$name}][{$extra['key']}]";
	
		
		printf( '<input type="text" class="%1$s-text" id="%2$s" name="%2$s" value="%3$s"/>', $size, $name, $value );
		printf( '<span class="description"> %s </span>', $this->get_desc() );

       
    }
	
	public function get_value( $options ) {
		
		//multi option is always an array
		if( !$value || ! is_array( $value ) )
			return $value;
		
		return $value[$this->_option_name];
	}
}
