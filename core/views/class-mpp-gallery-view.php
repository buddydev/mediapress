<?php

/**
 * Superclass for all the gallery views
 * A valid gallery view must extend this
 * 
 * A Gallery view supports only one media type but may support one or more componemnt( members/sitewide/groups etc)
 */
abstract class MPP_Gallery_View {
	/**
	 * Unique identifier for this view
	 * Each view must have a unique identifier that identifies it for the given media type uniquely
	 * 
	 * @var string unique identifier 
	 */
	protected $id = '';
	protected $name = '';
	
	private $widget_settings = array();
	private $shortcode_settings = array();
	private $gallery_settings = array();
	
	/**
	 * Associative array()
	 *	 
	 * @var array 
	 *	@type callable widget_display used to display the widget if this view is used
	 *	@type callable widget_settings used to display the settings inside admin widget area if this view is selected
	 *	@type callable widget_update_settings called if this view is selected in widget
	 *	@type callable shortcode_display used when this view is selected for shortcode
	 *  @type callable shortcode_settings Used when rendering shortcode UI for the selecting of Gallery
	 *	@type callable shortcode_update_setting used for saving shortcode info
	 *	@type gallery_display used to render single gallery view
	 *	@type gallery_settings used to render settings for this view on single gallery admin edit page
	 *	@type gallery_update_settings used to update gallery settings when this view is enabled
	 * 
	 *	
	 *	
	 *  
	 */
	protected $callbacks = array();
	
	protected function __construct( $args = null ) {
		
		if( ! empty( $args['widget'] ) ) {
			$this->widget_settings = $args['widget'];
			
		}
		
		if( ! empty( $args['gallery'] ) ) {
			$this->gallery_settings = $args['gallery'];
		}
		
		if( ! empty( $args['shortcode'] ) ) {
			$this->shortcode_settings = $args['shortcode'];
		}
		
		
	}

	
	/**
	 * Get unique view id
	 * 
	 * @return string unique view ID
	 */
	public function get_id() {
		return $this->id;
	}
	
	public function get_name() {
		return $this->name;
	}
	public function has_widget_view() {
		
		if( ! empty( $this->widget_settings ) && $this->widget_settings['display'] ) {
			return true;
		}
		return false;
	}
	
	/**
	 * Does this view provides settinsg for widget
	 * 
	 * @return boolean
	 */
	public function has_widget_settings() {
		
		if( ! empty( $this->widget_settings ) && $this->widget_settings['settings'] ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Does this view provides settinsg for widget
	 * 
	 * @return boolean
	 */
	public function has_widget_update_settings() {
		
		if( ! empty( $this->widget_settings ) && $this->widget_settings['update'] ) {
			return true;
		}
		
		return false;
	}
		
	public function has_gallery_view() {
		
		if( ! empty( $this->gallery_settings ) && $this->gallery_settings['display'] ) {
			return true;
		}
		return false;
	}
	
	/**
	 * Does this view provides settings for Gallery
	 * 
	 * @return boolean
	 */
	public function has_gallery_settings() {
		
		if( ! empty( $this->gallery_settings ) && $this->gallery_settings['settings'] ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Does this view provides settinsg for widget
	 * 
	 * @return boolean
	 */
	public function has_gallery_update_settings() {
		
		if( ! empty( $this->gallery_settings ) && $this->gallery_settings['update'] ) {
			return true;
		}
		
		return false;
	}
	
	
	public function has_shortcode_view() {
		
		if( ! empty( $this->shortcode_settings ) && $this->shortcode_settings['display'] ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Does this view provides settinsg for shortcode(Useful when we implement the UI )
	 * 
	 * @return boolean
	 */
	public function has_shortcode_settings() {
		
		if( ! empty( $this->shortcode_settings ) && $this->shortcode_settings['settings'] ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Does this view provides settinsg for widget
	 * 
	 * @return boolean
	 */
	public function has_shortcode_update_settings() {
		
		if( ! empty( $this->shortcode_settings ) && $this->shortcode_settings['update'] ) {
			return true;
		}
		
		return false;
	}
	/**
	 * Callback for single gallery display
	 * 
	 * @param type $gallery_id
	 */
	public function callback_gallery_display( $gallery_id ) {
		
	}
	
	/**
	 * Callback for single gallery settings
	 * 
	 * @param type $gallery_id
	 */
	public function callback_gallery_settings( $gallery_id ) {
		
	}
	
	public function callback_gallery_update( $gallery_id ) {
		
	}
	
	/**
	 * Display single gallery
	 * 
	 * @param type $gallery
	 */
	public function display( $gallery ) {
		
	}
}