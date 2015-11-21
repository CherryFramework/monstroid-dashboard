<?php
/**
 * Admin notification API for monstroid dashboard
 *
 * @package   monstroid_dashboard
 * @author    Cherry Team
 * @license   GPL-2.0+
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// If class 'Monstroid_Dashboard_Notices' not exists.
if ( ! class_exists( 'Monstroid_Dashboard_Notices' ) ) {

	/**
	 * Admin notices manager class
	 */
	final class Monstroid_Dashboard_Notices {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Notices array to show
		 *
		 * @since 1.0.0
		 * @var   bool|array
		 */
		public $show_notices = false;

		/**
		 * Constructor for the class
		 */
		function __construct() {
			add_action( 'admin_init', array( $this, 'remove_notices' ), 1 );
			add_action( 'admin_init', array( $this, 'check_notices' ) );
			add_action( 'admin_notices', array( $this, 'show_notices' ) );
		}

		/**
		 * Show needed notices
		 *
		 * @since 1.0.0
		 */
		public function show_notices() {

			if ( empty( $this->show_notices ) ) {
				return;
			}

			$this->notices_css();

			foreach ( $this->show_notices as $id => $data ) {
				echo $this->build_notice( $id, $data );
			}

		}

		/**
		 * Build single notice output
		 *
		 * @since  1.0.0
		 * @param  string $id   notice slug(id).
		 * @param  array  $data notice data.
		 * @return string
		 */
		public function build_notice( $id, $data ) {

			$accept_action = '';

			if ( ! empty( $data['accept_action'] ) ) {
				$accept_class = 'primary';
				if ( ! empty( $data['accept_action']['class'] ) ) {
					$accept_class = esc_attr( $data['accept_action']['class'] );
				}
				$accept_action = '<a href="' . $data['accept_action']['url'] . '" class="' . $accept_class . '">' . $data['accept_action']['label'] . '</a>';
			}

			if ( ! empty( $data['dismiss_action'] ) ) {
				$dismiss_class = 'dismiss';
				if ( ! empty( $data['dismiss_action']['class'] ) ) {
					$dismiss_class = esc_attr( $data['dismiss_action']['class'] );
				}
				$dismiss_action = '<a href="' . $data['dismiss_action']['url'] . '" class="' . $dismiss_class . '">' . $data['dismiss_action']['label'] . '</a>';
			}

			$result = sprintf(
				'<div id="%1$s" class="%2$s monstroid-notice"><p>%3$s<br>%4$s%5$s</p></div>',
				$id, $data['type'], $data['message'], $accept_action, $dismiss_action
			);

			return apply_filters( 'monstroid_dashboard_notice_output', $result, $id );

		}

		/**
		 * Get specific notices CSS
		 *
		 * @since  1.0.0
		 * @return void|bool false
		 */
		public function notices_css() {

			if ( monstroid_dashboard()->is_dashboard_page() ) {
				return false;
			}

			$css = apply_filters(
				'monstroid_dashboard_notices_css',
				'.wp-admin .monstroid-notice{display:block;background:#fff;box-shadow:none;padding:5px 20px;clear:both;margin-left:0;}.wp-admin .monstroid-notice p{font-size:14px;line-height:24px}.wp-admin .monstroid-notice a{box-shadow:none;color:#03a9f4;margin:0 10px 0 0;text-decoration:none}.wp-admin .monstroid-notice a.dismiss{color:#ef5350}.wp-admin .monstroid-notice.error{border-color:#ef5350}.wp-admin .monstroid-notice.updated{border-color:#03a9f4}'
			);

			printf( '<style>%s</style>', $css );

		}

		/**
		 * Check dashboard notices
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function check_notices() {

			$notices = apply_filters(
				'monstroid_dashboard_notices',
				array(
					'need_update' => array(
						'message'        => sprintf(
							__( 'New Monstroid version is available - %s.', 'monstroid-dashboard' ),
							monstroid_dashboard_updater()->get_update_data( 'new_version' )
						),
						'type'           => 'updated',
						'accept_action'  => array(
							'url'   => monstroid_dashboard()->get_link( 'monstroid-updater', array( 'md_hide_notice' => 'need_update' ) ),
							'label' => __( 'Update', 'monstroid-dashboard' ),
							'class' => '',
						),
						'dismiss_action' => array(
							'url'   => esc_url( add_query_arg( array( 'mu_skip_update' => 'yes' ) ) ),
							'label' => __( 'Skip', 'monstroid-dashboard' ),
							'class' => '',
						),
					),
					'set_creds' => array(
						'message'        => __( 'Please, set up filesystem credentials to allow automatic updates.', 'monstroid-dashboard' ),
						'type'           => 'error',
						'accept_action'  => false,
						'dismiss_action' => array(
							'url'   => esc_url( add_query_arg( array( 'md_dismiss_creds' => 'yes' ) ) ),
							'label' => __( 'Dismiss', 'monstroid-dashboard' ),
							'class' => '',
						),
					),
				)
			);

			$this->check_update_notice( $notices['need_update'] );
			$this->check_creds_notice( $notices['set_creds'] );

		}

		/**
		 * Check update notice visibility and add it into shown notices array
		 *
		 * @since  1.0.0
		 * @param  array $notice_data notice data.
		 * @return void|bool false
		 */
		public function check_update_notice( $notice_data ) {

			if ( ! current_user_can( 'update_themes' ) ) {
				return false;
			}

			if ( isset( $_GET['md_hide_notice'] ) && 'need_update' == $_GET['md_hide_notice'] ) {
				return false;
			}

			if ( ! monstroid_dashboard_updater()->has_update() ) {
				return false;
			}

			$this->show_notices['need_update'] = $notice_data;
		}

		/**
		 * Check creds notice visibility
		 *
		 * @since  1.0.0
		 * @param  array $notice_data notice data.
		 * @return void|bool false
		 */
		public function check_creds_notice( $notice_data ) {

			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}

			if ( true === monstroid_dashboard()->filesystem->check_filesystem_method() ) {
				return false;
			}

			$user_id   = get_current_user_id();
			$dismissed = get_user_meta( $user_id, 'dismiss_creds', true );

			if ( $dismissed ) {
				return false;
			}

			$this->show_notices['set_creds'] = $notice_data;

		}

		/**
		 * Remove dashboard admin notices
		 *
		 * @since 1.0.0
		 */
		public function remove_notices() {
			$this->skip_update();
			$this->dismiss_creds();
		}

		/**
		 * Skip update notice
		 *
		 * @since  1.0.0
		 * @return void|bool false
		 */
		public function skip_update() {

			if ( ! current_user_can( 'update_themes' ) ) {
				return false;
			}

			if ( ! isset( $_GET['mu_skip_update'] ) || 'yes' !== $_GET['mu_skip_update'] ) {
				return false;
			}

			delete_option( 'monstroid_dahboard_need_update' );
			$update_data = get_option( 'monstroid_dahboard_update_data' );

			if ( $update_data && isset( $update_data['new_version'] ) ) {
				update_option( 'monstroid_skip_update', $update_data['new_version'] );
			}

		}

		/**
		 * Dismiss creds
		 *
		 * @since  1.0.0
		 * @return void|bool false
		 */
		public function dismiss_creds() {

			if ( ! current_user_can( 'update_themes' ) ) {
				return false;
			}

			if ( ! isset( $_GET['md_dismiss_creds'] ) || 'yes' !== $_GET['md_dismiss_creds'] ) {
				return false;
			}
			$user_id = get_current_user_id();
			update_user_meta( $user_id, 'dismiss_creds', true );

		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
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

	Monstroid_Dashboard_Notices::get_instance();
}
