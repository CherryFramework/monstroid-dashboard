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
<div class="md-items-list package-item">
	<div class="md-items-list_screen">
		<img src="<?php echo $package['thumb']; ?>" alt="<?php echo $package['title']; ?>">
	</div>
	<h3 class="md-items-list_title"><?php echo $package['title']; ?></h3>
	<div class="md-items-list_actions">
		<?php if ( false === $has_errors ) : ?>

		<a href="<?php echo $install_link; ?>" class="md-button md-success run-package-install">
			<span class="dashicons dashicons-download"></span>
			<?php _e( 'Install', 'monstroid-dashboard' ); ?>
		</a>
		<?php else : ?>
		<?php echo $this->get_error_message( $package_id ); ?>
		<?php endif; ?>
	</div>
</div>
