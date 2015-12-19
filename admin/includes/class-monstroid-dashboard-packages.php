<?php
/**
 * Additional packacges for Monstroid theme. Like shop, etc.
 *
 * @package   monstroid_dashboard
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Monstroid_Dashboard_Packages' ) ) {

	/**
	 * Define Monstroid_Dashboard_Packages class
	 */
	class Monstroid_Dashboard_Packages {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.1.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Registered packages
		 *
		 * @since 1.1.0
		 * @var   array
		 */
		public $packages = null;

		/**
		 * Required wizard version
		 *
		 * @since 1.1.0
		 * @var   string
		 */
		public $required_wizard_version = '1.2.0';

		/**
		 * Required monstroid version
		 *
		 * @since 1.1.0
		 * @var   string
		 */
		public $required_monstroid_version = '1.1.1';

		/**
		 * Required data manager version
		 *
		 * @since 1.1.0
		 * @var   string
		 */
		public $required_data_manager_version = '1.0.8';

		/**
		 * Holder for packages errors
		 *
		 * @since 1.1.0
		 * @var   array
		 */
		public $package_errors = array();

		/**
		 * Constructor for the class
		 */
		function __construct() {

			if ( ! $this->is_valid_wizard_version() || ! $this->is_valid_data_manager_version() ) {
				return;
			}

			if ( ! $this->is_valid_monstroid_version() ) {
				return;
			}

			$this->prepare_package_installer();
			add_action( 'cherry_plugin_import_json', array( $this, 'set_package_status' ) );

		}

		/**
		 * Get registered packeges array.
		 *
		 * @since  1.1.0
		 * @return array
		 */
		public function get_packages() {

			$this->packages = array(
				'woocommerce' => array(
					'title'       => __( 'Shop', 'monstroid-dashboard' ),
					'thumb'       => monstroid_dashboard()->plugin_url( 'assets/images/woocommerce-screen.png' ),
					'installed'   => $this->is_package_installed( 'woocommerce' ),
					'plugins'     => apply_filters(
						'monstroid_dashboard_shop_plugins',
						array(
							'woocommerce',
							'yith-woocommerce-compare',
							'yith-woocommerce-wishlist',
							'yith-woocommerce-quick-view',
							'yith-woocommerce-zoom-magnifier',
						)
					),
					'sample_data' => $this->get_sample_data_part_link( 'woocommerce' ),
				),
			);

			return $this->packages;

		}

		/**
		 * Show packages list.
		 *
		 * @since  1.1.0
		 * @return void|null
		 */
		public function show_packages_list() {

			$packages = $this->get_packages();

			if ( empty( $packages ) ) {
				return null;
			}

			if ( ! $this->is_valid_wizard_version() || ! $this->is_valid_data_manager_version() ) {
				return null;
			}

			if ( ! $this->is_valid_monstroid_version() ) {
				return null;
			}

			foreach ( $packages as $package_id => $package ) {

				$has_errors   = $this->check_package_errors( $package_id, $package );
				$install_link = apply_filters( 'monstroid_dashboard_package_installation_link', '#', $package );
				$install_link = add_query_arg( array( 'package' => $package_id ), $install_link );

				include monstroid_dashboard()->plugin_dir( 'admin/views/package-item.php' );
			}

		}

		/**
		 * Check if is current package is available to installation
		 *
		 * @since  1.1.0
		 * @param  string $package package ID.
		 * @param  array  $data    package data array.
		 * @return bool
		 */
		public function check_package_errors( $package, $data ) {

			$is_installed = $data['installed'];

			if ( $is_installed ) {
				$message = __( 'This package already installed', 'monstroid-dashboard' );
				$this->add_error( $package, $message );
				return true;
			}

			if ( ! monstroid_dashboard()->is_monstroid_active() ) {

				$message = __( 'This package is available only for Monstroid theme.', 'monstroid-dashboard' );

				$activate_link = sprintf(
					'<a href="%s">%s</a>',
					admin_url( 'themes.php' ),
					__( 'activate', 'monstroid-dashboard' )
				);

				$message .= ' ';
				$message .= sprintf(
					__( 'You need %s Monstroid theme to get it', 'monstroid-dashboard' ),
					$activate_link
				);
				$this->add_error( $package, $message );
				return true;
			}

			return false;

		}

		/**
		 * Add error message for current package
		 *
		 * @since  1.1.0
		 * @param  string $package package name.
		 * @param  string $message error message to add.
		 * @return void
		 */
		public function add_error( $package, $message ) {

			if ( ! isset( $this->package_errors[ $package ] ) ) {
				$this->package_errors[ $package ]   = array();
			}

			$this->package_errors[ $package ][] = $message;
		}

		/**
		 * Get package error messages
		 *
		 * @since  1.1.0
		 * @param  string $package package ID.
		 * @return string
		 */
		public function get_error_message( $package ) {

			if ( ! isset( $this->package_errors[ $package ] ) ) {
				return '';
			}

			$result = '';
			$before = '';

			foreach ( $this->package_errors[ $package ] as $message ) {
				$result .= $before . $message;
				$before = '<br>';
			}

			return $result;
		}

		/**
		 * Check if shop package already installed.
		 *
		 * @since  1.1.0
		 * @param  string $package package name to check.
		 * @return boolean
		 */
		public function is_package_installed( $package ) {
			$installed_packages = get_option( 'monstroid_packages', array() );
			return in_array( $package, $installed_packages );
		}

		/**
		 * Set current package installation status
		 *
		 * @since  1.1.0
		 * @return void|bool
		 */
		public function set_package_status() {

			$package = $this->get_install_type();

			if ( ! $package ) {
				return false;
			}

			$installed_packages = get_option( 'monstroid_packages' );

			if ( ! $installed_packages ) {
				$installed_packages = array( $package );
				add_option( 'monstroid_packages', $installed_packages, '', false );
			}

			if ( ! in_array( $package, $installed_packages ) ) {
				update_option( 'monstroid_packages', $installed_packages );
			}

		}

		/**
		 * Get partial sample data download link by part name
		 *
		 * @since  1.1.0
		 * @param  string $part part name.
		 * @return string
		 */
		public function get_sample_data_part_link( $part ) {

			$key        = get_option( 'monstroid_key' );
			$request_arg = array(
				'action'  => 'get-montroid-package-content',
				'key'     => $key,
				'package' => $part,
			);

			$request_url = add_query_arg( $request_arg, monstroid_dashboard_updater()->api );
			$request     = wp_remote_get( $request_url );
			$data        = wp_remote_retrieve_body( $request );

			if ( empty( $data ) ) {
				return false;
			}

			$data = json_decode( $data, true );

			if ( empty( $data['links']['sample_data'] ) ) {
				return false;
			}

			return $data['links']['sample_data'];
		}

		/**
		 * Get current installation type
		 *
		 * @since  1.1.0
		 * @return string|bool
		 */
		public function get_install_type() {

			if ( ! isset( $_SESSION['monstroid_install_type'] ) ) {
				return false;
			}

			$type     = esc_attr( $_SESSION['monstroid_install_type'] );
			$packages = $this->get_packages();

			if ( ! isset( $packages[ $type ] ) ) {
				return false;
			}

			return $type;

		}

		/**
		 * Prepare package installer via monstroid wizard plugin
		 *
		 * @since  1.1.0
		 * @return void
		 */
		public function prepare_package_installer() {

			// Installation
			add_filter( 'monstroid_wizard_installation_steps', array( $this, 'prepare_package_install_steps' ) );
			add_filter( 'monstroid_wizard_installation_groups', array( $this, 'prepare_package_install_groups' ) );
			add_filter( 'monstroid_wizard_first_step', array( $this, 'set_first_wizard_step' ) );
			$this->prepare_package_plugins();

			// Sample data import
			add_filter( 'cherry_data_manager_cloud_sample_data_url', array( $this, 'set_sample_data_link' ) );
		}

		/**
		 * Modify wizard installation steps
		 *
		 * @since  1.1.0
		 * @param  array $steps default installation steps.
		 * @return array
		 */
		public function prepare_package_install_steps( $steps ) {

			$type = $this->get_install_type();

			if ( ! $type ) {
				return $steps;
			}

			return array( 'install-data-manager', 'install-plugins' );
		}

		/**
		 * Modify wizard installation groups
		 *
		 * @since  1.1.0
		 * @param  array $groups default groups set.
		 * @return array
		 */
		public function prepare_package_install_groups( $groups ) {

			$type = $this->get_install_type();

			if ( ! $type ) {
				return $groups;
			}

			return array(
				'service_plugins'  => array( 'install-data-manager' ),
				'frontend_plugins' => array( 'install-plugins' ),
			);
		}

		/**
		 * Prepare first installation step for package install process
		 *
		 * @since  1.1.0
		 * @param  array $step default first step data.
		 * @return array
		 */
		public function set_first_wizard_step( $step ) {

			$type = $this->get_install_type();

			if ( ! $type ) {
				return $step;
			}

			$step = array(
				'step'      => 'install-data-manager',
				'last_step' => 'no',
				'label'     => __( 'Re-Installing Data Manager to latest version', 'monstroid-dashboard' ),
				'plugin'    => '',
			);

			return $step;
		}

		/**
		 * Prepare current package plugins to install
		 *
		 * @since  1.0.0
		 * @return void|null
		 */
		public function prepare_package_plugins() {

			$package = $this->get_install_type();

			if ( ! $package ) {
				return;
			}

			$hook = 'monstroid_wizard_set_' . $package . '_plugins';

			add_filter( $hook, array( $this, 'set_package_plugins' ), 10, 2 );

		}

		/**
		 * Set current package plugins for installation
		 *
		 * @since  1.1.0
		 * @param  mixed $plugins default plugins set.
		 * @param  mixed $all_plugins all registered plugins set.
		 * @return mixed
		 */
		public function set_package_plugins( $plugins, $all_plugins ) {

			$package = $this->get_install_type();

			if ( ! $package ) {
				return $plugins;
			}

			$packages = $this->get_packages();

			if ( ! isset( $packages[ $package ]['plugins'] ) ) {
				return $plugins;
			}

			$result = array();

			foreach ( $packages[ $package ]['plugins'] as $plugin ) {

				if ( ! isset( $all_plugins[ $plugin ] ) ) {
					continue;
				}

				$result[ $plugin ] = $all_plugins[ $plugin ];
			}

			return $result;

		}

		/**
		 * Set package specific sample data installation link
		 *
		 * @since  1.1.0
		 * @param  string $url default sample data URL.
		 * @return string
		 */
		public function set_sample_data_link( $url ) {

			$package = $this->get_install_type();

			if ( ! $package ) {
				return $url;
			}

			$packages = $this->get_packages();

			if ( empty( $packages[ $package ]['sample_data'] ) ) {
				return $url;
			}

			return $packages[ $package ]['sample_data'];

		}

		/**
		 * Check if Monstroid version is compatible with patial installation.
		 *
		 * @since  1.1.0
		 * @return boolean
		 */
		public function is_valid_monstroid_version() {
			$theme = wp_get_theme( 'monstroid' );
			return version_compare( $theme->get( 'Version' ), $this->required_monstroid_version, '>=' );
		}

		/**
		 * Check if is valid wizard version installed. Returns 'false' if wizard not installed
		 *
		 * @since  1.1.0
		 * @return bool
		 */
		public function is_valid_wizard_version() {
			return $this->is_valid_plugin_version( 'monstroid-wizard' );
		}

		/**
		 * Check if is valid wizard version installed. Returns 'false' if wizard not installed
		 *
		 * @since  1.1.0
		 * @return bool
		 */
		public function is_valid_data_manager_version() {
			return $this->is_valid_plugin_version( 'cherry-data-manager' );
		}

		/**
		 * Check if passed plugin version is valid for packages management
		 *
		 * @since  1.1.0
		 * @param  string $plugin plugin slug to check.
		 * @return boolean
		 */
		public function is_valid_plugin_version( $plugin = 'monstroid-wizard' ) {

			$path_data   = pathinfo( monstroid_dashboard()->plugin_dir() );
			$plugins_dir = isset( $path_data['dirname'] ) ? $path_data['dirname'] : false;

			if ( ! $plugins_dir ) {
				return false;
			}

			if ( ! file_exists( $plugins_dir . '/' . $plugin . '/' . $plugin . '.php' ) ) {
				return false;
			}
			$plugin_data = get_plugin_data( $plugins_dir . '/' . $plugin . '/' . $plugin . '.php' );

			if ( ! $plugin_data ) {
				return false;
			}

			$versions = array(
				'monstroid-wizard'    => $this->required_wizard_version,
				'cherry-data-manager' => $this->required_data_manager_version,
			);

			if ( ! isset( $versions[ $plugin ] ) ) {
				return false;
			}

			return version_compare( $plugin_data['Version'], $versions[ $plugin ], '>=' );

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

}

/**
 * Returns instance of Monstroid_Dashboard_Packages
 *
 * @return object
 */
function monstroid_dashboard_packages() {
	return Monstroid_Dashboard_Packages::get_instance();
}

monstroid_dashboard_packages();
