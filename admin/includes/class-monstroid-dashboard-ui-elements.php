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

class Monstroid_Dashboard_UI {

	/**
	 * Get force check updates button HTML
	 *
	 * @since  1.0.0
	 * @param  String $label button label
	 * @param  string $class additional button CSS class
	 * @return string
	 */
	public static function check_update_button( $label = null, $class = '' ) {

		$url = esc_url( add_query_arg( array( 'md_force_check_update' => 1 ) ) );

		if ( ! $label ) {
			$label = __( 'Check if updates are available', 'monstroid-dashboard' );
		}

		if ( $class ) {
			$class = 'md-button md-link ' . $class;
		} else {
			$class = 'md-button md-link';
		}

		return sprintf(
			'<a href="%1$s" class="%2$s"><span class="dashicons dashicons-image-rotate"></span> %3$s</a>',
			$url, $class, $label
		);

	}

	/**
	 * Disable automatic updates checking
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public static function disable_update_button() {

		$url   = esc_url( add_query_arg( array( 'md_disable_auto_updates' => 1 ) ) );
		$label = __( 'Disable automatic updates', 'monstroid-dashboard' );

		return sprintf( '<a href="%s" class="md-button md-warning md-small disable-updates">%s</a>', $url, $label );

	}

	/**
	 * Enable automatic updates checking
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public static function enable_update_button() {

		$url   = esc_url( add_query_arg( array( 'md_enable_auto_updates' => 1 ) ) );
		$label = __( 'Enable automatic updates', 'monstroid-dashboard' );

		return sprintf( '<a href="%s" class="md-button md-small md-success enable-updates">%s</a>', $url, $label );

	}

	/**
	 * Download latest monstroid version
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public static function download_latest() {

		return sprintf(
			'<a href="#" class="md-button download-latest"><span class="dashicons dashicons-download"></span> %s</a>',
			__( 'Download the latest Monstroid .ZIP version', 'monstroid-dashboard' )
		);

	}

	/**
	 * Show main Monstroid theme updater box
	 *
	 * @since 1.0.0
	 */
	public static function main_theme_box() {

		$screen_url = monstroid_dashboard()->plugin_url( 'assets/images/monstroid-screen.png' );
		$title      = __( 'Monstroid', 'monstroid-dashboard' );

		ob_start();
		include monstroid_dashboard()->plugin_dir( 'admin/views/main-theme-item.php' );
		return ob_get_clean();

	}

	/**
	 * Check if license key is provided and show form to enter it, if not
	 *
	 * @since  1.0.0
	 */
	public static function check_license_key() {

		$key = get_option( 'monstroid_key', false );

		if ( false !== $key ) {
			return false;
		}

		ob_start();
		include monstroid_dashboard()->plugin_dir( 'admin/views/enter-key-form.php' );
		return ob_get_clean();

	}

	/**
	 * Show avaliable backups list
	 *
	 * @since 1.0.0
	 */
	public static function backups_list() {

		include_once( monstroid_dashboard()->plugin_dir( 'admin/includes/class-monstroid-dashboard-backup-manager.php' ) );

		$backup_manager = Monstroid_Dashboard_Backup_Manager::get_instance();
		$backups        = $backup_manager->get_backups();

		if ( empty( $backups ) ) {
			return;
		}

		ob_start();
		?>
		<div class="md-updates-list">
			<h4 class="md-updates-list_title"><?php _e( 'Monstroid theme backups', 'monstroid-dashboard' ); ?></h4>
			<div class="md-updates-list_items">
				<div class="md-updates-list_item heading_item">
					<div class="md-updates-list_item_name">
						<?php _e( 'File name', 'monstroid-dashboard' ); ?>
					</div>
					<div class="md-updates-list_item_date">
						<?php _e( 'Created', 'monstroid-dashboard' ); ?>
					</div>
					<div class="md-updates-list_item_download">
						<?php _e( 'Actions' ); ?>
					</div>
				</div>
				<?php
				foreach ( $backups as $file => $data ) {
					// prepare download and delete URLs to use in template
					$download_url = add_query_arg(
						array(
							'action' => 'monstroid_dashboard_get_backup',
							'file'   => $data['name']
						),
						admin_url( 'admin-ajax.php' )
					);
					$download_url = wp_nonce_url(
						$download_url, 'monstroid-dashboard', 'nonce'
					);

					$delete_url = add_query_arg(
						array(
							'action' => 'monstroid_dashboard_delete_backup',
							'file'   => $data['name']
						),
						admin_url( 'admin-ajax.php' )
					);
					$delete_url = wp_nonce_url(
						$delete_url, 'monstroid-dashboard', 'nonce'
					);

					include monstroid_dashboard()->plugin_dir( 'admin/views/backup-list-item.php' );
				}
				?>
			</div>
		</div>
		<?php
		return ob_get_clean();

	}

}