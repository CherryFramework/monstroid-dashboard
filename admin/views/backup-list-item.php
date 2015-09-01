<?php
/**
 * Updates list item
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
<div class="md-updates-list_item">
	<div class="md-updates-list_item_name">
		<?php echo $data['name']; ?>
	</div>
	<div class="md-updates-list_item_date">
		<?php echo $data['date']; ?>
	</div>
	<div class="md-updates-list_item_download">
		<a href="<?php echo $download_url; ?>" class="md-updates-list_download_link">
			<span class="dashicons dashicons-download"></span>
			<?php _e( 'Download', 'monstroid-dashboard' ); ?>
		</a>
	</div>
</div>