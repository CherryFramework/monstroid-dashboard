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
	<h3 class="md-items-list_title"><?php echo $title; ?></h3>
	<div class="md-items-list_actions">
		<?php if ( monstroid_dashboard_updater()->force_has_update() ) { ?>
		<?php
			$filesystem_method = monstroid_dashboard()->filesystem->check_filesystem_method();
			$class = ( true !== $filesystem_method ) ? ' disabled no-creds' : '';
		?>
		<a href="#" class="md-button md-success run-theme-update<?php echo $class; ?>">
			<span class="dashicons dashicons-update"></span>
			<?php _e( 'Update', 'monstroid-dashboard' ); ?>
		</a>
		<?php
			if ( true !== $filesystem_method ) {
				printf(
					'<div class="md-message md-warning">%s</div>',
					__( 'Please, set up filesystem credentials to allow automatic update', 'monstroid-dashboard' )
				);
			}
		?>
		<?php } ?>
		<div class="md-update-messages"></div>
		<div class="md-update-log md-hidden"></div>
		<?php echo Monstroid_Dashboard_UI::check_update_button(); ?>
		<?php
			if ( isset( $_REQUEST['md_force_check_update'] ) ) {
				echo monstroid_dashboard_updater()->check_update_messages();
			}
		?>
	</div>
</div>