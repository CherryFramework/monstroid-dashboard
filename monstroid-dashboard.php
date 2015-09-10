<?php
/**
 * @package   monstroid_dashboard
 * @author    Cherry Team
 * @license   GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name:       Monstroid Dashboard
 * Plugin URI:        http://www.templatemonster.com/
 * Description:       Dashboard for Monstroid theme
 * Version:           1.1.0
 * Author:            TemplateMonster
 * Author URI:        http://www.templatemonster.com/
 * Text Domain:       monstroid-dashboard
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 *
 * Dashboard for Monstroid theme
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// If class 'Monstroid_Dashboard' not exists.
if ( ! class_exists( 'Monstroid_Dashboard' ) ) {

	/**
	 * Main plugin class
	 */
	final class Monstroid_Dashboard {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Plugin version
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $version = '1.1.0';

		/**
		 * Plugin folder URL
		 *
		 * @since 1.0.0
		 * @var   string
		 */
		public $plugin_url = null;

		/**
		 * Plugin folder path
		 *
		 * @since 1.0.0
		 * @var   string
		 */
		public $plugin_dir = null;

		/**
		 * Filesystem class instance
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		public $filesystem = null;

		/**
		 * Class constructor
		 */
		function __construct() {

			// Do something only on backend
			if ( ! is_admin() ) {
				return;
			}

			$this->includes();
			$this->local_includes();
			$this->plugin_updater();

			$this->filesystem = Monstroid_Dashboard_Filesystem::get_instance();

			add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
			add_filter( 'cherry_data_manager_exclude_folder_from_export', array( $this, 'do_not_export_backups' ) );

			register_activation_hook( __FILE__, array( __CLASS__, 'activation' ) );
			register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivation' ) );
		}

		/**
		 * Include required core files
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function includes() {

			include_once $this->plugin_dir( 'admin/includes/class-monstroid-dashboard-interface.php' );
			include_once $this->plugin_dir( 'admin/includes/class-monstroid-dashboard-updater.php' );
			include_once $this->plugin_dir( 'admin/includes/class-monstroid-dashboard-notices.php' );
			include_once $this->plugin_dir( 'admin/includes/class-monstroid-dashboard-ui-handlers.php' );
			include_once $this->plugin_dir( 'admin/includes/class-monstroid-dashboard-filesystem.php' );
			include_once $this->plugin_dir( 'admin/includes/class-monstroid-dashboard-themes-list.php' );
			include_once $this->plugin_dir( 'admin/includes/gateways/class-md-wizard-gateway.php' );

		}

		public function local_includes() {
			if ( ! $this->is_dashboard_page() ) {
				return false;
			}

			include_once $this->plugin_dir( 'admin/includes/class-monstroid-dashboard-ui-elements.php' );
		}

		/**
		 * Get plugin URL (or some plugin dir/file URL)
		 *
		 * @since  1.0.0
		 * @param  string $path dir or file inside plugin dir
		 * @return string
		 */
		public function plugin_url( $path = null ) {

			if ( ! $this->plugin_url ) {
				$this->plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );
			}

			if ( null != $path ) {
				return $this->plugin_url . $path;
			}

			return $this->plugin_url;

		}

		/**
		 * Get plugin dir path (or some plugin dir/file path)
		 *
		 * @since  1.0.0
		 * @param  string $path dir or file inside plugin dir
		 * @return string
		 */
		public function plugin_dir( $path ) {

			if ( ! $this->plugin_dir ) {
				$this->plugin_dir = trailingslashit( plugin_dir_path( __FILE__ ) );
			}

			if ( null != $path ) {
				return $this->plugin_dir . $path;
			}

			return $this->plugin_dir;

		}

		/**
		 * Get dashboard inner links
		 *
		 * @since  1.0.0
		 * @param  string $page page slug to get URL for
		 * @param  array  $args additional query arguments array
		 * @return string
		 */
		public function get_link( $page = null, $args = array() ) {

			if ( ! $page ) {
				return;
			}

			$url = menu_page_url( $page, false );

			if ( ! empty( $args ) ) {
				$url = add_query_arg( $args, $url );
			}

			return $url;

		}

		/**
		 * Check if on dashborad-relate page
		 *
		 * @since  1.0.0
		 * @return boolean
		 */
		public function is_dashboard_page() {

			$page = isset( $_GET['page'] ) ? $_GET['page'] : false;
			if ( ! $page ) {
				return false;
			}

			return apply_filters(
				'monstroid_dashboard_is_related_page',
				in_array( $page, array( 'monstroid-dashboard', 'monstroid-updater', 'monstroid-themes' ) )
			);

		}

		/**
		 * Check updates
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function assets() {

			if ( ! $this->is_dashboard_page() ) {
				return;
			}

			wp_enqueue_script(
				'monstroid-dashboard',
				$this->plugin_url( 'assets/js/monstroid-dashboard.js' ), array(), $this->version, true
			);

			wp_localize_script(
				'monstroid-dashboard',
				'monstroid_dashboard',
				array(
					'nonce'          => wp_create_nonce( 'monstroid-dashboard' ),
					'empty_key'      => __( "Please, provide license key", 'monstroid-dashboard' ),
					'internal_error' => __( "Internal error. Please, contact support team", 'monstroid-dashboard' ),
					'confirm_update' => __( "Please, note that the update process will replace changes performed within theme core files with the Monstroid ones.\n\nClick OK to proceed", 'monstroid-dashboard' ),
					'confirm_delete' => __( "Are you sure you would like to remove a backup?\nPlease note that the available backup will be removed per your request.\n\nClick OK to proceed", 'monstroid-dashboard' ),
				)
			);

			wp_localize_script(
				'monstroid-dashboard',
				'md_wizard_steps',
				array(
					'install_theme' => add_query_arg( array( 'step' => 'theme-install', 'type' => 'premium', ), menu_page_url( 'monstroid-wizard', false ) ),
				)
			);

			wp_enqueue_style( 'roboto', '//fonts.googleapis.com/css?family=Roboto:400,300,500', array(), '1.0.0' );

			wp_enqueue_style(
				'monstroid-dashboard',
				$this->plugin_url( 'assets/css/monstroid-dashboard.css' ), array(), $this->version
			);

		}

		/**
		 * Add update backups folder to excluded from export directories
		 *
		 * @since  1.0.0
		 * @param  array  $dirs  excluded directories list
		 * @return array
		 */
		public function do_not_export_backups( $dirs ) {
			$dirs[] = 'update-backups';
			return $dirs;
		}

		/**
		 * Check if Monstroid theme is installed on this site
		 *
		 * @since  1.0.0
		 * @return boolean
		 */
		public function is_monstroid_installed() {
			$theme = wp_get_theme('monstroid');
			return $theme->exists();
		}

		/**
		 * Check if Monstroid Plugin is active
		 *
		 * @since  1.1.0
		 * @return boolean
		 */
		public function is_wizard_active() {

			return in_array(
				'monstroid-wizard/monstroid-wizard.php',
				apply_filters( 'active_plugins', get_option( 'active_plugins' ) )
			);

		}

		/**
		 * Init plugin self-updater
		 *
		 * @since  1.0.0
		 */
		public function plugin_updater() {

			require_once( $this->plugin_dir( 'admin/includes/class-cherry-update/class-cherry-plugin-update.php' ) );

			$Cherry_Plugin_Update = new Cherry_Plugin_Update();
			$Cherry_Plugin_Update->init( array(
				'version'         => $this->version,
				'slug'            => 'monstroid-dashboard',
				'repository_name' => 'monstroid-dashboard',
			));
		}

		/**
		 * Do actions on plugin deactivation
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public static function deactivation() {
			monstroid_dashboard_updater()->remove_shedules();
			monstroid_dashboard_updater()->clear_update_data();
			delete_option( 'monstroid_dashboard_disable_auto_updates' );
		}

		/**
		 * Do actions on plugin activation
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public static function activation() {
			monstroid_dashboard_updater()->shedule_updates();
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

/**
 * Define main function to get plugin instance
 *
 * @since  1.0.0
 * @return object
 */
function monstroid_dashboard() {
	return Monstroid_Dashboard::get_instance();
}

/**
 * Define base updater function
 *
 * @since  1.0.0
 * @return object
 */
function monstroid_dashboard_updater() {
	include_once monstroid_dashboard()->plugin_dir( 'admin/includes/class-monstroid-dashboard-updater.php' );
	return Monstroid_Dashboard_Updater::get_instance();
}

// create default plugin instance
monstroid_dashboard();
// create base updater instance
monstroid_dashboard_updater();