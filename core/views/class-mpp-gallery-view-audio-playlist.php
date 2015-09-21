<?php
/**
 * Default Grid View
 */
class MPP_Gallery_View_Audio_Playlist extends MPP_Gallery_View {
	
	private static $instance = null;
	
	protected function __construct() {
		parent::__construct();
		$this->id = 'playlist';
		$this->name = __( 'Audio Playlist', 'mediapress' );
		
	}
	
	public static function get_instance() {
		
		if( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function display( $gallery ) {
		
		mpp_get_template( 'gallery/views/audio-playlist.php');
	}
	
	
}