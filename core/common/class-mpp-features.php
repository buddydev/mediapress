<?php
// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MPP_Features class allows us to add features/test for features easily
 * We can associate features object to anything
 */
class MPP_Features {

	/**
	 * Array of supported feature/values.
	 *
	 * @var array of features
	 */
	private $supported = array();

	/**
	 * Add support for specific feature
	 * If $single is set to true, adding a feature multiple times will just replace it
	 * If $single is false, adding a feature multiple times will append the new values to the already existing one
	 *
	 * @param string  $feature feature name.
	 * @param mixed   $value feature value.
	 * @param boolean $single is it the only value or will there be multiple value.
	 *
	 * @return MPP_Features
	 */
	public function register( $feature, $value, $single = false ) {
		$this->supported[ $feature ][] = $value;

		return $this;
	}

	/**
	 * Remove feature or the feature value combination if value is given.
	 *
	 * @param string $feature feature name.
	 * @param mixed  $value feature value.
	 *
	 * @return MPP_Features
	 */
	public function deregister( $feature, $value = null ) {

		// if value is not given, remove support for this feature.
		if ( ! $value ) {
			unset( $this->supported[ $feature ] );
		} else {
			// if value is given, just remove that value.
			$vals  = $this->supported[ $feature ];
			$count = count( $vals );
			for ( $i = 0; $i < $count; $i ++ ) {

				if ( $vals[ $i ] == $value ) {
					unset( $vals[ $i ] );
					break;
				}
			}

			$vals = array_filter( $vals );

			$this->supported[ $feature ] = $vals;
		}

		return $this;

	}

	/**
	 * Get the value for the given feature.
	 *
	 * @param string $feature name feature name.
	 *
	 * @return mixed|boolean
	 */
	public function get( $feature ) {

		if ( isset( $this->supported[ $feature ] ) ) {
			return $this->supported[ $feature ];
		}

		return false;
	}

	/**
	 * Check if the feature supports given value
	 *
	 * @param string $feature feature name.
	 * @param mixed  $value Optional. Feature value.
	 *
	 * @return boolean
	 */
	public function supports( $feature, $value = null ) {

		if ( ! isset( $this->supported[ $feature ] ) || empty( $this->supported[ $feature ] ) ) {
			return false;
		}

		if ( ! $value ) {
			return true;
		}

		$vals = $this->supported[ $feature ];

		if ( in_array( $value, $vals ) ) {
			return true;
		}

		return false;
	}
}

// mpp_add_component_support( $component, $feature, $value );
// mpp_add_component_support( $component, $feature, $value );
