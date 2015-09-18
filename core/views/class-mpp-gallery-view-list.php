<?php
/**
 * Shows all the items as a list
 * 
 */
class MPP_Gallery_View_List extends MPP_Gallery_View {
	
	private static $instance = null;
	
	protected function __construct() {
		parent::__construct();
		$this->id = 'list';
		$this->name = __( 'List View', 'mediapress' );
		
		
	}
	
	public static function get_instance() {
		
		if( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function display( $gallery ) {
		
		mpp_get_template( 'gallery/views/list.php' );
	}

}