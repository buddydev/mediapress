<?php
/**
 * For example
 * Here is the Multioption field rendering
 * 
 */
class OptionsBuddy_Settings_Field_Multioption extends OptionsBuddy_Settings_Field{
    
    
    public function __construct( $field ) {
		
        parent::__construct($field);
		//$this->subtype = $field['subtype'];//text etc
		
    }
    
    
    public function render($args) {
		
        $this->callback_text($args);
    }
	    
	function callback_text( $args ) {

        $value = esc_attr( $args['value'] );
        $size  = $this->get_size();
		
		
		
		$extra = $args['extra'];
		
		$name = $extra['name'];
		$name = $args['base_name']. "[{$name}][{$extra['key']}]";
		
		printf( '<input type="text" class="%1$s-text" id="%2$s" name="%2$s" value="%3$s"/>', $size, $name, $value );
		printf( '<span class="description"> %s </span>', $this->get_desc() );

       
    }
}
