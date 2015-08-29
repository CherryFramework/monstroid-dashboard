<?php
/**
 * Build get strated block for monatroid dashboard
 *
 * @package   monstroid_dashboard
 * @author    Cherry Team
 * @license   GPL-2.0+
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// If class 'Monstroid_Dashboard_Tips' not exists.
if ( ! class_exists( 'Monstroid_Dashboard_Tips' ) ) {

	final class Monstroid_Dashboard_Tips {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Get tips list
		 *
		 * @since  1.0.0
		 * @return array
		 */
		public function get_tips_list() {
			return apply_filters(
				'monstroid_dashboard_tips_list',
				array(
					array(
						'id'         => 'manage-options',
						'title'      => __( 'Start customize', 'monstroid-dashboard' ),
						'desc'       => __( 'Go to Theme options manager', 'monstroid-dashboard' ),
						'link'       => '#',
						'link_label' => __( 'Start', 'monstroid-dashboard' ),
						'tutorial'   => '#'
					),
					array(
						'id'         => 'write-post',
						'title'      => __( 'Write your first post', 'monstroid-dashboard' ),
						'desc'       => __( 'Write your first post', 'monstroid-dashboard' ),
						'link'       => '#',
						'link_label' => __( 'Start', 'monstroid-dashboard' ),
						'tutorial'   => '#'
					)
				)
			);
		}

		/**
		 * Get useful links list
		 *
		 * @since  1.0.0
		 * @return array
		 */
		public function get_links_list() {
			return apply_filters(
				'monstroid_dashboard_links_list',
				array(
					array(
						'label' => __( 'Monstroid documentation', 'monstroid-dashboard' ),
						'url'   => 'http://www.templatemonster.com/help/quick-start-guide/wordpress-themes/monstroid/'
					),
					array(
						'label' => __( 'Quick Start Guide', 'monstroid-dashboard' ),
						'url'   => 'http://www.templatemonster.com/help/quick-start-guide/wordpress-themes/monstroid/quick_guide/index_en.html'
					),
					array(
						'label' => __( 'Monstroid Video Tutorials', 'monstroid-dashboard' ),
						'url'   => 'http://www.templatemonster.com/help/cms-blog-templates/monstroid/monstroid-tutorials/'
					)
				)
			);
		}

		/**
		 * Print quick guide tips list
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function get_tips() {

			$default_label = __( 'Strat', 'monstroid-dashboard' );

			foreach ( $this->get_tips_list() as $tip ) {
				include monstroid_dashboard()->plugin_dir( 'admin/views/single-tip.php' );
			}

		}

		/**
		 * Get useful links list
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function get_links() {

			$link_tmpl = '<li class="md-links_item"><a href="%2$s">%1$s</a></li>';
			$list      = '';

			foreach ( $this->get_links_list() as $link ) {
				$list .= sprintf( $link_tmpl, $link['label'], $link['url'] );
			}

			if ( ! $list ) {
				return;
			}

			printf( '<ul class="md-links">%s</ul>', $list );
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

	$monstroid_dashboard_tips = Monstroid_Dashboard_Tips::get_instance();
}