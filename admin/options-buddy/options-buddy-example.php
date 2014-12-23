<?php

/**
 * Plugin Name: OptionsBuddy Example
 * Plugin URI: http://buddyDev.com
 * Description: An example showing the use of OptionsBuddy to generate new option page, add options to existing Settings Page
 * Author: Brajesh Singh
 * Author URI: http://BuddyDev.com
 * Version: 1.0
 */
require_once dirname( __FILE__ ) . '/class.options-buddy.php';


class OptionsBuddy_Example {
 
    private $setting_page_example;
    
    public function __construct() {
        //create a options page
        //make sure to read the code below
        $this->setting_page_example = new OptionsBuddy_Settings_Page('example_page');
        
        //by default,  example_page will be used as option name and you can retrieve all options by using get_option('example_page')
        //if you want use a different option_name, you can pass it to set_option_name method as below
        
        //$this->setting_page_example->set_option_name('my_new_option_name');
        //now all the options for example_page will be stored in the 'my_new_option_name' option and you can get it by using get_option('my_new_option_name')
        
        //if you don't want to group all the fields in single option and want to store each field individually in the option table, you can set that too as below
        // if you cann use_unique_option method, all the fields will be stored in individual option(the option name will be field name ) and 
        //you can retrieve them using get_option('field_name')
        
       // $this->setting_page_example->use_unique_option();
        
        //incase your mood changed and you want to use single option to store evrything, you can call this use_single_option method again
        //use single option is the default 
        //$this->setting_page_example->use_single_option();
        
        
        //if it pleases you, you can set the optgroup too, if you don't set,. it is same as the page name
        //$this->setting_page_example->set_optgroup('buddypress');
        //now, let us create an options page, what do you say
        
        add_action( 'admin_init', array($this, 'admin_init'));
        add_action( 'admin_menu', array($this, 'admin_menu') );
    }

    function admin_init() {

        //set the settings
        
        $page = $this->setting_page_example;
        //add_section
        //you can pass section_id, section_title, section_description, the section id must be unique for this page, section descriptiopn is optional
        $page->add_section('basic_section', __( 'Basic Settings' ), __('This is description for basic section'));
        //since option buddy allows method chaining, you can start adding field in the same line above using ad_field or add_fields to add multiple field
        //or you can add fields later to a section by calling get_section('section_id');
        
        //let us add a couple more section
        //each section title is used as tab name
        $page->add_section('advance_section', __( 'Advanced Settings'),__('This is an optional description of advanced section'));
        
        $page->add_section('other_section', __( 'Other Settings' ));//ok I have left description here
        
        
        /**
         * Let us adfd a section and some field in one statement
         */
        //4th section
        $page->add_section('new_section', 'Some New Section')->add_field(array(
            'name'=>'test_input',//the field name to identify it uniquely for this page 
            'label'=>'This is Test Input',//this is the label for the input element
            'type'=> 'text',// Please see OptionsBuddy_Settings_Field for all types allow, some are text|textarea|image|password|select|checkbox|radio|multicheckbox etc
            
        ))->add_field(array( //add another field
            
            'name'=>'what_is_in_a_type',
            'label'=>'Write Something' //if we don't specify type, the type is taken as text
        ));
        
        //now, if we want, we can fetch a section and add some fields to it
        //I am not feeling adventurous, so I will simpley copy the example from Tareq's code
        //link https://github.com/tareq1988/wordpress-settings-api-class/blob/master/settings-api.php#L68
        //and use here
        // 
        //add fields
        $page->get_section('basic_section')->add_fields(array( //remember, we registered basic section earlier
                array(
                    'name' => 'input_text1',
                    'label' => __( 'Text Input' ),//you already know it from previous example
                    'desc' => __( 'Text input description' ),// this is used as the description of the field
                    'type' => 'text',
                    'default' => 'Title',//and this is the default value
                    'sanitize_callback' => 'intval' //right, you are learning now, This is the callback used for vaidation of the field data
                ),
                array(
                    'name' => 'test_textarea1',
                    'label' => __( 'Textarea Input' ),
                    'desc' => __( 'Textarea description' ),
                    'type' => 'textarea'
                ),
                array(
                    'name' => 'test_checkbox1',
                    'label' => __( 'Checkbox' ),
                    'desc' => __( 'Checkbox Label' ),
                    'type' => 'checkbox'
                ),
                array(
                    'name' => 'test_radio1',
                    'label' => __( 'Radio Button' ),
                    'desc' => __( 'A radio button' ),
                    'type' => 'radio',
                    'options' => array(
                        'yes' => 'Yes',//key=>label
                        'no' => 'No'
                    )
                ),
                array(
                    'name' => 'test_multicheck1',
                    'label' => __( 'Multile checkbox' ),
                    'desc' => __( 'Multi checkbox description' ),
                    'type' => 'multicheck',
                    'options' => array(
                        'one' => 'One',
                        'two' => 'Two',
                        'three' => 'Three',
                        'four' => 'Four'
                    )
                ),
                array(
                    'name' => 'test_selectbox1',
                    'label' => __( 'A Dropdown' ),
                    'desc' => __( 'Dropdown description' ),
                    'type' => 'select',
                    'default' => 'no',
                    'options' => array(
                        'yes' => 'Yes',
                        'no' => 'No'
                    )
                ),
                array(
                    'name' => 'test_password', //don't get fooled, it will stored in options table as normal text, only displayed as password
                    'label' => __( 'Password' ),
                    'desc' => __( 'Password description'),
                    'type' => 'password',
                    'default' => ''
                ),
                array(
                    'name' => 'test_image_1',
                    'label' => __( 'Logo' ),
                    'desc' => __( 'Choose a logo' ),
                    'type' => 'image',//right, it will allow you to use the wp media uploader to selec an image
                    'default' => ''//you can specify a url to existing image if you want
                ),
                array(
                    'name' => 'test_image_2',
                    'label' => __( 'Background'),
                    'desc' => __( 'Upload a sweet background' ),
                    'type' => 'image',
                    'default' => ''
                )
            ));
        
        //so far, we have added a section
        //let us add some fields to section 2 too 
            $page->get_section('advance_section')->add_fields(array(
                array(
                    'name' => 'test_text2',
                    'label' => __( 'What is your Name' ),
                    'desc' => __( 'ooh, let us make you famous!' ),
                    'type' => 'text',
                    'default' => 'I am Bond, James Bond'
                ),
                array(
                    'name' => 'test_textarea2',
                    'label' => __( 'what do you do for living?' ),
                    'desc' => __( 'Ok, share it now, don\'t hesitate boy!'),
                    'type' => 'textarea'
                ),
                array(
                    'name' => 'checkbox2',
                    'label' => __( 'What describes you Best?' ),
                    'desc' => __( 'yup!, we will keep it secret!'),
                    'type' => 'multicheck',
                    'default'=>'crazy',
                    'options'=>array(
                        'mad'   =>__( 'I am mad'),
                        'crazy' =>__( 'I am Crazy too' ),
                        'simpleton'=> __('I am a mortal, boohooo!')  
                    )
                ),
                array(
                    'name' => 'test_radio2',
                    'label' => __( 'Which one suits you?'),
                    'desc' => __( 'say again!' ),
                    'type' => 'radio',
                    'default' => 'cheese',
                    'options' => array(
                        'cheese' => __( 'I want Cheese'),
                        'cake' => __( 'I want Cake' )
                    )
                ),
              
             
            ));
            
            $page->get_section('other_section')->add_fields( array(
                array(
                    'name' => 'test_text3',
                    'label' => __( 'Text Input' ),
                    'desc' => __( 'Text input description' ),
                    'type' => 'text',
                    'default' => 'Title'
                ),
                array(
                    'name' => 'test_textarea4',
                    'label' => __( 'Textarea Input' ),
                    'desc' => __( 'Textarea description' ),
                    'type' => 'textarea'
                ),
                array(
                    'name' => 'test_checkbox4',
                    'label' => __( 'Checkbox'),
                    'desc' => __( 'Checkbox Label' ),
                    'type' => 'checkbox'
                ),
                array(
                    'name' => 'test_radio4',
                    'label' => __( 'Radio Button' ),
                    'desc' => __( 'A radio button' ),
                    'type' => 'radio',
                    'options' => array(
                        'yes' => 'Yes',
                        'no' => 'No'
                    )
                ),
                array(
                    'name' => 'test_multicheck4',
                    'label' => __( 'Multile checkbox' ),
                    'desc' => __( 'Multi checkbox description' ),
                    'type' => 'multicheck',
                    'options' => array(
                        'one' => 'One',
                        'two' => 'Two',
                        'three' => 'Three',
                        'four' => 'Four'
                    )
                ),
                array(
                    'name' => 'test_selectbox4',
                    'label' => __( 'A Dropdown'),
                    'desc' => __( 'Dropdown description' ),
                    'type' => 'select',
                    'options' => array(
                        'yes' => 'Yes',
                        'no' => 'No'
                    )
                ),
                array(
                    'name' => 'password4',
                    'label' => __( 'Password' ),
                    'desc' => __( 'Password description'),
                    'type' => 'password',
                    'default' => ''
                ),
                array(
                    'name' => 'test_file4',
                    'label' => __( 'File'),
                    'desc' => __( 'File description' ),
                    'type' => 'image',
                    'default' => ''
                )
            ));
       
        $page->init();
        
    }

    function admin_menu() {
        add_options_page( 'OptionsBuddy', 'OptionsBuddy Example', 'delete_posts', 'options-buddy-example', array($this->setting_page_example, 'render') );
    }

    

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    



}

new OptionsBuddy_Example();