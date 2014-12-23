<?php
/**
 * Galler Listing shortcode
 */
add_shortcode( 'mpp-gallery', 'mpp_gallery_shortcode' );

function mpp_gallery_shortcode( $atts = null, $content = '' ){
    //allow everything that can be done to be passed via this shortcode
    
        $defaults = array(
                'type'          => false, //gallery type, all,audio,video,photo etc
                'id'            => false, //pass specific gallery id
                'in'            => false, //pass specific gallery ids as array
                'exclude'       => false, //pass gallery ids to exclude
                'slug'          => false,//pass gallery slug to include
                'status'        => false, //public,private,friends one or more privacy level
                'component'     => false, //one or more component name user,groups, evenets etc
                'component_id'  => false,// the associated component id, could be group id, user id, event id
                'per_page'      => false, //how many items per page
                'offset'        => false, //how many galleries to offset/displace
                'page'          => false,//which page when paged
                'nopaging'      => false, //to avoid paging
                'order'         => 'DESC',//order 
                'orderby'       => 'date',//none, id, user, title, slug, date,modified, random, comment_count, meta_value,meta_value_num, ids
                //user params
                'user_id'       => false,
                'include_users' => false,
                'exclude_users' => false,//users to exclude
                'user_name'     => false,
                'scope'         => false,
                'search_terms'  => '',
            //time parameter
                'year'          => false,//this years
                'month'         => false,//1-12 month number
                'week'          => '', //1-53 week
                'day'           => '',//specific day
                'hour'          => '',//specific hour
                'minute'        => '', //specific minute
                'second'        => '',//specific second 0-60
                'yearmonth'     => false,// yearMonth, 201307//july 2013
                'meta_key'		=>'',
                'meta_value'	=>'',
               // 'meta_query'=>false,
                'fields'    => false,//which fields to return ids, id=>parent, all fields(default)
				'column'	=> 4,
        );
        
    $atts = shortcode_atts( $defaults, $atts );
    
    if( !$atts['meta_key'] ){
        
        unset( $atts['meta_key'] );
        unset( $atts['meta_value'] );
    }
    
	$shortcode_column = $atts['column'];
	mpp_shortcode_save_gallery_data( 'column', $shortcode_column );
	
	unset( $atts['column']);
	
    $query = new MPP_Gallery_Query( $atts );
    
    ob_start();
    
    echo '<div class="mpp-container mpp-shortcode-wrapper mpp-shortcode-gallery-wrapper"><div class="mpp-g mpp-item-list mpp-gallery-list mpp-shortcode-item-list mpp-shortcode-gallery-list"> ';
    
    while( $query->have_galleries() ): $query->the_gallery();
    
        mpp_get_template_part( 'shortcodes/gallery', 'entry' );//shortcodes/gallery-entry.php
    
    
    endwhile;
    mpp_reset_gallery_data();
    echo '</div></div>';   
    
    $content = ob_get_clean();
	
	mpp_shortcode_reset_gallery_data( 'column' );
    
    return $content;
}