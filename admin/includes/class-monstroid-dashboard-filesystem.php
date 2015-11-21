<?php
/**
 * Define filesystem related methods
 *
 * @package   monstroid_dashboard
 * @author    Cherry Team
 * @license   GPL-2.0+
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// If class 'Monstroid_Dashboard_Filesystem' not exists.
if ( ! class_exists( 'Monstroid_Dashboard_Filesystem' ) ) {

	/**
	 * Filesystem management class
	 */
	final class Monstroid_Dashboard_Filesystem {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Try automatically set filesystem credentials if required
		 */
		public function add_creds() {
			add_filter( 'request_filesystem_credentials', array( $this, 'maybe_set_cred' ), 10, 7 );
		}

		/**
		 * Maybe rewrite filesystem credentials
		 *
		 * @since  1.0.0
		 *
		 * @param  mixed  $credentials  Form output to return instead. Default empty.
		 * @param  string $form_post    URL to POST the form to.
		 * @param  string $type         Chosen type of filesystem.
		 * @param  bool   $error        Whether the current request has failed to connect.
		 *                             Default false.
		 * @param  string $context      Full path to the directory that is tested for
		 *                             being writable.
		 * @param  bool   $extra_fields Whether to allow Group/World writable.
		 * @param  array  $allow_relaxed_file_ownership Extra POST fields.
		 * @return mixed
		 */
		public function maybe_set_cred( $credentials, $form_post, $type, $error, $context, $extra_fields, $allow_relaxed_file_ownership ) {

			$method = $this->check_filesystem_method();

			if ( true === $method ) {
				return $credentials;
			}

			$credentials = get_option( 'ftp_credentials', array( 'hostname' => '', 'username' => '' ) );

			// If defined, set it to that, Else, If POST'd, set it to that, If not, Set it to whatever it previously was(saved details in option)
			$credentials['hostname'] = defined( 'FTP_HOST' ) ? FTP_HOST : ( ! empty( $_POST['hostname'] ) ? wp_unslash( $_POST['hostname'] ) : $credentials['hostname'] );
			$credentials['username'] = defined( 'FTP_USER' ) ? FTP_USER : ( ! empty( $_POST['username'] ) ? wp_unslash( $_POST['username'] ) : $credentials['username'] );
			$credentials['password'] = defined( 'FTP_PASS' ) ? FTP_PASS : ( ! empty( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : '' );

			// Check to see if we are setting the public/private keys for ssh
			$credentials['public_key'] = defined( 'FTP_PUBKEY' ) ? FTP_PUBKEY : ( ! empty( $_POST['public_key'] ) ? wp_unslash( $_POST['public_key'] ) : '' );
			$credentials['private_key'] = defined( 'FTP_PRIKEY' ) ? FTP_PRIKEY : ( ! empty( $_POST['private_key'] ) ? wp_unslash( $_POST['private_key'] ) : '' );

			// Sanitize the hostname, Some people might pass in odd-data:
			$credentials['hostname'] = preg_replace( '|\w+://|', '', $credentials['hostname'] );

			if ( strpos( $credentials['hostname'], ':' ) ) {
				list( $credentials['hostname'], $credentials['port'] ) = explode( ':', $credentials['hostname'], 2 );
				if ( ! is_numeric( $credentials['port'] ) ) {
					unset( $credentials['port'] );
				}
			} else {
				unset( $credentials['port'] );
			}

			if ( ( defined( 'FTP_SSH' ) && FTP_SSH ) || ( defined( 'FS_METHOD' ) && 'ssh2' == FS_METHOD ) ) {
				$credentials['connection_type'] = 'ssh';
			} elseif ( ( defined( 'FTP_SSL' ) && FTP_SSL ) && 'ftpext' == $type ) {
				// Only the FTP Extension understands SSL
				$credentials['connection_type'] = 'ftps';
			} elseif ( ! empty( $_POST['connection_type'] ) ) {
				$credentials['connection_type'] = wp_unslash( $_POST['connection_type'] );
			} elseif ( ! isset( $credentials['connection_type'] ) ) {
				// All else fails (And it's not defaulted to something else saved), Default to FTP
				$credentials['connection_type'] = 'ftp';
			}

			return $credentials;

		}

		/**
		 * Check avaliable filesystem method
		 *
		 * @since  1.0.0
		 * @return bool true - if avaliable direct access, else - access method
		 */
		public function check_filesystem_method() {

			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			$method = get_filesystem_method( array(), false, false );

			if ( 'direct' == $method ) {
				return true;
			}

			return $this->check_creds( $method );

		}

		/**
		 * Check, if user provide credentials via constants
		 *
		 * @since  1.0.0
		 * @param  string $method filesystem method.
		 * @return bool|string
		 */
		public function check_creds( $method ) {

			if ( in_array( $method, array( 'ftpext', 'ftpsockets' ) ) && defined( 'FTP_HOST' ) && defined( 'FTP_USER' ) && defined( 'FTP_PASS' ) ) {
				return true;
			}

			if ( 'ssh2' == $method && defined( 'FTP_PUBKEY' ) && defined( 'FTP_PRIKEY' ) ) {
				return true;
			}

			return $method;

		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}

}
