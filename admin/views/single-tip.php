<?php
/**
 * Monstroid dashboard single quick start tip temlater
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
<div class="md-tips_item" id="tip-<?php echo $tip['id']; ?>">
	<div class="md-tips_item_content">
		<?php if ( ! empty( $tip['title'] ) ) { ?>
			<h3 class="md-tips_item_title"><?php echo $tip['title']; ?></h3>
		<?php } ?>
		<?php if ( ! empty( $tip['desc'] ) ) { ?>
			<div class="md-tips_item_desc"><?php echo $tip['desc']; ?></div>
		<?php } ?>
	</div>
	<div class="md-tips_item_links">
		<?php if ( ! empty( $tip['link'] ) ) { ?>
			<a href="<?php echo esc_url( $tip['link'] ); ?>"><?php echo isset( $tip['link_label'] ) ? $tip['link_label'] : $default_label; ?></a>
		<?php } ?>
		<?php if ( ! empty( $tip['tutorial'] ) ) { ?>
			<a href="<?php echo esc_url( $tip['tutorial'] ); ?>"><?php echo __( 'Tutorial', 'monstroid-dashboard' ); ?></a>
		<?php } ?>
	</div>
</div>