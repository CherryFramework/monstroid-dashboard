<?php
/**
 * Handlers from custom UI controls
 *
 * @package   monstroid_dashboard
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// If class 'Monstroid_Dashboard_UI_Handlers' not exists.
if ( ! class_exists( 'Monstroid_Dashboard_UI_Handlers' ) ) {

	final class Monstroid_Dashboard_UI_Handlers {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		function __construct() {
			add_action( 'wp_ajax_monstroid_dashboard_download_latest', array( $this, 'download_latest' ) );
			add_action( 'wp_ajax_monstroid_dashboard_save_key', array( $this, 'save_key' ) );
			add_action( 'wp_ajax_monstroid_dashboard_get_backup', array( $this, 'get_backup' ) );
		}

		/**
		 * Process download latest monstroid version
		 *
		 * @since  1.0.0
		 */
		public function download_latest() {

			if ( ! isset( $_REQUEST['nonce'] ) ) {
				die();
			}

			if ( ! wp_verify_nonce( esc_attr( $_REQUEST['nonce'] ), 'monstroid-dashboard' ) ) {
				die();
			}

			if ( ! current_user_can( 'update_themes' ) ) {
				die();
			}

			$url = monstroid_dashboard_updater()->get_update_package();

			$this->throw_download_errors( $url );

			wp_send_json_success(
				array(
					'url' => $url
				)
			);

		}

		/**
		 * Get saved backup from uploads
		 *
		 * @since 1.0.0
		 */
		public function get_backup() {

			if ( ! isset( $_REQUEST['nonce'] ) ) {
				wp_die(
					__( 'Nonce key not provided', 'monstroid-dashboard' ),
					__( 'Downloading Error', 'monstroid-dashboard' )
				);
			}

			if ( ! wp_verify_nonce( esc_attr( $_REQUEST['nonce'] ), 'monstroid-dashboard' ) ) {
				wp_die(
					__( 'Incorrect nonce key', 'monstroid-dashboard' ),
					__( 'Downloading Error', 'monstroid-dashboard' )
				);
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die(
					__( 'Permission denied', 'monstroid-dashboard' ),
					__( 'Downloading Error', 'monstroid-dashboard' )
				);
			}

			$file = isset( $_REQUEST['file'] ) ? esc_attr( $_REQUEST['file'] ) : '';

			if ( ! $file ) {
				wp_die(
					__( 'Backup file not provided', 'monstroid-dashboard' ),
					__( 'Downloading Error', 'monstroid-dashboard' )
				);
			}

			include_once( monstroid_dashboard()->plugin_dir( 'admin/includes/class-monstroid-dashboard-backup-manager.php' ) );

			$backup_manager = Monstroid_Dashboard_Backup_Manager::get_instance();
			$backups        = $backup_manager->download_backup( $file );

			if ( false == $backups ) {
				wp_die(
					$backup_manager->get_message(),
					__( 'Downloading Error', 'monstroid-dashboard' )
				);
			}

		}

		/**
		 * Save monstroid license key
		 *
		 * @since  1.0.0
		 */
		public function save_key() {

			if ( ! isset( $_REQUEST['nonce'] ) ) {
				die();
			}

			if ( ! wp_verify_nonce( esc_attr( $_REQUEST['nonce'] ), 'monstroid-dashboard' ) ) {
				die();
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				die();
			}

			$key = isset( $_REQUEST['key'] ) ? esc_attr( $_REQUEST['key'] ) : '';

			if ( ! $key ) {
				wp_send_json_error( array(
					'message' => __( 'Please, enter valid key', 'monstroid-dashboard' )
				) );
			}

			$request_uri = add_query_arg(
				array(
					'edd_action' => 'activate_license',
					'item_name'  => urlencode( monstroid_dashboard_updater()->monstroid_id ),
					'license'    => $key
				),
				monstroid_dashboard_updater()->api
			);

			global $wp_version;
			$key_request = wp_remote_get(
				$request_uri,
				array( 'user-agent'  => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ) )
			);

			// Can't send request
			if ( is_wp_error( $key_request ) || ! isset($key_request['response']) ) {
				wp_send_json_error( array(
					'message' => __( 'Can not send activation request. ' . $key_request->get_error_message(), 'monstroid-dashboard' )
				) );
			}

			if ( 200 != $key_request['response']['code'] ) {
				wp_send_json_error( array(
					'message' => __( 'Activation request error. ' . $key_request['response']['code'] . ' - ' . $key_request['response']['message'] . '. Please, try again later', 'monstroid-dashboard' )
				) );
			}

			$response = json_decode( $key_request['body'] );

			// Request generate unexpected result
			if ( ! is_object( $response ) || !isset( $response->success ) ) {
				wp_send_json_error( array(
					'message' => __( 'Bad request.', 'monstroid-dashboard' )
				) );
			}

			// Requested license key is missing
			if ( ! $response->success && 'missing' == $response->error ) {
				wp_send_json_error( array(
					'message'   => __( 'Wrong license key. Make sure activation key is correct.', 'monstroid-dashboard' )
				) );
			}

			// Hosts limit reached
			if ( ! $response->success && 'limit_reached' == $response->error ) {
				wp_send_json_error( array(
					'message'   => __( 'Sorry, the license key you are trying to use exceeded the maximum amount of activations was applied for 3 domains', 'monstroid-dashboard' )
				) );
			}
			if ( ! $response->success && 'no_activations_left' == $response->error ) {
				wp_send_json_error( array(
					'message'   => __( 'Sorry, the license key you are trying to use exceeded the maximum amount of activations was applied for 3 domains', 'monstroid-dashboard' )
				) );
			}

			// Unknown error
			if ( ! $response->success && $response->error ) {
				wp_send_json_error( array(
					'message'   => $response->error
				) );
			}

			// Can not get the,e information from TM
			if ( empty( $response->tm_data->status ) || 'request failed' == $response->tm_data->status ) {
				wp_send_json_error( array(
					'message'   => __( 'License key is invalid or evaluation expired. Please, contact Support Live Chat: <a href="http://chat.template-help.com/">http://chat.template-help.com/</a>', 'monstroid-dashboard' )
				) );
			}

			// Theme currently in queue
			if ( 'queue' == $response->tm_data->status ) {
				wp_send_json_error( array(
					'message'   => __( 'Theme is not available yet. Please try again in 10 minutes.', 'monstroid-dashboard' )
				) );
			}

			// Theme currently removed from cloud
			if ( 'failed' == $response->tm_data->status ) {
				wp_send_json_error( array(
					'message'   => __( 'Theme is not available. Please contact support team for help.', 'monstroid-dashboard' )
				) );
			}

			update_option( 'monstroid_key', $key );

			set_transient( 'cherry_theme_name', monstroid_dashboard_updater()->monstroid_id, WEEK_IN_SECONDS );
			set_transient( 'cherry_key', $key, WEEK_IN_SECONDS );

			$_SESSION['cherry_data'] = array(
				'theme'  => $response->tm_data->theme,
				'sample' => $response->tm_data->sample_data,
			);

			wp_send_json_success( array(
				'message' => __( 'Key succesfully activated and saved', 'monstroid-dashboard' )
			) );

		}

		/**
		 * Check received packgae URL and throw JSON errors if it invalid
		 *
		 * @since  1.0.0
		 * @param  string $url received URL
		 * @return void
		 */
		public function throw_download_errors( $url ) {

			$message = false;

			switch ( $url ) {
				case 'key_missed':
					$message = __( 'Monstroid key not provided', 'monstroid-dashboard' );
					break;

				case 'request_failed':
					$message = __( 'Can\'t send download request. Please, try again later', 'monstroid-dashboard' );
					break;

				case 'empty_response':
					$message = __( 'Empty result returned. Please, try again later', 'monstroid-dashboard' );
					break;

				case 'key_invalid':
					$message = __( 'Your license key are invalid. Please, contact our support team.', 'monstroid-dashboard' );
					break;

			}

			if ( false !== $message ) {
				wp_send_json_error( array( 'message' => $message ) );
			}

		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance )
				self::$instance = new self;
			return self::$instance;
		}

	}

}

Monstroid_Dashboard_UI_Handlers::get_instance();