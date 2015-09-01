<?php
/**
 * Monstroid dashboard main theme  item
 *
 * @package   monstroid_dashboard
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>
<div class="md-items-list main-theme">
	<div class="md-items-list_screen">
		<img src="<?php echo $screen_url; ?>" alt="<?php echo $title; ?>">
	</div>
	<h3 class="md-items-list_title">
		<?php echo $title; ?> v<?php echo monstroid_dashboard_updater()->get_current_version(); ?>
	</h3>
	<div class="md-items-list_actions">
		<?php if ( monstroid_dashboard_updater()->force_has_update() ) { ?>
		<?php
			$filesystem_method = monstroid_dashboard()->filesystem->check_filesystem_method();
			$class = ( true !== $filesystem_method ) ? ' disabled no-creds' : '';
		?>
		<a href="#" class="md-button md-success run-theme-update<?php echo $class; ?>">
			<span class="dashicons dashicons-update"></span>
			<?php
				printf(
					__( 'Update to v%s', 'monstroid-dashboard' ),
					monstroid_dashboard_updater()->get_update_data( 'new_version' )
				);
			?>
		</a>
		<?php
			if ( true !== $filesystem_method ) {
				printf(
					'<div class="md-message md-warning">%s</div>',
					__( 'Please, set up filesystem credentials to allow automatic update', 'monstroid-dashboard' )
				);
			}
		?>
		<div class="md-misc-messages">
			<?php _e( 'We will perform full backup prior updating your data', 'monstroid-dashboard' ); ?>
		</div>
		<?php } ?>
		<div class="md-update-messages"></div>
		<div class="md-update-log md-hidden"></div>
		<?php if ( ! monstroid_dashboard_updater()->force_has_update() ) { ?>
			<?php echo Monstroid_Dashboard_UI::check_update_button(); ?>
			<?php
				if ( isset( $_REQUEST['md_force_check_update'] ) ) {
					echo monstroid_dashboard_updater()->check_update_messages();
				}
			?>
		<?php } ?>
	</div>
</div>