<?php


if( ! class_exists( 'OptionsBuddy_Settings_Field' ) ):
/**
 * Abstracts a Setting Field
 * 
 * This class abstarcts the Settings field
 * For your custom fields, you may extend this class and its render(), sanitize() method
 */
class OptionsBuddy_Settings_Field {
    
	/**
     *
     * @var string unique field id 
     */
    private $id;
    /**
     *
     * @var string Unique field name, almost same as id 
     */
    private $name;
    /**
     *
     * @var string Label for the settings field 
     */
    private $label;
    
    /**
     * 
     * @var string description of the setting field 
     */
    private $desc;
    /**
     *
     * @var string Field Type
     * @since version 1.0
     * current allowed values  
     */
    private $type= 'text';
    /**
     *
     * @var mixed associative array of key=>val pair for multiselect,select checkbox etc 
     */
    private $options;//array of key=>label for radio/multichebox etc
    
    /**
     *
     * @var string used for generating classes of the input element 
     */
    private $size;// to apply class and size in case of wysiwyg
    /**
     *
     * @var mixed the default value of the current field 
     */
    private $default ='';
    
    /**
     *
     * @var string name of a callable function/method used to sanitize the field data
     */
    private $sanitize_cb;
    
    public function __construct($field) {
        
         $defaults = array(
            'name'          => '',
            'label'         => '',
            'desc'          => '',
            'type'          => 'text',//default type is text. allowd values are text|textarea|checkbox|radio|password|image|file
            'options'       => '',
            'size'          =>'regular',
            'sanitize_cb'   => '',
            'default'		=>''
             
        );

        $arg = wp_parse_args( $field, $defaults );
		
        extract($arg);
        
        $this->id           = $this->name = $name;
        $this->label        = $label;
        $this->desc         = $desc;
        $this->type         = $type;
        $this->sanitize_cb  = $sanitize_cb;
        $this->options      = $options;
        $this->size         = $size;
        $this->default      = $default;
    }
    
    /**
     * 
     * @param string $field_name property name
     * @return mixed|boolean  the value of the property or false
     */
    public function get( $field_name ) {
		
        if( isset ( $this->{$field_name} ) )
            return $this->{$field_name};
            
        return false;    
        
    }
    

    public function get_id() {
        
        return $this->id;
    }
    public function get_name() {
        
        return $this->name;
    }

    public function get_label() {
        
        return $this->label;
    }
    public function get_desc() {
        
        return $this->desc;
    }
	
    public function get_type() {
        
        return $this->type;
    }
	
    public function get_options() {
        
        return $this->options;
    }
	
    public function get_size() {
        
        return $this->size;
    }
    
    public function get_default() {
		
        return $this->default;
    }
    
    public function get_sanitize_cb() {

		if( !empty( $this->sanitize_cb ) && is_callable( $this->sanitize_cb ) ) 
			$cb = $this->sanitize_cb ;
		else 
			$cb=  false;


        return $cb;
    }
    
    
    
     /**
      * Sanitize options callback for Settings API
      * 
      * only used if the option name is global
      * If the option name stored in options table is not unique and used as part of optgroup, this method is not callde
      * 
     */
    public function sanitize( $value ) {
      
		$sanitize_callback =  $this->get_sanitize_cb();

		// If callback is set, call it
		if ( $sanitize_callback ) {
			$value = call_user_func( $sanitize_callback, $value );

		} elseif ( ! is_array( $value ) ) {
			$value = sanitize_text_field( $value );

		}
       
        return $value;
    }
    /**
     * Display the form elemnts
     * 
     * Override it in the child classes to show the output
     * 
     * @param string $args
     */
    public function render( $args ) {
        
        $method_name = 'callback_' . $this->get_type();
        
        if( method_exists( $this, $method_name ) )
            call_user_func ( array( $this, $method_name ), $args );
                
        
    }
    
    
    /**
     * Hepler methods to generate the form elements for settings fields
     * These are fallback, if you are adding a new field type, please override render method in your class instead of using this
     * The inspiration for these display methods were the Settings api class by Tareq<>
     */
    
    /**
     * Displays a text field for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_text( $args ) {

        $value = esc_attr( $args['value'] );
        $size  = $this->get_size();

         printf( '<input type="text" class="%1$s-text" id="%2$s" name="%2$s" value="%3$s"/>', $size, $args['option_key'], $value );
         printf( '<span class="description"> %s </span>', $this->get_desc() );

       
    }

    /**
     * Displays a checkbox for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_checkbox( $args ) {

        $value = esc_attr($args['value'] );
       
        $id = $this->get_id();
        
       
        printf( '<input type="checkbox" class="checkbox" id="%1$s" name="%1$s" value="1" %3$s />', $args['option_key'],  $value, checked( $value, 1, false ) );
        printf( '<label for="%1$s"> %2$s</label>', $args['option_key'], $this->get_desc() );

       
    }

    /**
     * Displays a multicheckbox a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_multicheck( $args ) {
        
        $id = $this->get_id();
        $value = $args['value'] ;
        $options = $this->get_options();
        
        foreach ( $options as $key => $label ) {
           $checked = isset( $value[$key] ) ? $value[$key] : 0;
           printf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%1$s[%2$s]" value="%2$s"%3$s />', $args['option_key'],  $key, checked( $checked, $key, false ) );
           printf( '<label for="%1$s[%3$s]"> %2$s </label><br>', $args['option_key'], $label, $key );
        }
        printf( '<span class="description"> %s </span>', $this->get_desc() );

       
    }

    /**
     * Displays a multicheckbox a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_radio( $args ) {

        $value = $args['value'];
        $id = $this->get_id();
        $options = $this->get_options();
       
        foreach ( $options as $key => $label ) {
           printf( '<input type="radio" class="radio" id="%1$s[%3$s]" name="%1$s" value="%3$s"%4$s />', $args['option_key'], $id, $key, checked( $value, $key, false ) );
           printf( '<label for="%1$s[%4$s]"> %3$s</label><br>', $args['option_key'], $id, $label, $key );
        }
        printf( '<span class="description"> %s</label>', $this->get_desc() );

       
    }

    /**
     * Displays a selectbox for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_select( $args ) {
        $id = $this->get_id();
        $value = esc_attr($args['value'] );
        
        $options = $this->get_options();
        
        $size = $this->get_size();
        
        printf( '<select class="%1$s" name="%2$s" id="%2$s">', $size, $args['option_key'], $id );
        foreach ( $options as $key => $label ) {
            printf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $label );
        }
        printf( '</select>' );
        printf( '<span class="description"> %s </label>', $this->get_desc() );

       
    }

    /**
     * Displays a textarea for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_textarea( $args ) {

        $value = esc_attr($args['value'] );
        $size = $this->get_size();

       printf( '<textarea rows="5" cols="55" class="%1$s-text" id="%2$s" name="%2$s">%3$s</textarea>', $size, $args['option_key'],  $value );
       printf( '<br /><span class="description"> %s </span>', $this->get_desc() );

      
    }

    /**
     * Displays a textarea for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_html( $args ) {
        echo $this->get_desc();
    }

    /**
     * Displays a rich text textarea for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_wysiwyg( $args ) {

        $value = wpautop( $args['value'] );
        $size = $this->get_size();
        
        if('regular' == $size)
            $size= '500px';
       

        echo '<div style="width: ' . $size . ';">';

        wp_editor( $value, $args['option_key'] , array( 'teeny' => true, 'textarea_rows' => 10 ) );

        echo '</div>';

        printf( '<br /><span class="description"> %s </span>', $this->get_desc() );
    }

    
    /**
     * Displays a password field for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_password( $args ) {

       $value = esc_attr($args['value'] );
       $size = $this->get_size();
       printf( '<input type="password" class="%1$s-text" id="%2$s" name="%2$s" value="%3$s"/>', $size, $args['option_key'], $value );
       printf( '<span class="description"> %s </span>', $this->get_desc() );

       
    }

    
    
}

endif;