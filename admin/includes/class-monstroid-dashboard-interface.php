<?php
/**
 * Re-build WordPress Admin menu for Monstroid Dasboard and load interface
 *
 * @package   monstroid_dashboard
 * @author    Cherry Team
 * @license   GPL-2.0+
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// If class 'Monstroid_Dashboard_Interface' not exists.
if ( ! class_exists( 'Monstroid_Dashboard_Interface' ) ) {

	/**
	 * Inaterface management class
	 */
	final class Monstroid_Dashboard_Interface {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Monstroid menu start position
		 *
		 * @since 1.0.0
		 * @var   int
		 */
		public $start_position = 101;

		/**
		 * Custom post type position change iteration
		 *
		 * @since 1.0.0
		 * @var int
		 */
		public $iter = 1;

		/**
		 * Subpages list for Monstroid menu
		 *
		 * @since 1.0.0
		 * @var   array
		 */
		public $subpages = null;

		/**
		 * Class constructor
		 */
		function __construct() {

			// add menu separators
			add_action( 'admin_menu', array( $this, 'add_separators' ) );
			// add menu pages
			add_action( 'admin_menu', array( $this, 'register_new_pages' ) );
			// Replace mailchimp, woocommerce, etc.
			add_action( 'admin_menu', array( $this, 'replace_misc' ), 110 );
			// replace YIT plugins
			add_filter( 'yit_plugins_menu_item_position', array( $this, 'replace_yit' ) );
			// Duplicate YIT items hack
			do_action( 'yit_after_add_settings_page', array( $this, 'remove_duplicate_yit_pages' ) );
			// replace Cherry menu item
			add_filter( 'cherry_menu_item_args', array( $this, 'replace_cherry' ) );

			$this->replace_related_post_types();
		}

		/**
		 * Move YIT-related menu items below
		 *
		 * @since  1.0.0
		 * @param  string $position default position.
		 * @return string
		 */
		function replace_yit( $position ) {
			remove_menu_page( 'yit_plugin_panel' );
			return $position;
		}

		/**
		 * Remove dublicate submenu
		 *
		 * @return void
		 */
		function remove_duplicate_yit_pages() {
			remove_submenu_page( 'yit_plugin_panel', 'yit_plugin_panel' );
		}

		/**
		 * Add menu separators for monstroid group
		 *
		 * @since 1.0.0
		 */
		function add_separators() {
			global $menu;

			$positions = array(
				$this->validate_position( $this->start_position ),
				$this->validate_position( $this->start_position + $this->iter + 1 ),
			);

			foreach ( $positions as $position ) {
				$menu[ $position ] = array(
					0 => '',
					1 => 'read',
					2 => 'separator' . $position,
					3 => '',
					4 => 'wp-menu-separator',
				);
			}

		}

		/**
		 * Register new admin pages for monstoid
		 *
		 * @since  1.0.0
		 * @return void
		 */
		function register_new_pages() {

			// Register main menu item
			add_menu_page(
				__( 'Monstroid Dashboard', 'monstroid-dashboard' ),
				sprintf( __( 'Monstroid %s', 'monstroid-dashboard' ), $this->get_menu_badge() ),
				'manage_options',
				'monstroid-dashboard',
				array( $this, 'build_page' ),
				monstroid_dashboard()->plugin_url( 'assets/images/icon.png' ),
				$this->validate_position( $this->start_position + 1 )
			);

			// Register subitems
			foreach ( $this->subpages() as $slug => $data ) {
				add_submenu_page(
					'monstroid-dashboard',
					$data['page-title'],
					$data['menu-title'],
					'manage_options',
					$slug,
					array( $this, 'build_page' )
				);
			}
		}

		/**
		 * Replace Cherry menu item into monstroid group
		 *
		 * @since  1.0.0
		 * @param  array $args post type arguments array.
		 * @return array
		 */
		public function replace_cherry( $args ) {
			$this->iter++;
			$args['position'] = $this->validate_position( $this->start_position + 2 );
			return $args;
		}

		/**
		 * Replace Monstroid-related custom post types into Monstroid group
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function replace_related_post_types() {

			$filters = apply_filters(
				'monstroid_dashboard_custom_post_types_args',
				array(
					'cherry_testimonials_post_type_args',
					'cherry_portfolio_post_type_args',
					'cherry_services_post_type_args',
					'cherry_team_post_type_args',
					'cherry_slider_post_type_args',
					'cherry_chart_post_type_args',
					'cherry_clients_post_type_args',
				)
			);

			$this->iter++;

			foreach ( $filters as $filter ) {
				add_filter( $filter, array( $this, 'change_position' ) );
			}

		}

		/**
		 * Change position custom post types position
		 *
		 * @since  1.0.0
		 * @param  array $args default post type arguments.
		 * @return array
		 */
		public function change_position( $args ) {
			$this->iter++;
			$args['menu_position'] = $this->validate_position( $this->start_position + $this->iter );
			return $args;
		}

		/**
		 * Validate menu position to prevent erasing existing menu items.
		 *
		 * @since  1.0.1
		 * @param  int $postition position to set into it.
		 * @return int
		 */
		public function validate_position( $postition ) {

			global $menu;

			while ( ! empty( $menu[ $postition ] ) ) {
				$postition++;
			}

			return $postition;

		}

		/**
		 * Get registered subpages list for Monstroid menu
		 *
		 * @since  1.0.0
		 * @return array
		 */
		public function subpages() {

			if ( null !== $this->subpages ) {
				return $this->subpages;
			}

			$this->subpages = apply_filters(
				'monstroid_dashboard_subpages',
				array(
					'monstroid-dashboard' => array(
						'page-title' => __( 'Monstroid Dashboard', 'monstroid-dashboard' ),
						'menu-title' => __( 'Dashboard', 'monstroid-dashboard' ),
						'depends'    => array(
							monstroid_dashboard()->plugin_dir( 'admin/includes/class-monstroid-dashboard-tips.php' )
						),
					),
					'monstroid-updater' => array(
						'page-title' => __( 'Data manager', 'monstroid-dashboard' ),
						'menu-title' => sprintf( __( 'Data manager %s', 'monstroid-dashboard' ), $this->get_menu_badge() ),
					),
					/*
					Temporary comment out themes page

					'monstroid-themes' => array(
						'page-title' => __( 'Monstroid Themes', 'monstroid-dashboard' ),
						'menu-title' => __( 'Themes', 'monstroid-dashboard' )
					)
					*/
				)
			);

			if ( empty( $this->subpages ) || ! is_array( $this->subpages ) ) {
				return array();
			}

			return $this->subpages;
		}

		/**
		 * Get menu badge when update avaliable
		 *
		 * @since  1.0.0
		 * @return string
		 */
		public function get_menu_badge() {
			return ( monstroid_dashboard_updater()->force_has_update() )
					? '<span class="update-plugins count-1 md-badge" ><span class="update-count">1</span></span>'
					: '';
		}

		/**
		 * Build dashboard submenu page
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function build_page() {

			$subpages = $this->subpages();
			$pages = array_unique(
				array_merge(
					array( 'monstroid-dashboard' ),
					array_keys( $subpages )
				)
			);

			$current = isset( $_GET['page'] ) ? esc_attr( $_GET['page'] ) : false;

			if ( ! in_array( $current, $pages ) ) {
				wp_die( 'Page not exists' );
			}

			$depends = isset( $subpages[ $current ]['depends'] ) ? $subpages[ $current ]['depends'] : false;

			if ( ! monstroid_dashboard()->is_monstroid_installed() ) {
				$this->open_page_wrap();
				printf(
					'<div class="md-content">%s</div>',
					__( 'Monstroid theme not installed on this site', 'monstroid-dashboard' )
				);
				$this->close_page_wrap();
				return;
			}

			$this->open_page_wrap();
			$this->load_view( $current, $depends );
			$this->close_page_wrap();

		}

		/**
		 * Open admin page wrapper
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function open_page_wrap() {
			echo '<div class="wrap">';
		}

		/**
		 * Close admin page wrapper
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function close_page_wrap() {
			echo '</div>';
		}

		/**
		 * Load selecte page view
		 *
		 * @since  1.0.0
		 * @param  string     $view    get specific page output.
		 * @param  bool|array $depends view dependencies array.
		 * @return void|bool false
		 */
		public function load_view( $view = null, $depends = false ) {

			if ( ! $view ) {
				return false;
			}

			$subpages = $this->subpages();

			if ( false !== $depends ) {
				foreach ( $depends as $file ) {
					if ( ! file_exists( $file ) ) {
						continue;
					}
					include_once $file;
				}
			}

			if ( file_exists( monstroid_dashboard()->plugin_dir( 'admin/views/' . $view . '.php' ) ) ) {
				include monstroid_dashboard()->plugin_dir( 'admin/views/' . $view . '.php' );
			} else {
				do_action( 'monstroid_wizard_page_' . $view );
			}

		}

		/**
		 * Replace Mail Chimp menu item below monstroid
		 *
		 * @since  1.0.0
		 * @return void
		 */
		function replace_misc() {

			global $menu;

			$replace_by_slug = apply_filters(
				'monstroid_dashboard_replace_by_slug',
				array(
					'woocommerce',
					'yit_plugin_panel',
					'wpcf7',
					'cherry-white-label-settings',
					'mailchimp-for-wp',
				)
			);

			foreach ( $replace_by_slug as $slug ) {
				$this->replace_by_slug( $slug );
			}

		}

		/**
		 * Replace menu item by slug
		 *
		 * @since  1.0.0
		 * @return void
		 */
		function replace_by_slug( $slug = null ) {

			if ( ! $slug ) {
				return;
			}

			global $menu;

			foreach ( $menu as $position => $item ) {

				if ( ! in_array( $slug, $item ) ) {
					continue;
				}

				$this->iter++;
				$new_index = $this->validate_position( $this->start_position + $this->iter );

				do {
					$this->iter++;
					$new_index = $this->validate_position( $this->start_position + $this->iter );
				} while ( isset( $menu[ $new_index ] ) );

				unset( $menu[ $position ] );
				$menu[ $new_index ] = $item;
				break;
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
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}

	Monstroid_Dashboard_Interface::get_instance();

}
