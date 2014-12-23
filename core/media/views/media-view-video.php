<?php

class MPP_Media_View_Video extends MPP_Media_View{
	
	
	
	public function get_html( $media ) {
		
		if( ! $media )
			return '';
		
		$html = '';
		
		$args = array(
				'src' => mpp_get_media_src( $media ),
				'poster' => mpp_get_media_src( 'thumbnail', $media ),

		);
		return  wp_video_shortcode( $args );
	
		
	}
	
	
}