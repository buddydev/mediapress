<?php
/**
 * Helps us detach/reattach pre existing hook callbacks.
 *
 * @package    MediaPress
 * @subpackage Core\Common
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

/**
 * Helps detaching/Attaching WordPress hooks on some of the filters/actions we don't want to be affected by 3rd parties.
 *
 * @see MPP_Gallery_Query::query()
 * @see MPP_Media_Query::query()
 */
class MPP_Hooks_Helper {

	/**
	 * Hooks array.
	 *
	 * @var []WP_Hook
	 */
	private $hooks = array();

	/**
	 * Detach one or more wp hooks callbacks.
	 *
	 * @param []string $tags array of variable args of tag names.
	 *
	 * @return bool
	 */
	public function detach( $tags ) {
		if ( empty( $tags ) ) {
			return false;
		}

		if ( ! is_array( $tags ) ) {
			$tags = func_get_args();
		}

		$done = true;
		foreach ( $tags as $tag ) {
			if ( ! $this->_detach( $tag ) ) {
				$done = false;
			}
		}

		return $done;
	}

	/**
	 * Restore one or more hooks.
	 *
	 * @param []string| $tags array of variable args of tag names.
	 *
	 * @return bool
	 */
	public function restore( $tags ) {

		if ( empty( $tags ) ) {
			return false;
		}

		if ( ! is_array( $tags ) ) {
			$tags = func_get_args();
		}

		$done = true;

		foreach ( $tags as $tag ) {
			if ( ! $this->_restore( $tag ) ) {
				$done = false;
			}
		}

		return $done;
	}

	/**
	 * Detach a WordPress action/filter and store it in our local array.
	 *
	 * @param string $tag filter/action name.
	 *
	 * @return bool
	 */
	private function _detach( $tag ) {
		global $wp_filter;
		$done = false;

		if ( isset( $wp_filter[ $tag ] ) ) {
			$this->hooks[ $tag ] = $wp_filter[ $tag ];

			// Remove from wp registered hooks.
			unset( $wp_filter[ $tag ] );
			$done = true;
		}

		return $done;
	}

	/**
	 * Restore a hook's attached callbacks.
	 *
	 * @param string $tag action/filter name.
	 *
	 * @return bool
	 */
	private function _restore( $tag ) {

		global $wp_filter;

		$done = false;
		if ( isset( $this->hooks[ $tag ] ) ) {
			$wp_filter[ $tag ] = $this->hooks[ $tag ];

			// Remove from local saved hooks.
			unset( $this->hooks[ $tag ] );
			$done = true;
		}

		return $done;
	}
}
