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
		 * Constructor for the class
		 */
		function __construct() {
			$this->prepare_package_installer();
		}

		/**
		 * Get registered packeges array.
		 *
		 * @since  1.1.0
		 * @return array
		 */
		public function get_packages() {

			if ( null !== $this->packages ) {
				return $this->packages;
			}

			$this->packages = array(
				'woocommerce' => array(
					'title'        => __( 'Shop', 'monstroid-dashboard' ),
					'thumb'        => monstroid_dashboard()->plugin_url( 'assets/images/woocommerce-screen.png' ),
					'installed_cb' => array( $this, 'is_shop_installed' ),
					'plugins'      => apply_filters( 'monstroid_dashboard_shop_plugins', array( 'woocommerce' ) ),
					'sample_data'  => $this->get_sample_data_part_link( 'shop' ),
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

			foreach ( $packages as $package_id => $package ) {

				$is_installed = call_user_func( $package['installed_cb'] );
				$install_link = apply_filters( 'monstroid_dashboard_package_installation_link', '#', $package );
				$install_link = add_query_arg( array( 'package' => $package_id ), $install_link );

				include monstroid_dashboard()->plugin_dir( 'admin/views/package-item.php' );
			}

		}

		/**
		 * Check if shop package already installed.
		 *
		 * @since  1.1.0
		 * @return boolean
		 */
		public function is_shop_installed() {
			return false;
		}

		/**
		 * Get partial sample data download link by part name
		 *
		 * @since  1.1.0
		 * @param  string $part part name.
		 * @return string
		 */
		public function get_sample_data_part_link( $part ) {
			return 'link';
		}

		/**
		 * Prepare package installer via monstroid wizard plugin
		 *
		 * @since  1.1.0
		 * @return void
		 */
		public function prepare_package_installer() {
			add_filter( 'monstroid_wizard_installation_steps', array( $this, 'prepare_package_install_steps' ) );
			add_filter( 'monstroid_wizard_installation_groups', array( $this, 'prepare_package_install_groups' ) );
			add_filter( 'monstroid_wizard_first_step', array( $this, 'set_first_wizard_step' ) );
			$this->prepare_package_plugins();
		}

		/**
		 * Modify wizard installation steps
		 *
		 * @since  1.1.0
		 * @param  array $steps default installation steps.
		 * @return array
		 */
		public function prepare_package_install_steps( $steps ) {
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

			if ( empty( $_SESSION['monstroid_install_type'] ) ) {
				return;
			}

			$package = esc_attr( $_SESSION['monstroid_install_type'] );
			$hook    = 'monstroid_wizard_set_' . $package . '_plugins';

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

			if ( empty( $_SESSION['monstroid_install_type'] ) ) {
				return $plugins;
			}

			$packages = $this->get_packages();
			$package  = esc_attr( $_SESSION['monstroid_install_type'] );

			if ( ! isset( $packages[ $package ] ) ) {
				return $plugins;
			}

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
