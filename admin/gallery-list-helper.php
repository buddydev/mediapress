<?php

class MPP_Gallery_List_Helper {
	
	private $post_type = '';
	
	public function __construct() {
		
		$this->post_type = mpp_get_gallery_post_type();
		
		add_filter( "manage_edit-{$this->post_type}_columns", array( $this, 'add_cols' ) ) ;
		
		add_action( "manage_{$this->post_type}_posts_custom_column", array( $this, 'display_cols' ), 10, 2 );
		
		//add sortable cols
		add_filter( "manage_edit-{$this->post_type}_sortable_columns", array( $this, 'sortable_cols' ) );
		//update query for it
		
		add_action( 'pre_get_posts', array( $this, 'sort_list' ) );
		
		//filter out the quickedit
		
		add_filter( 'post_row_actions', array( $this, 'filter_actions' ), 10, 2 );
		
	}
	
	
	
	public function add_cols( $columns ) {
		
		unset( $columns['date'] );
		
		$columns['type']		= __( 'Type', 'mediapress' );
		$columns['status']		= __( 'Status', 'mediapress' );
		$columns['component']	= __( 'Component', 'mediapress' );
		
		$columns['user_id']		= __( 'Created By:', 'mediapress' );
		
		$columns['media_count'] = __( 'No. of Media', 'mediapress' );
		$columns['date']		= __( 'Date', 'mediapress' );
		
	
		return $columns;
	}
	
	
	public function display_cols( $col, $post_id ) {
		
		$allowed = array( 'type', 'status', 'component', 'user_id', 'media_count' );
		
		if( ! in_array( $col, $allowed ) )
			return $col;
		
		$gallery = mpp_get_gallery( get_post( $post_id ) );
		
		switch( $col ) {
			
			case 'type':
				echo $gallery->type;
				break;
			case 'status':
				echo $gallery->status;
				break;
			case 'component':
				echo $gallery->component;
				break;
			
			case 'media_count':
				echo $gallery->media_count;
				break;
			
			case 'user_id':
				echo bp_core_get_userlink( $gallery->user_id );
				break;
			
		}
		
	}
	
	public function sortable_cols( $cols ) {
		
		$cols['type']			= 'type';
		$cols['status']			= 'status';
		$cols['component']		= 'component';
		
		$cols['user_id']		= 'user_id';
		$cols['media_count']	= 'media_count';
		
		return $cols;
		
	}
	
	public function sort_list( WP_Query $query ) {
		
		if( ! mpp_admin_is_gallery_list() )
			return ;
		
		//check if the post type 
		if( ! $query->is_main_query() || $query->get('post_type') != mpp_get_gallery_post_type() )
			return;
		
		//if we are here, we may need to sort
		$orderby = isset( $_REQUEST['orderby'] )? $_REQUEST['orderby'] : '';
		
		$sort_order = isset( $_REQUEST['order'] )? $_REQUEST['order']:'';
		
		if( ! $orderby || ! $sort_order )
			return;
		
		if( $orderby == 'user_id' ) {
		
			$query->set('orderby', 'author' );
			
		}elseif( $orderby == 'media_count' ) {
			
			$query->set('meta_key', '_mpp_media_count');
			$query->set('orderby', 'meta_value_num' );
		}
			
		
		$query->set( 'order', $sort_order );
		
	}
	public function filter_actions( $actions, $post ) {
		
		if( $post->post_type != mpp_get_gallery_post_type() )
			return $actions;
		
		unset( $actions['inline hide-if-no-js'] );
		
		return $actions;
	}
	
	
}
new MPP_Gallery_List_Helper();