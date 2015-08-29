<?php
/**
 * Theme Upgrader Skin for Monstroid Dashboard updater.
 *
 * @package   monstroid_dashboard
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

if ( ! class_exists( 'Theme_Upgrader_Skin' ) ) {
	wp_die( __( 'Doing it wrong', 'monstroid-dashboard' ) );
}

class Monstroid_Dashboard_Upgrader_Skin extends Theme_Upgrader_Skin {

	/**
	 * Installation messages log
	 * @var string
	 */
	public $log;

	/**
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );
	}

	/**
	 * @access public
	 */
	public function header() {}

	/**
	 * @access public
	 */
	public function footer() {}

	/**
	 * @access public
	 */
	public function after() {}

	/**
	 * @param string $string
	 */
	public function feedback($string) {

		if ( isset( $this->upgrader->strings[$string] ) ) {
			$string = $this->upgrader->strings[$string];
		}

		if ( strpos($string, '%') !== false ) {
			$args = func_get_args();
			$args = array_splice($args, 1);
			if ( $args ) {
				$args = array_map( 'strip_tags', $args );
				$args = array_map( 'esc_html', $args );
				$string = vsprintf($string, $args);
			}
		}

		if ( empty($string) ) {
			return;
		}

		$this->add_log( $string );
	}

	/**
	 * Add installation log string
	 *
	 * @since 1.0.0
	 * @param string $string
	 */
	public function add_log( $string ) {
		$this->log .= '<p>' . $string . '</p>';
	}

	/**
	 * Get installation log
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_install_log() {
		return $this->log;
	}
}