<?php
/**
 * Enter monstroid key form
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
<div class="md-key-form monstroid-notice updated">
	<form method="POST" action="">
		<p><?php _e( 'Please enter your license key. We suggest to visit your Monstroid deliver page in order to locate the license key and paste it here.', 'monstroid-dashboard' );
		?></p>
		<input type="text" name="monstroid-key" placeholder="<?php _e( 'Enter theme activation key', 'monstroid-dashboard' ); ?>" class="md-input">
		<a href="#" class="md-button save-license-key"><?php _e( 'Save', 'monstroid-dashboard' ); ?></a>
	</form>
</div>
