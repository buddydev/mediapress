<?php
/**
 * The OptionsBuddy Settings Manager Core file
 * 
 * We load the function/classes only if another instance has not loaded it
 * That's why we have put a function_exist/class_exist test everywhere
 * The purpose is to allow multiple plugins/themes use it
 */

if(!function_exists('options_buddy_field_class_loader')):
/**
 * Register a loader to load Field Class dynamically if they exist in fields/ directory
 * 
 * @param string $class name of the class
 */
function options_buddy_field_class_loader($class){
  
    //let us just get the part after OptionsBuddy_Settings_Field_ string e.g for OptionsBuddy_Settings_Field_Text class it loads fields/text.php
    $file_name = strtolower(str_replace( 'OptionsBuddy_Settings_Field_', '', $class ) );
    
    //let us reach to the file
    $file = dirname( __FILE__ ). DIRECTORY_SEPARATOR . 'fields'. DIRECTORY_SEPARATOR . $file_name. '.php';
     
    if( is_readable( $file ) )
        require_once $file; 
       
}
spl_autoload_register('options_buddy_field_class_loader');
endif;

if(!class_exists('OptionsBuddy_Settings_Field')):
/**
 * Abstracts a Setting Field
 * 
 * This class abstarcts the Settings field
 * For your custom fields, you may extend this class and its render(), sanitize() method
 */
class OptionsBuddy_Settings_Field{
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
    
	
	private $extra = array();//any thing extra goes here
    function __construct( $field ) {
        
         $defaults = array(
            'name'          => '',
            'label'         => '',
            'desc'          => '',
            'type'          => 'text',//default type is text. allowd values are text|textarea|checkbox|radio|password|image|file
            'options'       => '',
            'size'          =>'regular',
            'sanitize_cb'   => '',
            'default'		=> '',
			'extra'			=> array() 
             
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
		$this->extra		= $extra;
    }
    
    /**
     * 
     * @param string $field_name property name
     * @return mixed|boolean  the value of the property or false
     */
    public function get( $field_name ){
        if( isset ( $this->{$field_name} ) )
            return $this->{$field_name};
            
        return false;    
        
    }
    

    public function get_id(){
        
        return $this->id;
    }
    public function get_name(){
        
        return $this->name;
    }

    public function get_label(){
        
        return $this->label;
    }
    public function get_desc(){
        
        return $this->desc;
    }
    public function get_type(){
        
        return $this->type;
    }
    public function get_options(){
        
        return $this->options;
    }
    public function get_size(){
        
        return $this->size;
    }
    
    public function get_default(){
        return $this->default;
    }
    
	public function get_extra(){
		
		return $this->extra;
	}
    public function get_sanitize_cb(){

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
    function sanitize( $value ) {
      
            $sanitize_callback =  $this->get_sanitize_cb();

            // If callback is set, call it
            if ( $sanitize_callback ) {
                $value = call_user_func( $sanitize_callback, $value );
               
            } elseif ( !is_array( $value ) ) {
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
    public function render($args){
        
        
        $method_name = 'callback_'.$this->get_type();
        
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
    function callback_text( $args ) {

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
    function callback_checkbox( $args ) {

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
    function callback_multicheck( $args ) {
        
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
    function callback_radio( $args ) {

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
    function callback_select( $args ) {
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
    function callback_textarea( $args ) {

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
    function callback_html( $args ) {
        echo $this->get_desc();
    }

    /**
     * Displays a rich text textarea for a settings field
     *
     * @param array   $args settings field args
     */
    function callback_wysiwyg( $args ) {

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
    function callback_password( $args ) {

       $value = esc_attr($args['value'] );
       $size = $this->get_size();
       printf( '<input type="password" class="%1$s-text" id="%2$s" name="%2$s" value="%3$s"/>', $size, $args['option_key'], $value );
       printf( '<span class="description"> %s </span>', $this->get_desc() );

       
    }

    
    
}

endif;

if(!class_exists('OptionsBuddy_Settings_Section')):
    
class OptionsBuddy_Settings_Section{
    /**
     *
     * @var string Unique section Id for the page 
     */
    private $id;
    /**
     *
     * @var string section title 
     */
    private $title;
    /**
     *
     * @var string Section description  
     */
    private $desc ='';
    
    /**
     *
     * @var array  of fields
     */
    private $fields = array();//array
   
    
   /**
    * 
    * @param string $id Section Id
    * @param string $title Section Title
    * @param string $desc Section description
    */
    function __construct( $id, $title, $desc='') {
        
        $this->id    = $id;
        $this->title = $title;
        $this->desc  = $desc;
        
    }
    /**
     * Adds a field to this section
     * 
     * We can use it to chain and add multiple fields in a go
     * 
     * @return OptionsBuddy_Settings_Section
     */
    public function add_field($field){
       
        //check if a field class with name OptionsBuddy_Settings_Field_$type exists, use it 
        $type = 'text';
        
        if(isset($field['type']))
            $type = $field['type'];//text/radio etc
        
        $class_name= 'OptionsBuddy_Settings_Field';
        //a field specific class can be declared as OptionsBuddy_Settings_Field_typeName
        $field_class_name = $class_name . '_' . ucfirst( $type );
       
        if( class_exists( $field_class_name ) && is_subclass_of($field_class_name, $class_name ) )
                $class_name = $field_class_name; 
        
        
       //let us store the field  
       $this->fields[$field['name']] = new $class_name($field);
        
        return $this;
    }
    /**
     * Adds Multiple Setting fields at one
     * 
     * @see OptionsBuddy_Settings_Section::add_field()
     * @return OptionsBuddy_Settings_Section
     * 
     */
    public function add_fields( $fields ){
        
        foreach( $fields as $field )
            $this->add_field( $field );
        
        return $this;
    }

    /**
     * Override fields
     * 
     * @param type $fields
     * @return OptionsBuddy_Settings_Section
     */
    public function set_fields( $fields ){
        //if set fields is called, first reset fiels
        $this->reset_fields();
        
        $this->add_fields($fields);
        
        return $this;
    }
    /**
     * Resets fields
     */
    function reset_fields(){
        unset( $this->fields );
        $this->fields = array();
        return $this;
    }
    /**
     * Setters
     */
    
    public function set_id( $id ){
        $this->id = $id;
        return $this;
    }
    
    public function set_title( $title ){
        $this->title = $title;
        return $this;
    }
    public function set_description( $desc ){
        $this->desc = $desc;
        return $this;
    }
    
    
    /**
     * Retuns the Section ID
     * @return string Section ID
     */
    public function get_id(){
        return $this->id;
    }
    /**
     *  Returns Section title
     * @return string Section title
     */
    public function get_title(){
        return $this->title;
    }
    
    /**
     * Retursn Section Description
     * @return string section description
     */
    public function get_disc(){
        return $this->desc;
    }
    
    /**
     * Return a multidimensional array of the setting fields Objects in this section
     * @return OptionsBuddy_Settings_Field
     */
    public function get_fields(){
        return $this->fields;
    }
    /**
     * 
     * @param type $name
     * @return OptionsBuddy_Settings_Field
     */
    public function get_field($name){
        return $this->fields[$name];
    }
  
}

endif;

if( !class_exists( 'OptionsBuddy_Settings_Page' ) ):
/**
 * This class represents an Admin page
 * It could be a newly generated page or just an existing page
 * If the page exists, It will inject the sections/fields to that page   
 * 
 */
class OptionsBuddy_Settings_Page {
    /**
     *
     * @var string unique page slug where you want to show this page 
     */
    private $page='';
    
    /**
     *
     * @var string the option name to be stored in options table
     * 
     * If using individual field name as option is not enabled, this is used to store all the options in a multidimensional array
     * 
     */
    private $option_name='';
    /**
     *
     * @var string option group name 
     */
    private $optgroup = '';
    /**
     * Settings sections array
     *
     * @var  OptionsBuddy_Settings_Section
     */
    private $sections = array();
    
    private $cb_stack=array();//field_name=>callback stack

    /**
     *
     * @var boolean use unique option name for each settings? if enabled, each field will be individually stored in the options table 
     */
    private $use_unique_option = false;
    
    private $is_network_mode = false;
    private $is_bp_mode = false;
    public function __construct( $page ) {
       
        $this->page = $page;
        $this->set_option_name( $page );
        $this->set_optgroup( $page );//by default, set optgroup same as page
    }
    
    /**
     *  if use unique option is enabled, each setting field is stored in the options table as individual item, so an item can be retrieved as get_option('setting_field_name');
     * otherwise, all the setting field option is stored in a single option as array and that name of option is page_name or option_name depending on which one is set
     * @return \OptionsBuddy_Settings_Page
     */
    public function use_unique_option(){
        $this->use_unique_option = true;
        return $this;
    }
    
    public function use_single_option(){
        $this->use_unique_option = false;
        
        if(!isset($this->option_name))
            $this->set_option_name( $this->page );
       
        return $this;
    }
    
    /**
     * 
     * @return bool are we using unique options to store each field
     */
    public function using_unique_option(){
        return $this->use_unique_option;
    }
    
    
    function set_network_mode(){
        
        $this->is_network_mode = true;
        return $this;
    }
    
    function is_network_mode(){
        
        return $this->is_network_mode;
    }
    function set_bp_mode(){
        
        $this->is_bp_mode = true;
        return $this;
    }
    
    function is_bp_mode(){
        
        return $this->is_bp_mode;
    }
    
    function reset_mode(){
        $this->is_network_mode =false;
        $this->is_bp_mode =false;
        
        return $this;
    }
    /**
     * Set an option name if you want. It is only used if using_unique_option is disabled
     * @param type $option_name
     * @return OptionsBuddy_Settings_Page
     */
    public function set_option_name( $option_name ){
        
        $this->option_name = $option_name;
        return $this;
    }
    /**
     * Get the option name
     * 
     * @return string
     */
    public function get_option_name( ){
        
        return $this->option_name ;
    }
    
    public function set_optgroup($optgroup){
        $this->optgroup = $optgroup;
    }
    public function get_optgroup(){
        return $this->optgroup;
    }
   

    /**
     * Add new Setting Section 
     * 
     * @param  string $id section id
     * @param  string $title section title
     * @param  string $desc Section description
     * @return return OptionsBuddy_Settings_Section
     */
    public function add_section( $id, $title, $desc = false ){
        
        $section_id = $id ;
        
        $this->sections[$section_id] = new OptionsBuddy_Settings_Section( $id, $title, $desc );        
       
        return $this->sections[$section_id];
        
    }
     /**
      * 
      * @param type $sections
      * @return OptionsBuddy_Settings_Page
      */
    public function add_sections( $sections ) {
       
        foreach ( $sections as $id => $title )
            $this->add_section ( $id, $title );

        return $this;
    }
    /**
     * 
     * @param string $id
     * @return OptionsBuddy_Settings_Section
     */
    public function get_section( $id ){
        return $this->sections[$id];
        
    }
    /**
     * mainly used for generating the settings form
     * @return type
     */
    public function get_page(){
        
        return $this->page;
    }

    
   /**
    * Registers settings sections and fields
    * This should be called at admin_init action
    * If you are using existing page, make sure to attach your admin_init hook to low priority
    */
    public function init() {
        
        
        $global_option_name = $this->get_option_name();
        
        //check if the option exists, if not, let us add it
        if( ! $this->using_unique_option() ){
             if ( false == get_option( $global_option_name ) ) {
                 add_option( $global_option_name );
             }
        }
        //register settings sections
        //for every section
        foreach ( $this->sections as  $section ) {
                
            //for individual section
                       
            if ( $section->get_disc()  ) {
                $desc = '<div class="inside">'.$section->get_disc().'</div>';
                $callback = create_function('', 'echo "'.str_replace('"', '\"', $desc).'";');
            } else {
                $callback = '__return_false';
            }

            add_settings_section( $section->get_id(), $section->get_title(), $callback, $this->get_page() );
        
             
            
            //register settings fields
            foreach ( $section->get_fields() as $field ) {
                 
                 $option_name = $global_option_name . '[' . $field->get_name() . ']';
                 //when using local 
                 if( $this->using_unique_option() ){
                     
                    if ( false == get_option( $field->get_name() ) ) {
                        add_option( $field->get_name() );
                    }
                    //override option name
                    $option_name = $field->get_name();
                   
                   
                }
       
               

                $args = array(
                    'section' => $section->get_id(),
                    'std' => $field->get_default(),
                    'option_key'=> $option_name,
                    'value'     =>$this->get_option( $field->get_id(),  $field->get_default() ),
                    'base_name' => $global_option_name,
					'is_uniqueue' => $this->using_unique_option(),
					'extra' => $field->get_extra()
                );
                
                $this->cb_stack[$field->get_id()] = $field->get_sanitize_cb() ;
                
                add_settings_field( $option_name, $field->get_label(), array( $field, 'render' ), $this->get_page(), $section->get_id(), $args );
                
                 //when using local 
                 if( $this->using_unique_option() ){
                     
                         register_setting( $this->get_optgroup(), $field->get_name(), array( $field, 'sanitize' ) );
                }
                
            }
        
       
            
        //when using only one option to store all values
       if( !$this->using_unique_option() ){
           

         register_setting( $this->get_optgroup(), $this->get_option_name(), array( $this, 'sanitize_options' ) );
       }
  }
  }
    

    
    /**
     * Get the value of a settings field
     *
     * @param string  $option  settings field name
     * @param string  $section the section name this field belongs to
     * @param string  $default default text if it's not found
     * @return string
     */
    public function get_option( $option, $default = '' ) {
        $function_name = 'get_option';//use get_option function
        //if the page is in network mode, use get_site_option
        
        if($this->is_network_mode()){
            $function_name = 'get_site_option';
        }elseif( $this->is_bp_mode() ){
            if(function_exists('bp_get_option'))
                $function_name = 'bp_get_option';
            
        }
        
      
        
        
        if( !$this->using_unique_option() ){
            
            $options = $function_name( $this->get_option_name() );
          
            if ( isset( $options[$option] ) ) {
                    return $options[$option];
            }

            
        } else {
            $options = $function_name( $option, $default);
            
            return $options;
            
        }
        
               

        return $default;
    }

    /**
     * Show navigations as tab
     *
     * Shows all the settings section labels as tab
     */
    public function show_navigation() {
        //do not show nav is it is hidden
       
        $html = '<h2 class="nav-tab-wrapper">';

        foreach ( $this->sections as $tab ) {
            $html .= sprintf( '<a href="#%1$s" class="nav-tab" id="%1$s-tab">%2$s</a>', $tab->get_id(), $tab->get_title() );
        }

        $html .= '</h2>';

        echo $html;
    }

    /**
     * Show the section settings forms
     *
     * This function displays every sections in a different form
     */
    public function show_form() {
        ?>
        <div class="metabox-holder">
            <div class="postbox options-postbox" style="padding:10px;">
                <form method="post" action="options.php">
                 <?php settings_fields( $this->get_optgroup() ); ?>
                <?php foreach ( $this->sections as $section ) : ?>
                <div id="<?php echo $section->get_id(); ?>" class="settings-section-tab">
                        

                            <?php do_action( 'wsa_form_top_' . $section->get_id(), $section ); ?>
                            
                            <?php $this->do_settings_sections( $this->get_page(),$section->get_id() ); ?>
                            <?php do_action( 'wsa_form_bottom_' . $section->get_id(), $section ); ?>

                            <div style="padding-left: 10px">
                                <?php submit_button(); ?>
                            </div>
                       
                    </div>
                <?php endforeach; ?>
                     </form>
            </div>
        </div>
        <?php
        $this->script();
    }
    
    
    public function render() {
        echo '<div class="wrap">';

        $this->show_navigation();
        $this->show_form();

        echo '</div>';
    }

    public function do_settings_sections( $page,$section_id ) {
	
        global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections ) || !isset( $wp_settings_sections[$page] ) )
			return;

        $section = $wp_settings_sections[$page][$section_id];
		if ( $section['title'] )
			echo "<h3>{$section['title']}</h3>\n";

		if ( $section['callback'] )
			call_user_func( $section['callback'], $section );

		if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) )
			return;
		
		echo '<table class="form-table">';
		do_settings_fields( $page, $section['id'] );
		echo '</table>';
	
}

    /**
     * Sanitize options callback for Settings API
     */
    public function sanitize_options( $options ) {
       
        foreach( $options as $option_slug => $option_value ) {
            
            $sanitize_callback = $this->cb_stack[$option_slug];

            // If callback is set, call it
            if ( $sanitize_callback ) {
                $options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
                continue;
            }

            // Treat everything that's not an array as a string
            if ( !is_array( $option_value ) ) {
                $options[ $option_slug ] = sanitize_text_field( $option_value );
                continue;
            }
        }
        return $options;
    }

    /**
     * Tabbable JavaScript codes
     *
     * This code uses localstorage for displaying active tabs
     */
    public function script() {
        ?>
        <script>
            jQuery(document).ready(function($) {
                // Switches option sections
                $('.settings-section-tab').hide();
                var activetab = '';
                //check for the active tab stored in the local storage
                if (typeof(localStorage) != 'undefined' ) {
                    activetab = localStorage.getItem('activetab');
                }
                //if active tab is set, show it
                if (activetab != '' && $(activetab).length ) {
                    $(activetab).fadeIn();
                } else {
                    //otherwise show the first tab
                    $('.settings-section-tab:first').fadeIn();
                }
                
                $('.group .collapsed').each(function(){
                    $(this).find('input:checked').parent().parent().parent().nextAll().each(
                    function(){
                        if ($(this).hasClass('last')) {
                            $(this).removeClass('hidden');
                            return false;
                        }
                        $(this).filter('.hidden').removeClass('hidden');
                    });
                });

                if (activetab != '' && $(activetab + '-tab').length ) {
                    $(activetab + '-tab').addClass('nav-tab-active');
                }
                else {
                    $('.nav-tab-wrapper a:first').addClass('nav-tab-active');
                }
                
                //on click of the tab navigation
                $('.nav-tab-wrapper a').click(function(evt) {
                    $('.nav-tab-wrapper a').removeClass('nav-tab-active');
                    $(this).addClass('nav-tab-active').blur();
                    var clicked_group = $(this).attr('href');
                    if (typeof(localStorage) != 'undefined' ) {
                        localStorage.setItem("activetab", $(this).attr('href'));
                    }
                    $('.settings-section-tab').hide();
                    $(clicked_group).fadeIn();
                    evt.preventDefault();
                });
            });
        </script>
        <?php
    }


}
endif;


if( !class_exists( 'OptionsBuddy_Settings_Manager' ) ):
/**
 * Settings Page store
 * Use it to add/retrieve settings pages
 * Not required to make the script function but just a convenienence
 * 
 */
class OptionsBuddy_Settings_Manager {
    /**
     *
     * @var string url of the current directory 
     */
    private $url;
    
    /**
     *
     * @var type 
     */
    private static $instance;
    /**
     *
     * @var OptionsBuddy_Settings_Page 
     */
    
    private $pages = array();
    
    private function __construct() {
        
        
        if( !isset( $this->url ) ) {
            //we need to find the directory of the options-buddy
            //it could be inside a theme or a plugin we  don't know
            $path= dirname( __FILE__ );

            //for windows
            $path= str_replace( '\\', '/', $path );

            $abspath = str_replace( '\\', '/', ABSPATH );
        
            //find relative path
            $rel_path = str_replace( $abspath, '',$path );

            $this->url = trailingslashit( site_url('/') . $rel_path );
        }
        add_action( 'admin_enqueue_scripts', array( $this, 'load_js' ) );
    }
    /**
     * 
     * @return OptionsBuddy_Settings_Manager
     */
    public static function get_instance() {
    
        if( !isset( self::$instance ) )
            self::$instance = new self();
        
        return self::$instance;
        
    }
    /**
     * 
     * @param type $page_name the slug for page
     * @param OptionsBuddy_Settings_Page $page
     * @return OptionsBuddy_Settings_Page
     */
    public function add_page( $page_name, $page=false ){
        
        if( !$page )
            $page = new OptionsBuddy_Settings_Page( $page_name );
		
        $this->pages[$page_name] = $page;
        
        return $page;
        
    }
    /**
     * 
     * @param string $page_name
     * @return OptionsBuddy_Settings_Page
     */
    public function get_page( $page_name ) {
		
        //if the page exists in the store, let us return it
        if( isset( $this->pages[$page_name] ) )
            return $this->pages[$page_name];
        
       //otherwise return a new page
       
        return $this->add_page($page_name);
    }
    
       
    
         /**
     * Enqueue scripts and styles
     */
    public function load_js() {
       wp_enqueue_media();
       wp_enqueue_script( 'optionsbuddy-media-uploader', $this->url . '_inc/uploader.js', array( 'jquery' ) );
              
    }
}

OptionsBuddy_Settings_Manager::get_instance();
endif;