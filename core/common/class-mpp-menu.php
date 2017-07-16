<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MediaPress menu Class
 */
class MPP_Menu {

	/**
	 * Array of menu items.
	 *
	 * @var array menu items array(multi dimensional)
	 */
	protected $items = array();

	/**
	 * Add a new menu item
	 *
	 * @param array $args array of item args.
	 *
	 * @return boolean
	 */
	public function add_item( $args ) {

		$default = array(
			'slug'     => '',
			'label'    => '',
			'action'   => '',
			'url'      => '',
			'callback' => 'mpp_is_menu_item_visible', // we might have used capability here.
		);

		$args = wp_parse_args( $args, $default );

		if ( ! $args['action'] || ! $args['slug'] || ! $args['label'] ) {
			return false;
		}

		$this->items[ $args['slug'] ] = $args;
	}

	/**
	 * Remove a Menu Item from the current Menu Item stack
	 *
	 * @param string $slug item identifier.
	 */
	public function remove_item( $slug ) {

		$item = $this->items[ $slug ];

		unset( $this->items['slug'] );
	}

	/**
	 * Render the Menu
	 *
	 * @param MPP_Gallery $gallery Gallery object.
	 * @param string      $selected selected item.
	 */
	public function render( $gallery, $selected = '' ) {

		$items = apply_filters( 'mpp_pre_render_gallery_menu_items', $this->items, $gallery );

		$html = array();

		foreach ( $items as $item ) {
			$class = '';
			$url   = $item['url'];
			// For visibility, check for the callback return value.
			if ( is_callable( $item['callback'] ) && call_user_func( $item['callback'], $item, $gallery ) ) {

				if ( ! $url ) {
					$url = $this->get_url( $gallery, $item['action'] );
				}

				if ( $item['action'] === $selected ) {
					$class = 'mpp-selected-item';
				}

				$html[] = sprintf( '<li><a href="%1$s" title ="%2$s" id="%3$s" data-mpp-action ="%4$s" class="%6$s">%5$s</a></li>', $item['url'], $item['label'], 'mpp-gallery-menu-item-' . $item['slug'], $item['action'], $item['label'], $class );
			}
		}

		if ( $html ) {
			echo '<ul>' . join( '', $html ) . '</ul>';
		}
	}

	/**
	 * Get the url for the given action.
	 *
	 * @param MPP_Gallery $gallery gallery object.
	 * @param string      $action action name.
	 *
	 * @return string
	 *
	 * @todo update with actual url
	 */
	public function get_url( $gallery, $action ) {
		return '#' . $action;
	}

}

/**
 * Gallery context menu class.
 */
class MPP_Gallery_Menu extends MPP_Menu {
}

/**
 * Media context menu class.
 */
class MPP_Media_Menu extends MPP_Menu {

}

