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

/**
 * Extend standard WordPress theme update skin for Monstroid updater
 */
class Monstroid_Dashboard_Upgrader_Skin extends Theme_Upgrader_Skin {

	/**
	 * Installation messages log
	 *
	 * @var string
	 */
	public $log;

	/**
	 * Constructor for the class
	 *
	 * @param array $args upgrader arguments array
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );
	}

	/**
	 * Print something in update feedback header
	 *
	 * @access public
	 */
	public function header() {}

	/**
	 * Print something in update feedback gooter
	 *
	 * @access public
	 */
	public function footer() {}

	/**
	 * Print something after update feedback
	 *
	 * @access public
	 */
	public function after() {}

	/**
	 * Process update log feedback
	 *
	 * @since  1.0.0
	 * @param  string $string feedback test string.
	 * @return void|null
	 */
	public function feedback( $string ) {

		if ( isset( $this->upgrader->strings[ $string ] ) ) {
			$string = $this->upgrader->strings[ $string ];
		}

		if ( strpos( $string, '%' ) !== false ) {
			$args = func_get_args();
			$args = array_splice( $args, 1 );
			if ( $args ) {
				$args   = array_map( 'strip_tags', $args );
				$args   = array_map( 'esc_html', $args );
				$string = vsprintf( $string, $args );
			}
		}

		if ( empty( $string ) ) {
			return;
		}

		$this->add_log( $string );
	}

	/**
	 * Add installation log string
	 *
	 * @since  1.0.0
	 * @param  string $string text to add into update log.
	 * @return void
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
