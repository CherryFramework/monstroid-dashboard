<?php
/**
 * Monstroid dashboard single quick start tip temlater
 *
 * @package   monstroid_dashboard
 * @author    Cherry Team
 * @license   GPL-2.0+
 */
?>
<li class="md-themes_list_item">
	<div class="wizard-theme-item">
		<h4 class="wizard-theme-item-title">
			<?php printf( __( 'Theme %s', 'monstroid-dashboard' ), $theme['template_id'] ); ?>
		</h4>
		<div class="wizard-theme-item-thumb">
			<img src="<?php echo esc_url( $theme['screen_md'] ); ?>" alt="">
		</div>
		<div class="wizard-theme-item-actions">
			<a href="<?php echo esc_url( $theme['livedemo'] ); ?>" class="md-button md-success" target="_blank">
				<span class="dashicons dashicons-desktop"></span><?php _e( 'Live Demo', 'monstroid-dashboard' ); ?>
			</a>
			<a href="#" data-template="<?php echo $theme['template_id']; ?>" class="md-button install-child">
				<span class="dashicons dashicons-download"></span><?php _e( 'Install', 'monstroid-dashboard' ); ?>
			</a>
		</div>
	</div>
</li>