<?php

//only load if not already loaded
if( ! class_exists( 'OptionsBuddy_Settings_Page' ) ):
	
	$ob_path = dirname( __FILE__ ). DIRECTORY_SEPARATOR ;
	
	require_once $ob_path.'core/class-ob-field.php';
	require_once $ob_path.'core/class-ob-section.php';
	require_once $ob_path.'core/class-ob-page.php';
	require_once $ob_path.'core/class-ob-helper.php';
	
endif;


//field class autoloader
if( ! function_exists( 'options_buddy_field_class_loader' ) ):
/**
 * Register a loader to load Field Class dynamically if they exist in fields/ directory
 * 
 * @param string $class name of the class
 */
function options_buddy_field_class_loader( $class ) {
  
    //let us just get the part after OptionsBuddy_Settings_Field_ string e.g for OptionsBuddy_Settings_Field_Text class it loads fields/text.php
    $file_name = strtolower( str_replace( 'OptionsBuddy_Settings_Field_', '', $class ) );
    
    //let us reach to the file
    $file = dirname( __FILE__ ). DIRECTORY_SEPARATOR . 'fields'. DIRECTORY_SEPARATOR . $file_name. '.php';
     
    if( is_readable( $file ) )
        require_once $file; 
       
}
spl_autoload_register('options_buddy_field_class_loader');

endif;
