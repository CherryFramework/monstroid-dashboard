<?php
/**
 * Add a gateway for safe transfer methods from Monstroid Wizard
 *
 * @package   monstroid_dashboard
 * @author    Cherry Team
 * @license   GPL-2.0+
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class MD_Wizard_Gateway {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since 1.0.0
	 * @var   object
	 */
	private static $instance = null;

	private $wizard_path = null;

	/**
	 * Get path to specific wizard files
	 *
	 * @since  1.1.0
	 * @param  string $to file to get path for
	 * @return string
	 */
	public static function get_path( $to = '' ) {

		if ( ! $this->wizard_path ) {
			$this->wizard_path = trailingslashit( WP_PLUGIN_DIR );
		}

		switch ( $to ) {
			case 'themes-list':
				return $this->wizard_path . 'includes/class-monstroid-wizard-themes-list.php';
				break;

			default:
				return $this->wizard_path;
				break;
		}

	}

	/**
	 * Wrapper for "get_themes" method from wizard
	 *
	 * @since  1.1.0
	 * @param  int $page     page number to show
	 * @param  int $per_page themes count per page
	 * @return string|bool
	 */
	public function get_themes_list( $page = 1, $per_page = 4 ) {

		/*if ( ! $this->has_access_to_themes_method() ) {
			return false;
		}

		$themes_api = Monstroid_Wizard_Themes_List::get_instance();

		return $themes_api->get_themes( $page, $per_page );*/

	}

	/**
	 * Check if we call valid method
	 *
	 * @since  1.1.0
	 * @param  string  $method  method name
	 * @return boolean
	 */
	public function has_access_to_themes_method( $method ) {

		if ( ! $method ) {
			return false;
		}

		if ( ! class_exists( 'Monstroid_Wizard_Themes_List' ) ) {
			return false;
		}

		if ( ! method_exists( 'Monstroid_Wizard_Themes_List', $method ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.1.0
	 * @return object
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}

/**
 * Wrapping function to get instance of gateway class
 *
 * @since  1.1.0
 * @return object gateway class instance
 */
function monstroid_dashboard_wizard_gateway() {
	return MD_Wizard_Gateway::get_instance();
}

monstroid_dashboard_wizard_gateway();