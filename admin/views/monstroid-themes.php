<?php
/**
 * Monstroid child themes list
 *
 * @package   monstroid_dashboard
 * @author    Cherry Team
 * @license   GPL-2.0+
 * @version   1.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>
<div class="md-content">
<?php
if ( ! monstroid_dashboard()->is_wizard_active() ) {
	_e( 'Please, install and activate Monstroid Wizard plugin to allow child themes installation', 'monstroid-dashboard' );
}
?>
	<div class="md-themes">
		<h2 class="md-themes_title"><?php _e( 'Monstroid child themes', 'monstroid-dashboard' ); ?></h2>
		<div class="md-themes_content">
			<ul class="md-themes_list">
				<?php
					$themes_api = Monstroid_Dashboard_Themes_List::get_instance();
					$themes_api->build_themes_list();
				?>
			</ul>
			<?php $themes_api->build_pager(); ?>
		</div>
	</div>
</div>