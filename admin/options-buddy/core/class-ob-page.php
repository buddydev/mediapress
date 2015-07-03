<?php


if( ! class_exists( 'OptionsBuddy_Settings_Page' ) ):
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
    private $page = '';
    
    /**
     *
     * @var string the option name to be stored in options table
     * 
     * If using individual field name as option is not enabled, this is used to store all the options in a multidimensional array
     * 
     */
    private $option_name = '';
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
    public function use_unique_option() {
		
        $this->use_unique_option = true;
        return $this;
    }
    
    public function use_single_option() {
		
        $this->use_unique_option = false;
        
        if( ! isset( $this->option_name ) )
            $this->set_option_name( $this->page );
       
        return $this;
    }
    
    /**
     * 
     * @return bool are we using unique options to store each field
     */
    public function using_unique_option() {
        return $this->use_unique_option;
    }
    
    
    public function set_network_mode() {
        
        $this->is_network_mode = true;
		
        return $this;
    }
    
    public function is_network_mode() {
        
        return $this->is_network_mode;
    }
	
    public function set_bp_mode() {
        
        $this->is_bp_mode = true;
        return $this;
    }
    
    public function is_bp_mode() {
        
        return $this->is_bp_mode;
    }
    
    public function reset_mode() {
		
        $this->is_network_mode = false;
        $this->is_bp_mode = false;
        
        return $this;
    }
    /**
     * Set an option name if you want. It is only used if using_unique_option is disabled
     * @param type $option_name
     * @return OptionsBuddy_Settings_Page
     */
    public function set_option_name( $option_name ) {
        
        $this->option_name = $option_name;
        return $this;
    }
    /**
     * Get the option name
     * 
     * @return string
     */
    public function get_option_name() {
        
        return $this->option_name ;
    }
    
    public function set_optgroup( $optgroup ) {
		
        $this->optgroup = $optgroup;
    }
	
    public function get_optgroup() {
		
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
    public function add_section( $id, $title, $desc = false ) {
        
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
    public function get_section( $id ) {
		
        return $this->sections[$id];
        
    }
    /**
     * mainly used for generating the settings form
     * @return type
     */
    public function get_page() {
        
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
        if( ! $this->using_unique_option() ) {

			if ( false == get_option( $global_option_name ) ) {
				add_option( $global_option_name );
			}
        }
        //register settings sections
        //for every section
        foreach ( $this->sections as  $section ) {
                
            //for individual section
                       
            if ( $section->get_disc()  ) {
				
                $desc = '<div class="inside">'.$section->get_disc() . '</div>';
                $callback = create_function('', 'echo "' . str_replace( '"', '\"', $desc ). '";' );
				
            } else {
				
                $callback = '__return_false';
            }

            add_settings_section( $section->get_id(), $section->get_title(), $callback, $this->get_page() );
        
             
            //register settings fields
            foreach ( $section->get_fields() as $field ) {
                 
				$option_name = $global_option_name . '[' . $field->get_name() . ']';
				//when using local 
				if( $this->using_unique_option() ) {

				   if ( false == get_option( $field->get_name() ) ) {
					   add_option( $field->get_name() );
				   }
				   //override option name
				   $option_name = $field->get_name();
                   
                   
                }
       
                $args = array(
                    'section'		=> $section->get_id(),
                    'std'			=> $field->get_default(),
                    'option_key'	=> $option_name,
                    'value'			=> $this->get_option( $field->get_id(),  $field->get_default() ),
                    
                );
                
                $this->cb_stack[$field->get_id()] = $field->get_sanitize_cb() ;
                
                add_settings_field( $option_name, $field->get_label(), array( $field, 'render' ), $this->get_page(), $section->get_id(), $args );
                
                 //when using local 
                if( $this->using_unique_option() ) {
                     
                    register_setting( $this->get_optgroup(), $field->get_name(), array( $field, 'sanitize' ) );
                }
                
            }
        
       
            
			//when using only one option to store all values
		   if( ! $this->using_unique_option() ) {


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
        
        if( $this->is_network_mode() ) {
			
            $function_name = 'get_site_option';
			
        } elseif( $this->is_bp_mode() ) {
			
            if( function_exists( 'bp_get_option' ) )
                $function_name = 'bp_get_option';
            
        }
        
        if( ! $this->using_unique_option() ) {
            
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
                    
						<?php do_action( 'optionsbuddy_form_top_' . $section->get_id(), $section ); ?>

						<?php $this->do_settings_sections( $this->get_page(),$section->get_id() ); ?>
						<?php do_action( 'optionsbuddy_form_bottom_' . $section->get_id(), $section ); ?>

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

    public function do_settings_sections( $page, $section_id ) {
	
        global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections ) || !isset( $wp_settings_sections[$page] ) )
			return;

        $section = $wp_settings_sections[$page][$section_id];
		
		if ( $section['title'] )
			echo "<h3>{$section['title']}</h3>\n";

		if ( $section['callback'] && is_callable( $section['callback'] ) )
			call_user_func( $section['callback'], $section );

		if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[$page] ) || ! isset( $wp_settings_fields[$page][$section['id']] ) )
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
