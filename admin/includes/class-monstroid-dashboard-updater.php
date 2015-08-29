<?php
/**
 * Define base update methods and actions
 *
 * @package   monstroid_dashboard
 * @author    Cherry Team
 * @license   GPL-2.0+
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// If class 'Monstroid_Dashboard_Updater' not exists.
if ( ! class_exists( 'Monstroid_Dashboard_Updater' ) ) {

	final class Monstroid_Dashboard_Updater {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Cloud updater API endpoint
		 *
		 * @since 1.0.0
		 * @var   string
		 */
		public $api = 'http://192.168.9.40/local-cloud-live/';

		/**
		 * Current theme version
		 *
		 * @since 1.0.0
		 * @var   null
		 */
		private $current_version = null;

		/**
		 * Is update avaliable
		 *
		 * @since 1.0.0
		 * @var   bool
		 */
		private $has_update = false;

		/**
		 * Detailed infromation about avaliable update (if exist)
		 *
		 * @since 1.0.0
		 * @var   array|boolean
		 */
		public $update_data = false;

		/**
		 * moinstroid theme ID
		 *
		 * @since 1.0.0
		 * @var   integer
		 */
		public $monstroid_id = 55555;

		/**
		 * Updater options
		 *
		 * @since 1.0.0
		 * @var   array
		 */
		private $options = array();

		function __construct() {

			add_filter( 'cron_schedules', array( $this, 'add_shedules' ) );
			add_filter( 'site_transient_update_themes', array( $this, 'add_monstroid_data' ) );

			add_action( 'admin_init', array( $this, 'check_auto_updates' ) );
			$this->shedule_updates();

			add_action( 'monstroid_scheduled_update', array( $this, 'scheduled_update' ) );
			add_action( 'admin_init', array( $this, 'force_check_updates' ) );

			add_action( 'wp_ajax_monstroid_dashboard_do_theme_update', array( $this, 'do_theme_update' ) );
		}

		/**
		 * Get current Monstroid version
		 *
		 * @since  1.0.0
		 */
		public function get_current_version() {
			if ( null == $this->current_version ) {
				$theme = wp_get_theme( 'monstroid' );
				$this->current_version = $theme->get( 'Version' );
			}

			return $this->current_version;
		}

		/**
		 * Setup updater options
		 *
		 * @since 1.0.0
		 */
		public function get_options() {

			if ( ! empty( $this->options ) ) {
				return $this->options;
			}

			$this->options = apply_filters(
				'monstroid_dashboard_updater_options',
				array(
					'disable_auto_check' => get_option( 'monstroid_dashboard_disable_auto_updates', false )
				)
			);

			return $this->options;

		}

		/**
		 * Add dashboard-specific intervals for scheduled events
		 *
		 * @param array $schedules registered inervals
		 */
		public function add_shedules( $schedules ) {
			$schedules['minute'] = array(
				'interval' => 60,
				'display'  => __( 'Every minute' ),
			);
			return $schedules;
		}

		/**
		 * Schedule automatic updates
		 *
		 * @since 1.0.0
		 */
		public function shedule_updates() {
			$options = $this->get_options();

			if ( $options['disable_auto_check'] ) {
				wp_clear_scheduled_hook( 'monstroid_scheduled_update' );
				return true;
			}

			if ( ! wp_next_scheduled( 'monstroid_scheduled_update' ) ) {
				wp_schedule_event( time(), 'minute', 'monstroid_scheduled_update' );
			}
		}

		/**
		 * force check update
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function check_updates() {

			$query = add_query_arg(
				array(
					'mu_request' => true,
					'mu_action'  => 'check_updates',
					'user_ver'   => $this->get_current_version()
				),
				$this->api
			);

			$check_request = wp_remote_get( $query );

			if ( is_wp_error( $check_request ) ) {
				return false;
			}

			$check_result = $check_request['body'];

			return json_decode( $check_result, true );

		}

		/**
		 * run scheduled update
		 *
		 * @since 1.0.0
		 */
		public function scheduled_update() {

			$update = $this->check_updates();

			if ( ! $update ) {
				return false;
			}

			$skip_update = get_option( 'monstroid_skip_update' );

			if ( $skip_update && isset( $update['new_version'] ) && version_compare( $skip_update, $update['new_version'], "==" ) ) {
				delete_option( 'monstroid_dahboard_need_update' );
				delete_option( 'monstroid_dahboard_update_data' );
				return false;
			}

			if ( isset( $update['need_update'] ) && true == $update['need_update'] ) {
				update_option( 'monstroid_dahboard_need_update', true );
				$this->has_update = true;
				delete_option( 'monstroid_skip_update' );
				update_option( 'monstroid_dahboard_update_data', $update );
			} elseif ( isset( $update['need_update'] ) && false == $update['need_update'] ) {
				$this->clear_update_data();
			}

		}

		/**
		 * Remove all update related data
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function clear_update_data() {
			$this->has_update = false;
			delete_option( 'monstroid_skip_update' );
			delete_option( 'monstroid_dahboard_update_data' );
			delete_option( 'monstroid_dahboard_need_update' );
		}

		/**
		 * Prepare theme update transients for Monstroid update
		 *
		 * @since  1.0.0
		 * @param  object $data themes update data
		 * @return object
		 */
		public function add_monstroid_data( $data ) {

			if ( ! isset( $_REQUEST['action'] ) || 'monstroid_dashboard_do_theme_update' !== $_REQUEST['action'] ) {
				return $data;
			}

			$package = $this->get_update_package();

			if ( ! $package ) {
				return $data;
			}

			$update_data = $this->get_update_data();

			$data->response['monstroid'] = array(
				'theme'       => 'monstroid',
				'new_version' => $update_data['new_version'],
				'url'         => 'http://www.templatemonster.com/wordpress-themes/monstroid/',
				'package'     => $package
			);

			return $data;
		}

		/**
		 * Get monstroid update package
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function get_update_package() {

			$key = get_option( 'monstroid_key' );

			if ( ! $key ) {
				return 'key_missed';
			}

			$request_uri = add_query_arg(
				array(
					'mu_request' => true,
					'mu_action'  => 'get_link',
					'key'        => $key
				),
				$this->api
			);

			$request = wp_remote_get( $request_uri );

			if ( is_wp_error( $request ) ) {
				return 'request_failed';
			}

			$result = json_decode( $request['body'], true );

			if ( empty( $result ) || ! is_array( $result ) ) {
				return 'empty_response';
			}

			if ( isset( $result['success'] ) && false == $result['success'] ) {
				return 'key_invalid';
			}

			return $result['data']['link'];
		}

		/**
		 * Check if update is avaliable
		 *
		 * @since  1.0.0
		 * @return boolean
		 */
		public function has_update() {

			if ( ! $this->has_update ) {
				$this->has_update = get_option( 'monstroid_dahboard_need_update' );
			}
			return $this->has_update;

		}

		/**
		 * Get update data
		 *
		 * @since  1.0.0
		 * @return array|bool
		 */
		public function get_update_data() {
			if ( ! $this->update_data ) {
				$this->update_data = get_option( 'monstroid_dahboard_update_data' );
			}
			return $this->update_data;
		}

		/**
		 * Check if update avaliable directly from Update data option
		 *
		 * @since  1.0.0
		 * @return bool
		 */
		public function force_has_update() {

			$data = $this->get_update_data();

			if ( ! $data ) {
				return false;
			}

			if ( ! isset( $data['new_version'] ) ) {
				return false;
			}

			$curren_ver = $this->get_current_version();

			return version_compare( $data['new_version'], $curren_ver, ">" );

		}

		/**
		 * Force check monstroid updates
		 *
		 * @since 1.0.0
		 */
		public function force_check_updates() {

			if ( ! isset( $_REQUEST['md_force_check_update'] ) ) {
				return;
			}

			$this->scheduled_update();

		}

		/**
		 * Check if we need enable or disable auto updates and process
		 *
		 * @since  1.0.0
		 */
		public function check_auto_updates() {
			if ( isset( $_REQUEST['md_disable_auto_updates'] ) ) {
				$this->disable_auto_updates();
				return;
			}
			if ( isset( $_REQUEST['md_enable_auto_updates'] ) ) {
				$this->enable_auto_updates();
				return;
			}
		}

		/**
		 * Disable automatic updates checking
		 *
		 * @since 1.0.0
		 */
		public function disable_auto_updates() {

			update_option( 'monstroid_dashboard_disable_auto_updates', true );
			delete_option( 'monstroid_dahboard_need_update' );
			wp_redirect( esc_url( remove_query_arg( 'md_disable_auto_updates' ) ) );
			die();

		}

		/**
		 * Enable automatic updates checking
		 *
		 * @since 1.0.0
		 */
		public function enable_auto_updates() {

			delete_option( 'monstroid_dashboard_disable_auto_updates' );
			$this->scheduled_update();
			wp_redirect( esc_url( remove_query_arg( 'md_enable_auto_updates' ) ) );
			die();
		}

		/**
		 * Process automatic theme update
		 *
		 * @since 1.0.0
		 */
		public function do_theme_update() {

			if ( ! isset( $_REQUEST['nonce'] ) ) {
				die();
			}

			if ( ! wp_verify_nonce( esc_attr( $_REQUEST['nonce'] ), 'monstroid-dashboard' ) ) {
				die();
			}

			if ( ! current_user_can( 'update_themes' ) ) {
				die();
			}

			monstroid_dashboard()->filesystem->add_creds();

			$backup_link = $this->try_make_backup();

			include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
			include_once( monstroid_dashboard()->plugin_dir( 'admin/includes/class-monstroid-dashboard-upgrader-skin.php' ) );

			$title = __( 'Update Monstroid theme', 'monstroid-dashboard' );
			$theme = 'monstroid';

			$upgrader = new Theme_Upgrader( new Monstroid_Dashboard_Upgrader_Skin( compact('title', 'theme') ) );

			$update = $upgrader->upgrade( $theme );
			$log    = $upgrader->skin->get_install_log();

			$success = sprintf(
				__( 'Great news! Monstroid Dashboard successfully updated your theme to the latest available version. Also, we backed up your previous data and you can find it <a href="%s">here</a>', 'monstroid-dashboard'),
				$backup_link
			);


			if ( true == $update ) {
				$this->clear_update_data();
				wp_send_json_success(
					array(
						'message'    => $success,
						'update_log' => $log
					)
				);
			}

			$upgrader->maintenance_mode( false );

			if ( is_wp_error( $update ) ) {
				wp_send_json_error(
					array(
						'message'    => sprintf( __( 'Update failed. %s', 'monstroid-dashboard' ), $update->get_error_message() ),
						'update_log' => $log
					)
				);
			}

			if ( ! $update ) {
				wp_send_json_error(
					array(
						'message'    => __(
							'Update failed. <a href="#" class="show-update-log">Show update log</a>',
							'monstroid-dashboard'
						),
						'update_log' => $log
					)
				);
			}

			wp_send_json_error(
				array(
					'message'    => __( 'Unknown error, please try again later or contact our support', 'monstroid-dashboard' ),
					'update_log' => $log
				)
			);

		}

		/**
		 * Show messages after force update checking
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function check_update_messages( $before = '', $after = '' ) {
			if ( $this->force_has_update() ) {
				return sprintf(
					'<div class="md-message md-update">%2$s%1$s%3$s</div>',
					__( 'New Monstroid version avaliable', 'monstroid-dashboard' ),
					$before,
					$after
				);
			} else {
				return sprintf(
					'<div class="md-message md-success">%2$s%1$s%3$s</div>',
					__( 'Your Monstroid is up to date', 'monstroid-dashboard' ),
					$before,
					$after
				);
			}
		}

		/**
		 * Try to make backup before updating
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function try_make_backup() {

			if ( ! empty( $_REQUEST['ignore_backup'] ) ) {
				return true;
			}

			include_once( monstroid_dashboard()->plugin_dir( 'admin/includes/class-monstroid-dashboard-backup-manager.php' ) );

			$backup_manager = new Monstroid_Dashboard_Backup_Manager;
			$backup_done    = $backup_manager->make_backup();

			if ( false === $backup_done ) {
				$message = __(
					"Can't create current theme backup. <a href='#' class='force-run-theme-update'>Update anyway</a>",
					"monstroid-dashboard"
				);
				wp_send_json_error(
					array(
						'type'    => 'backup_failed',
						'message' => $message
					)
				);
			}

			return $backup_done;
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