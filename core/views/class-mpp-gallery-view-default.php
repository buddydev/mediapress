<?php
/**
 * Default Grid View
 */
class MPP_Gallery_View_Default extends MPP_Gallery_View {
	
	private static $instance = null;
	
	protected function __construct() {
		parent::__construct();
		$this->id = 'default';
		$this->name = __( 'Default Grid layout', 'mediapress' );
	}
	
	public static function get_instance() {
		
		if( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function display( $gallery ) {
		
		mpp_get_template( 'gallery/views/grid.php' );
	}
	
	
}