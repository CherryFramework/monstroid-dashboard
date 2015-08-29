<?php
/**
 * Monstroid dashboard main page template
 *
 * @package   monstroid_dashboard
 * @author    Cherry Team
 * @license   GPL-2.0+
 * @version   1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>
<div class="md-content">
	<div class="md-updater-service-actions">
		<?php
			// Show enable/disable update buttons
			$updater_options = monstroid_dashboard_updater()->get_options();
			if ( $updater_options['disable_auto_check'] ) {
				echo Monstroid_Dashboard_UI::enable_update_button();
			} else {
				echo Monstroid_Dashboard_UI::disable_update_button();
			}
		?>
	</div>
	<h2><?php _e( 'Monstroid Updater', 'monstroid-dashboard' ); ?></h2>
	<?php echo Monstroid_Dashboard_UI::check_license_key(); ?>
	<div class="md-updater-items">
		<?php echo Monstroid_Dashboard_UI::main_theme_box(); ?>
	</div>
	<div class="md-updater-footer">
		<?php echo Monstroid_Dashboard_UI::download_latest(); ?>
	</div>
</div>