<?php
/**
 * Themes list manager
 *
 * @package   monstroid_dashboard
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Monstroid_Dashboard_Themes_List {

	/**
	 * A reference to an instance of this class.
	 *
	 * @since 1.1.0
	 * @var   object
	 */
	private static $instance = null;

	/**
	 * Current themes query data
	 *
	 * @since 1.1.0
	 * @var   object
	 */
	private $query_data = array();

	function __construct() {
		add_action( 'wp_ajax_monstroid_dashboard_get_themes_page', array( $this, 'pager_callbak' ) );
	}

	/**
	 * Get TM themes list array
	 *
	 * @since  1.1.0
	 * @param  int $page     page number to show
	 * @param  int $per_page themes count per page
	 * @return array
	 */
	public function get_themes( $page = 1, $per_page = 4 ) {

		$page     = absint( $page );
		$per_page = absint( $per_page );

		if ( ! $page ) {
			$page = 1;
		}

		if ( ! $per_page ) {
			$per_page = 4;
		}

		$offset = $per_page * ( $page - 1 );

		$url = add_query_arg(
			array(
				'cherry_action' => 'get-themes-list',
				'offset'        => $offset,
				'per_page'      => $per_page
			),
			monstroid_dashboard_updater()->api
		);

		$response = wp_remote_get( $url );

		$response = $this->validate_response( $response );

		if ( ! $response ) {
			return array();
		}

		$this->clear_screens_data();

		$total_pages = ceil( $response['count'] / $per_page );
		// prepare current query data to build pager after themes list
		$this->query_data = array(
			'page'        => $page,
			'total_pages' => $total_pages
		);

		return $response['themes'];

	}

	/**
	 * Get TM themes list array
	 *
	 * @since  1.1.0
	 * @param  int $page     page number to show
	 * @param  int $per_page themes count per page
	 * @return void
	 */
	public function build_themes_list( $page = 1, $per_page = 4 ) {

		$themes = $this->get_themes( $page, $per_page );

		if ( empty( $themes ) ) {
			return;
		}

		foreach ( $themes as $theme ) {
			$this->store_large_screen( $theme['template_id'], $theme['screen_lg'] );
			include monstroid_dashboard()->plugin_dir( 'admin/views/themes-list-item.php' );
		}

	}

	/**
	 * Build themes list pager
	 *
	 * @since  1.1.0
	 * @return string
	 */
	public function build_pager() {

		if ( empty( $this->query_data ) ) {
			return;
		}

		$page_format = '<div class="md-themes_pager_item">
			<a href="#" data-page="%1$s" class="md-themes_pager_link page-item%2$s">%1$s</a>
		</div>';
		$direct_format = '<div class="md-themes_pager_item">
			<a href="#" data-page="%1$s" class="md-themes_pager_link%2$s %4$s">%3$s</a>
		</div>';

		$pages = '';

		for ( $i = 1; $i <= $this->query_data['total_pages']; $i++) {

			$current = ( $this->query_data['page'] == $i ) ? ' current-page' : '';

			$pages .= sprintf(
				$page_format,
				$i, $current
			);
		}

		if ( $this->query_data['page'] == 1 ) {
			$prev_num      = $this->query_data['page'];
			$prev_disabled = ' disabled';
		} else {
			$prev_num      = $this->query_data['page'] - 1;
			$prev_disabled = '';
		}

		if ( $this->query_data['page'] == $this->query_data['total_pages'] ) {
			$next_num      = $this->query_data['page'];
			$next_disabled = ' disabled';
		} else {
			$next_num      = $this->query_data['page'] + 1;
			$next_disabled = '';
		}

		$prev = sprintf(
			$direct_format,
			$prev_num, $prev_disabled, '<span class="dashicons dashicons-arrow-left-alt2"></span>', 'prev-page'
		);

		$next = sprintf(
			$direct_format,
			$next_num, $next_disabled, '<span class="dashicons dashicons-arrow-right-alt2"></span>', 'next-page'
		);

		printf( '<div class="md-themes_pager">%s</div>', $prev . $pages . $next );

	}

	/**
	 * Ajax pager callack for themes list
	 *
	 * @since  1.1.0
	 * @return void
	 */
	public function pager_callbak() {

		if ( ! current_user_can( 'switch_themes' ) ) {
			die();
		}

		$page = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : false;

		if ( ! $page ) {
			die();
		}
		?>
		<ul class="md-themes_list">
		<?php
			$this->build_themes_list( $page, 4 );
		?>
		</ul>
		<?php
			$this->build_pager();

		die();

	}

	/**
	 * Store large screen for next step
	 *
	 * @since  1.1.0
	 * @return void
	 */
	public function store_large_screen( $id, $screen ) {

		if ( ! isset( $_SESSION['cherry_screens'] ) ) {
			$_SESSION['cherry_screens'] = array();
		}

		$_SESSION['cherry_screens'][$id] = $screen;

	}

	/**
	 * Remove screens data before new themes list building
	 *
	 * @since  1.1.0
	 * @return void
	 */
	public function clear_screens_data() {
		if ( isset( $_SESSION['cherry_screens'] ) ) {
			$_SESSION['cherry_screens'] = array();
		}
	}

	/**
	 * Check if we get valid response from wizard API
	 *
	 * @since  1.1.0
	 * @param  array|WP_Error  $response  API response
	 * @return bool|array
	 */
	public function validate_response( $response ) {

		if ( is_wp_error( $response ) ) {
			return false;
		}

		if ( ! isset( $response['response']['code'] ) || 200 != $response['response']['code'] ) {
			return false;
		}

		$body = $response['body'];

		$body = json_decode( $body, true );

		if ( ! is_array( $body ) ) {
			return false;
		}

		return $body;

	}

	/**
	 * Returns the instance.
	 *
	 * @since  1.1.0
	 * @return object
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance )
			self::$instance = new self;

		return self::$instance;
	}

}