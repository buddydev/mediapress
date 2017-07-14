<?php
class MPP_Media_Debugger {


	/**
	 * MPP_Media_Debugger constructor.
	 */
	public function __construct() {

		$this->setup();

	}

	private function setup() {
		add_filter( 'attachment_fields_to_edit', array( $this, 'info' ), 10, 2 );
	}

	public function info( $fields, $post ) {

		$html = $this->get_html( $post );

		$fields['mpp-media-debug-info'] = array(
			'label'     => __( 'Debug Info', 'mediapress' ),
			'input'     => 'html',
			'html'      => $html,
		);

		return $fields;
	}


	public function get_html( $media ) {

		$media = mpp_get_media( $media );

		$html = '<table>';
		$html .='<tr><th>' . __( 'Component', 'mediapress' ) . '</th><td>' . $media->component .'</td></tr>';
		$html .='<tr><th>' . __( 'Type', 'mediapress' ) . '</th><td>' . mpp_get_type_singular_name( $media->type ) .'</td></tr>';
		$html .='<tr><th>' . __( 'Status', 'mediapress' ) . '</th><td>' . $media->status .'</td></tr>';
		$html .='<tr><th>' . __( 'Is Orphan Media', 'mediapress' ) . '</th><td>' . $this->get_yesno( $media->is_orphan ) .'</td></tr>';
		$html .='<tr><th>' . __( 'Storage Method', 'mediapress' ) . '</th><td>' . mpp_get_storage_method( $media->id ) .'</td></tr>';
		$html .='<tr><th>' . __( 'Valid Media?', 'mediapress' ) . '</th><td>' . $this->get_yesno( $media->is_mpp_media ) .'</td></tr>';

		$html .= '</table>';

		return $html;
	}

	public function get_yesno( $anything ) {

		if ( $anything ) {
			return __ ( 'Yes', 'mdiapress' );
		}

		return __( 'No', 'mediapress' );
	}
}

new MPP_Media_Debugger();