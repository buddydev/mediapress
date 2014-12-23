<?php
/**
 * Storage Manager superclass
 * 
 * All the storage managers 
 */
abstract class MPP_Storage_Manager{
    
    protected $component;
    protected $component_id;
    protected $gallery_id;
    
    abstract public function upload( $file, $args );
    abstract public function get_meta( $uploaded_info );
    abstract public function generate_metadata( $id, $file );
    
  
    public abstract function delete( $id );
	
    abstract public function get_used_space( $component, $component_id );
    
    public function get_url( $size, $id ){
		
		return $this->get_src( $size, $id );
	}
 
    
    /**
     * Get the absolute url to a media file
     * e.g http://example.com/wp-content/uploads/mediapress/members/1/xyz.jpg
     */
    public abstract function get_src( $type = '', $id = null );
    /**
     * Get the absolute file system path to the 
     */
    public abstract function get_path( $type = '', $id = null );
        /**
     * Setup uploader for uploading to a component?
     * @param type $component
     * @param type $component_id
     */
    public function setup_for( $component, $component_id ){

        $this->component = $component;
        $this->component_id = $component_id;

    }
   
    
    /**
     * Assume that the server can handle upload
     * Mainly used in case of local uploader for checking postmax size etc
     * 
     * @return boolean
     */
    public function can_handle() {
		
      return true;
		
    }
    
    
    
    
}

