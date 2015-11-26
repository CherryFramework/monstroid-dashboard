(function( $ ) {
	'use strict';

	$( function() {

		function mdAjaxUpdate( $this, ignoreBackup ) {
			var $notice       = $( '#need_update' ),
				$forceNotice = $( '.md-message.md-update' ),
				data          = {
					action: 'monstroid_dashboard_do_theme_update',
					nonce: window.monstroidDashboard.nonce
				};

			if ( $this.hasClass( 'in-progress' ) || $this.hasClass( 'disabled' ) ) {
				return ! 1;
			}

			$this.addClass( 'in-progress' );
			mdSpinner( $this, 'success' );
			$( '.md-misc-messages' ).addClass( 'md-hidden' );

			mdClearUpdateMessage();

			if ( true === ignoreBackup ) {
				data.ignoreBackup = true;
				mdSpinner( $( '.md-update-messages' ), 'dark' );
			}

			$.ajax({
				url: window.ajaxurl,
				type: 'post',
				dataType: 'json',
				data: data,
				error: function() {
					$this.removeClass( 'in-progress' );
					mdRemoveSpinner( $this );
					mdUpdateMessage( window.monstroidDashboard.internalError );
				}
			}).done( function( response ) {

				$this.removeClass( 'in-progress' );
				mdRemoveSpinner( $this );

				if ( true === response.success ) {
					$this.addClass( 'md-hidden' );

					if ( $notice.length ) {
						$notice.addClass( 'md-hidden' );
					}

					if ( $forceNotice.length ) {
						$forceNotice.addClass( 'md-hidden' );
					}

					mdUpdateMessage( response.data.message, 'success' );
					mdUpdateLog( response.data.updateLog );

					$( '.md-new-version' ).text( response.data.newVersion );
					$( '.md-badge' ).remove();

					return 1;
				}

				if ( false === response.success && 'backup_failed' === response.data.type ) {
					mdUpdateMessage( response.data.message );
					mdUpdateLog( response.data.updateLog );
					$this.addClass( 'md-hidden' );
					return ! 1;
				}

				mdUpdateMessage( response.data.message );
				mdUpdateLog( response.data.updateLog );
			});
		}

		function mdUpdateMessage( message, type ) {
			type = 'undefined' !== typeof type ? type : 'default';
			$( '.md-update-messages' ).addClass( 'md-' + type ).html( message );
		}

		function mdUpdateLog( log ) {
			$( '.md-update-log' ).html( log );
		}

		function mdClearUpdateMessage() {
			$( '.md-update-messages' ).attr( 'class', 'md-update-messages' ).empty();
			$( '.md-update-log' ).empty().addClass( 'md-hidden' );
		}

		function mdSpinner( parent, type ) {
			parent.prepend( '<span class="md-spinner md-' + type + '"><span class="md-spinner-circle"></span></span>' );
		}

		function mdRemoveSpinner( button ) {
			$( '.md-spinner', button ).remove();
		}

		$( document ).on( 'click', '.run-theme-update', function( event ) {
			event.preventDefault();
			if ( ! window.confirm( window.monstroidDashboard.confirmUpdate ) ) {
				return ! 1;
			}
			mdAjaxUpdate( $( this ), false );
		});

		$( document ).on( 'click', '.force-run-theme-update', function( event ) {
			event.preventDefault();
			mdAjaxUpdate( $( this ), true );
		});

		$( document ).on( 'click', '.show-update-log', function( event ) {
			event.preventDefault();
			$( '.md-update-log' ).removeClass( 'md-hidden' );
		});

		// Process latest monstroid version download
		$( document ).on( 'click', '.download-latest', function( event ) {

			var $this = $( this );

			event.preventDefault();

			$this.addClass( 'in-progress' );
			mdSpinner( $this, 'default' );
			$( '.md-download-message' ).remove();

			$.ajax({
				url: window.ajaxurl,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'monstroid_dashboard_download_latest',
					nonce: window.monstroidDashboard.nonce
				},
				error: function() {
					$this.removeClass( 'in-progress' );
					mdRemoveSpinner( $this );
					$this.after( '<div class="md-download-message md-error">' + window.monstroidDashboard.internalError + '</div>' );
				}
			}).done( function( response ) {
				$this.removeClass( 'in-progress' );
				mdRemoveSpinner( $this );
				if ( true === response.success ) {
					window.location = response.data.url;
					return 1;
				} else {
					$this.after( '<div class="md-download-message md-error">' + response.data.message + '</div>' );
				}
			});
		});

		// Process license key activation
		$( document ).on( 'click', '.save-license-key', function( event ) {

			var $this  = $( this ),
				$input = $( 'input[name="monstroid-key"]' ),
				key;

			event.preventDefault();

			if ( $this.hasClass( 'in-progress' ) ) {
				return ! 1;
			}

			$this.addClass( 'in-progress' );
			mdSpinner( $this, 'default' );

			key = $input.val();

			$( '.md-form-message' ).remove();

			if ( ! key ) {
				$input.addClass( 'error' ).parent().append( '<div class="md-form-message md-error">' + window.monstroidDashboard.emptyKey + '</div>' );
				$this.removeClass( 'in-progress' );
				mdRemoveSpinner( $this );
				return ! 1;
			}

			$.ajax({
				url: window.ajaxurl,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'monstroid_dashboard_save_key',
					key: key,
					nonce: window.monstroidDashboard.nonce
				},
				error: function() {
					$this.removeClass( 'in-progress' );
					mdRemoveSpinner( $this );
					$input.parent().append( '<div class="md-form-message md-error">' + window.monstroidDashboard.internalError + '</div>' );
				}
			}).done( function( response ) {
				$this.removeClass( 'in-progress' );
				mdRemoveSpinner( $this );
				if ( true === response.success ) {
					$input.parent().append( '<div class="md-form-message md-success">' + response.data.message + '</div>' );
				} else {
					$input.parent().append( '<div class="md-form-message md-error">' + response.data.message + '</div>' );
				}
			});

		});

		// Confirm backup delete
		$( document ).on( 'click', '.md-updates-list_delete_link', function( event ) {
			if ( ! window.confirm( window.monstroidDashboard.confirmDelete ) ) {
				event.preventDefault();
			}
		});

	});

}( jQuery ) );
