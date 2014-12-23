<?php

class MPP_Comment_Query {
    
    private $comments = array();
    
    private $post_id;
    
    private $comment_type = 'mpp-comment';
    
    private $comment_status = 'approved';
    
    private $comment_count = 0;
    private $current_comment = -1;
    
    private $in_the_loop = false;
    
    private $comment;
    
    function init() {
		$this->comments = array();
		unset($this->comment);
        
		
		$this->comment_count = 0;
		$this->current_comment = -1;
		$this->in_the_loop = false;
		
	}
    
    public function get_comments(){
        
        
        return $this->comments;
        
    }
    
    /**
	 * Iterate current comment index and return comment object.
	 *
	 * 
	 * @return object Comment object.
	 */
	function next_comment() {
		$this->current_comment++;

		$this->comment = $this->comments[$this->current_comment];
		return $this->comment;
	}

	/**
	 * Sets up the current comment.
	 *
	 * @uses do_action() Calls 'comment_loop_start' hook when first comment is processed.
	 */
	function the_comment() {
		global $comment;

		$comment = $this->next_comment();

		if ( $this->current_comment == 0 ) {
			do_action('comment_loop_start');
		}
	}

	/**
	 * Whether there are more comments available.
	 *
	 * Automatically rewinds comments when finished.
	 *
	
	 *
	 * @return bool True, if more comments. False, if no more posts.
	 */
	function have_comments() {
		if ( $this->current_comment + 1 < $this->comment_count ) {
			return true;
		} elseif ( $this->current_comment + 1 == $this->comment_count ) {
			$this->rewind_comments();
		}

		return false;
	}

	/**
	 * Rewind the comments, resets the comment index and comment to first.
	 *
	 * @since 2.2.0
	 * @access public
	 */
	function rewind_comments() {
        
		$this->current_comment = -1;
		if ( $this->comment_count > 0 ) {
			$this->comment = $this->comments[0];
		}
	}
    public function query($query_vars) {
        
        $this->init();
        
        $query_vars['type'] = $this->comment_type;
        $query_vars['status'] = $this->comment_status;
        
        //$comment_query = new WP_Comment_Query();
        
        $this->comments = get_comments( $query_vars );
        
        $this->comment_count = count( $this->comments );
        
    }
}

class MPP_Comment{
    
    public $id;
    
    public $content;
    
    public $user_id;
    
    public $post_id;
    
    public $user_domain;
    
    public $date_posted;
    
    public $parent_id;
    
}
/**
 * 
 * @param type $comment
 * @return MPP_Comment
 */
function mpp_comment_migrate( $comment ){
    
    $mpp_comment= new MPP_Comment;
    
    $mpp_comment->id = $comment->comment_ID;
    
    $mpp_comment->content = $comment->comment_content;
    
    $mpp_comment->user_id = $comment->user_id;
    
    $mpp_comment->post_id =$comment->comment_post_ID;
    
    $mpp_comment->user_domain = $comment->comment_author_url;
    
    $mpp_comment->date_posted = $comment->comment_date;
    
    $mpp_comment->parent_id =$comment->comment_parent;
    
    return $mpp_comment;
    
}


