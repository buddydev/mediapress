<?php
/**
 * The benefit of using a view class is the control that it allows to change the view generation without worrying about template changes
 * 
 */
class MPP_Media_View_Audio extends MPP_Media_View {
	
	
	
	public function get_html( $media ) {
		
		if( ! $media )
			return '';
		
		$html = '';
		
			
		$args = array(
				'src'		=> mpp_get_media_src(),
				'loop'		=> false,
				'autoplay'	=> false,
			);

		return wp_audio_shortcode(  $args );
	
		
	}
	
	
}