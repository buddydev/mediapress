<?php
$media = mpp_get_current_media();
if ( ! $media ) {
	return;
}

$src = mpp_get_media_src( '', $media );

$ext = mpp_get_file_extension( $src );
// valid extension.
if ( $ext && mpp_is_doc_viewer_enabled( $media ) && mpp_doc_viewer_supports_file_type( 'gdoc', $ext ) ) {
	// $ext = strtolower( $ext );
	// for doc viewer, we will use google doc viewer for.

	// IF IT IS PDF, PPT OR TIFF USE THE GOOGLE VIEWER
	// USE Proper URL scheme for Viewer.
	$url = is_ssl() ? "https://docs.google.com/viewer?url=" : "http://docs.google.com/viewer?url=";
	$url = $url . urlencode( $src );

	// should we validate if the type is supported by viewer?
	// see for supported type
	// https://support.google.com/drive/answer/2423485?hl=en&p=docs_viewer&rd=1
	// and check this for more details
	// https://docs.google.com/viewer.
	$html = "<iframe src='" . $url . "&embedded=true' style='border: none;'></iframe>";
} else {
	// Needs more improvement.
	$html = sprintf( '<a href="%s">%s</a>', esc_attr( $src ), esc_attr( basename( mpp_get_media_path('', $media ) ) ) );
}

echo $html;