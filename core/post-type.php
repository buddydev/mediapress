<?php
/**
 * MPP Post type helper
 * 
 * This class registers custom post type and taxonomies
 * 
 */
class MPP_Post_Type_Helper {
    /**
     *
     * @var MPP_Post_Type_Helper
     */
    private static $instance;
    
    
    private function __construct() {
        
        
        //register post type
        
        //register taxonomy
        
        //associate post type to taxonomy
        
        add_action( 'init', array( $this, 'init'), 1 );
    }
    /**
     * 
     * @return MPP_Post_Type_Helper
     */
    public static function get_instance() {
        
        if( !isset( self::$instance ) ) {
            
            self::$instance = new self();
            
        }    
        
        return self::$instance;
    }
    
    public function init(){
        
        $this->register_post_types();
        $this->register_taxonomies();
        
    }
    public function register_post_types(){
            
        $label = _x( 'Gallery', 'The Gallery Post Type Name', 'mediapress' );
        $label_plural = _x( 'Galleries', 'The Gallery Post Type Plural Name', 'mediapress' );
        
        $_labels = array(
                'name'  =>  $label_plural,
                'singular_name'         => $label,
                'menu_name'             => __(  'MediaPress' ),
                'name_admin_bar'        => __( 'MediaPress'),
                'all_items'             => __(  'Galleries'),
                'add_new'               => __(  'New Gallery'),
                'add_new_item'          => __(  'New Gallery'),
                'edit_item'             => __( 'Edit Gallery'),
                'new_item'              => __( 'New Gallery'),
                'view_item'             => __( 'View Gallery'),
                'search_items'          => __( 'search Galleries'),
                'not_found'             => __(  'No Galleries found!'),
                'not_found_in_trash'    => __( 'No Galleries found in trash!'),
                'parent_item_colon'     => __( 'Parent Gallery')
            
        );// $this->_get_labels( $label, $label_plural );

        $args = array(
            
                'public'                => true,
                'publicly_queryable'    => true,
                'exclude_from_search'   => true,
                'show_ui'               => true,
                'show_in_menu'          => true,
                'menu_position'         => 10,
                'menu_icon'             => null,//sorry I don't have one
                'show_in_admin_bar'     => true,
                'capability_type'       => 'post',
                'has_archive'           => true,
                'rewrite'               => array(
                                            'with_front'    => false
                                        )
                );

        

        //$args = wp_parse_args($default);
    

        $args['labels'] = $_labels;

        register_post_type( mpp_get_gallery_post_type(), $args );
        
        add_rewrite_endpoint('edit', EP_PAGES );
        
        //for media
    
      /*  
        $label = _x( 'Gallery Media', 'The Gallery Media Post Type Name', 'mediapress' );
        $label_plural = _x( 'Gallery Media', 'The Gallery Media Post Type Plural Name', 'mediapress' );
        
        $_labels = $this->_get_labels( $label, $label_plural );   
        

        $args['labels'] = $_labels;
        $args['menu_position'] = 15;//$_labels;
        
*/
       // register_post_type(mpp_get_media_post_type(), $args);        
        
        
        
    }
    
    public function register_taxonomies(){
        //register type
        //register status
        // register component
        
       $this->register_taxonomy( mpp_get_type_taxname(), array(

               'label'          => _x( 'Media Type', 'Gallery Media Type', 'mediapress' ),

               'labels'         => _x( 'Media Types', 'Gallery Media Type Plural Name', 'mediapress' ),

               'hierarchical'   => false

               ) );
        
       
        
        $this->register_taxonomy( mpp_get_component_taxname(), array(

               'label'          => _x( 'Component', 'Gallery Associated Type', 'mediapress' ),

               'labels'         => _x( 'Components', 'Gallery Associated Component Plural Name', 'mediapress' ),

               'hierarchical'   => false

               ) );
        $this->register_taxonomy( mpp_get_status_taxname(), array(

               'label'          => _x( 'Gallery Status', 'Gallery privacy/status Type', 'mediapress' ),

               'labels'         => _x( 'Galery Statuses', 'Gallery Privacy Plural Name', 'mediapress' ),

               'hierarchical'   => false

               ) );
        
       
        
        register_taxonomy_for_object_type( mpp_get_type_taxname(), mpp_get_gallery_post_type() );
        
        register_taxonomy_for_object_type( mpp_get_component_taxname(), mpp_get_gallery_post_type() );
        register_taxonomy_for_object_type( mpp_get_status_taxname(), mpp_get_gallery_post_type() );
        
        register_taxonomy_for_object_type( mpp_get_type_taxname(), mpp_get_media_post_type() );
        register_taxonomy_for_object_type( mpp_get_component_taxname(), mpp_get_media_post_type() );
        register_taxonomy_for_object_type( mpp_get_status_taxname(), mpp_get_media_post_type() );
    }
    
    public function associate_tax_to_post_type(){
        
        
    }
     
    public function register_taxonomy( $taxonomy, $args ){

        extract( $args );

        if( empty( $taxonomy ) )
            return false;

        $labels = self::_get_tax_labels( $label, $labels );
    
        if( empty( $slug ) )
            $slug = $taxonomy;

        register_taxonomy( $taxonomy, false,
                array(

                    'hierarchical'      => $hierarchical,

                    'labels'            => $labels,

                    'public'            => true,
                    'show_in_menu'      => false,
                    'show_in_nav_menus' => false,

                    'show_ui'           => false,

                    'show_tagcloud'     => true,
                    'capabilities'      => array(
                        'manage_terms'  => 'manage_categories',
                        'edit_terms'    => 'manage_categories',
                        'delete_terms'  => 'manage_categories',
                        'assign_terms'  => 'read'//allow subscribers to do it

                    ),


                    'update_count_callback'   => '_update_post_term_count',

                    'query_var'               => true,

                    'rewrite'               => array(

                        //  'slug' => $slug,

                          'with_front'=>true,

                          'hierarchical'=>$hierarchical

                          ),

      ));

      mediapress()->taxonomies[$taxonomy] = $args;

    }

   //label builder for easy use

    public function _get_tax_labels( $singular_name, $plural_name ){

        $labels = array(
                'name'                          => $plural_name,

                'singular_name'                 => $singular_name,

                'search_items'                  => sprintf( __( 'Search %s',  'mediapress' ), $plural_name ),

                'popular_items'                 =>  sprintf( __( 'Popular %s', 'mediapress' ), $plural_name ),

                'all_items'                     =>  sprintf( __( 'All %s', 'mediapress' ), $plural_name ),

                'parent_item'                   =>  sprintf( __( 'Parent %s', 'mediapress' ), $singular_name ),

                'parent_item_colon'             =>  sprintf( __( 'Parent %s:', 'mediapress' ), $singular_name ),

                'edit_item'                     =>  sprintf( __( 'Edit %s', 'mediapress' ), $singular_name ),

                'update_item'                   =>  sprintf( __( 'Update %s', 'mediapress' ), $singular_name ),

                'add_new_item'                  =>  sprintf( __( 'Add New %s', 'mediapress' ), $singular_name ),

                'new_item_name'                 =>  sprintf( __( 'New %s Name', 'mediapress' ), $singular_name ),

                'separate_items_with_commas'    =>  sprintf( __( 'Separate %s with commas', 'mediapress' ), $plural_name ),

                'add_or_remove_items'           =>  sprintf( __( 'Add or Remove %s', 'mediapress' ), $plural_name ),

                'choose_from_most_used'         =>  sprintf( __( 'Choose from the most used %s', 'mediapress' ), $plural_name )

                //menu_name=>'' //nah let us leave it default



            );

       

       return $labels;

    } 
    
    
}

MPP_Post_Type_Helper::get_instance();