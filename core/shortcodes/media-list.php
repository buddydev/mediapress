<?php
/**
 * Use [mpp-media ...] as shortcode
 * availabele options are
 * @type string $type possible values 'audio', 'video', 'photo', it specifies type of media
 * @type int $id the specific media id
 * @type array $in possible values are media ids as 
 */
add_shortcode( 'mpp-media', 'mpp_media_shortcode' );

function mpp_media_shortcode( $atts = null, $content = '' ) {
    //allow everything that can be done to be passed via this shortcode
    
        $defaults = array(
                'type'				=> false, //gallery type, all,audio,video,photo etc
                'id'				=> false, //pass specific media id
                'in'				=> false, //pass specific media ids as array
                'exclude'			=> false, //pass gallery ids to exclude
                'slug'				=> false, //pass gallery slug to include
                'status'			=> false, //public,private,friends one or more privacy level
                'component'			=> false, //one or more component name user,groups, evenets etc
                'component_id'		=> false, // the associated component id, could be group id, user id, event id
                'gallery_id'		=> false,
                'galleries'			=> false,
                'galleries_exclude'	=> false,
            
                'per_page'			=> false, //how many items per page
                'offset'			=> false, //how many galleries to offset/displace
                'page'				=> false,//which page when paged
                'nopaging'			=> false, //to avoid paging
                'order'				=> 'DESC',//order 
                'orderby'			=> 'date',//none, id, user, title, slug, date,modified, random, comment_count, meta_value,meta_value_num, ids
                //user params
                'user_id'			=> false,
                'include_users'		=> false,
                'exclude_users'		=> false,//users to exclude
                'user_name'			=> false,
                'scope'				=> false,
                'search_terms'		=> '',
            //time parameter
                'year'				=> false,//this years
                'month'				=> false,//1-12 month number
                'week'				=> '', //1-53 week
                'day'				=> '',//specific day
                'hour'				=> '',//specific hour
                'minute'			=> '', //specific minute
                'second'			=> '',//specific second 0-60
                'yearmonth'			=> false,// yearMonth, 201307//july 2013
                'meta_key'			=> '',
                'meta_value'		=> '',
				'column'			=> 4,
				'playlist'			=> 0,
               // 'meta_query'=>false,
                'fields'			=> false,//which fields to return ids, id=>parent, all fields(default)
        );
        
    $atts = shortcode_atts( $defaults, $atts );
    
    if( ! $atts['meta_key'] )  {
        
        unset( $atts['meta_key'] );
        unset( $atts['meta_value'] );
    }
    
	$cols		= $atts['column'];
	$playlist	= $atts['playlist'];
	$type		= $atts['type']; 
	
	unset( $atts['column'] );
	unset( $atts['playlist'] );
	
	$slug = '';
	
	if( $type && $playlist )
		$slug = "$type-playlist";
	elseif( $type )
		$slug = $type;
		
	mpp_shortcode_save_media_data( 'column', $cols );
	
    $query = new MPP_Media_Query( $atts );
	
	mpp_shortcode_save_media_data( 'query', $query );

    ob_start();
    
    echo '<div class="mpp-container mpp-shortcode-media-wrapper"><div class="mpp-g mpp-item-list mpp-media-list mpp-media-shortcode-list"> ';
    
        mpp_get_template_part( 'shortcodes/loop', $slug );//shortcodes/gallery-entry.php
       
    echo '</div></div>';   
    
    $content = ob_get_clean();
    
	mpp_shortcode_reset_media_data( 'query');
	mpp_shortcode_reset_media_data( 'column');
    return $content;
}