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
	 * @since 1.1.0
	 * @var   object
	 */
	private static $instance = null;

	/**
	 * Path to Monstroid Wizard base directory
	 *
	 * @since 1.1.0
	 * @var   string
	 */
	private $wizard_path = null;

	function __construct() {
		add_filter( 'wp_ajax_monstroid_dashboard_theme_install', array( $this, 'theme_install' ) );
	}

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

			case 'helper':
				return $this->wizard_path . 'class-monstroid-wizard-helper.php';
				break;

			default:
				return $this->wizard_path;
				break;
		}

	}

	/**
	 * Ajax callback for theme installation processing
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function theme_install() {

		if ( ! isset( $_REQUEST['nonce'] ) ) {
			die();
		}

		if ( ! wp_verify_nonce( esc_attr( $_REQUEST['nonce'] ), 'monstroid-dashboard' ) ) {
			die();
		}

		global $monstroid_wizard;

		if ( ! $monstroid_wizard || ! isset( $monstroid_wizard->helper ) ) {
			include_once $this->get_path( 'helper' );
			$helper = new monstroid_wizard_helper();
		} else {
			$helper = $monstroid_wizard->helper;
		}

		$helper->get_child_links();

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