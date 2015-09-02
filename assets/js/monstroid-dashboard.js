(function ($) {
	"use strict";

	$(function(){

		function md_ajax_update( $this, ignore_backup ) {
			var $notice       = $('#need_update'),
				$force_notice = $('.md-message.md-update'),
				data          = {
					action: 'monstroid_dashboard_do_theme_update',
					nonce: monstroid_dashboard.nonce
				};

			if ( $this.hasClass('in-progress') || $this.hasClass('disabled') ) {
				return !1;
			}

			$this.addClass('in-progress');
			md_spinner( $this, 'success' );
			$('.md-misc-messages').addClass('md-hidden');

			md_clear_update_message();
			if ( ignore_backup == true ) {
				data.ignore_backup = true;
				md_spinner( $('.md-update-messages'), 'dark' );
			}

			$.ajax({
				url: ajaxurl,
				type: "post",
				dataType: "json",
				data: data,
				error: function(response) {
					$this.removeClass('in-progress');
					md_remove_spinner($this);
					md_update_message(monstroid_dashboard.internal_error);
				}
			}).done(function(response) {
				$this.removeClass('in-progress');
				md_remove_spinner($this);
				if ( response.success == true ) {
					$this.addClass('md-hidden');
					if ( $notice.length > 0 ) {
						$notice.addClass('md-hidden');
					}
					if ( $force_notice.length > 0 ) {
						$force_notice.addClass('md-hidden');
					}
					md_update_message(response.data.message, 'success');
					md_update_log(response.data.update_log);
					$('.md-new-version').text(response.data.new_version);
					$('.md-badge').remove();
					return 1;
				}
				if ( response.success == false && response.data.type == 'backup_failed' ) {
					md_update_message(response.data.message);
					md_update_log(response.data.update_log);
					$this.addClass('md-hidden');
					return !1;
				}
				md_update_message(response.data.message);
				md_update_log(response.data.update_log);
			});
		}

		function md_update_message( message, type ) {
			type = typeof type !== 'undefined' ? type : 'default';
			$('.md-update-messages').addClass('md-'+type).html(message);
		}

		function md_update_log( log ) {
			$('.md-update-log').html(log);
		}

		function md_clear_update_message() {
			$('.md-update-messages').attr('class', 'md-update-messages').empty();
			$('.md-update-log').empty().addClass('md-hidden');
		}

		function md_spinner( parent, type ) {
			parent.prepend('<span class="md-spinner md-' + type + '"><span class="md-spinner-circle"></span></span>');
		}

		function md_remove_spinner(button) {
			$('.md-spinner', button).remove();
		}

		$(document).on('click', '.run-theme-update', function(event) {
			event.preventDefault();
			md_ajax_update( $(this), false );
		});

		$(document).on('click', '.force-run-theme-update', function(event) {
			event.preventDefault();
			md_ajax_update( $(this), true );
		});

		$(document).on('click', '.show-update-log', function(event) {
			event.preventDefault();
			$('.md-update-log').removeClass('md-hidden');
		});

		// Process latest monstroid version download
		$(document).on('click', '.download-latest', function(event) {
			event.preventDefault();

			var $this = $(this);

			$this.addClass('in-progress');
			md_spinner( $this, 'default' );
			$('.md-download-message').remove();

			$.ajax({
				url: ajaxurl,
				type: "post",
				dataType: "json",
				data: {
					action: 'monstroid_dashboard_download_latest',
					nonce: monstroid_dashboard.nonce
				},
				error: function(response) {
					$this.removeClass('in-progress');
					md_remove_spinner($this);
					$this.after('<div class="md-download-message md-error">' + monstroid_dashboard.internal_error + '</div>');
				}
			}).done(function(response) {
				$this.removeClass('in-progress');
				md_remove_spinner($this);
				if ( response.success == true ) {
					window.location = response.data.url;
					return 1;
				} else {
					$this.after('<div class="md-download-message md-error">' + response.data.message + '</div>');
				}
			});
		});

		// process license key activation
		$(document).on('click', '.save-license-key', function(event) {
			event.preventDefault();

			var $this  = $(this),
				$input = $('input[name="monstroid-key"]'),
				key;

			if ( $this.hasClass('in-progress') ) {
				return !1;
			}

			$this.addClass('in-progress');
			md_spinner( $this, 'default' );

			key = $input.val();

			$('.md-form-message').remove();

			if ( key == '' ) {
				$input.addClass('error').parent().append('<div class="md-form-message md-error">' + monstroid_dashboard.empty_key + '</div>');
				$this.removeClass('in-progress');
				md_remove_spinner($this);
				return !1;
			}

			$.ajax({
				url: ajaxurl,
				type: "post",
				dataType: "json",
				data: {
					action: 'monstroid_dashboard_save_key',
					key: key,
					nonce: monstroid_dashboard.nonce
				},
				error: function(response) {
					$this.removeClass('in-progress');
					md_remove_spinner($this);
					$input.parent().append('<div class="md-form-message md-error">' + monstroid_dashboard.internal_error + '</div>');
				}
			}).done(function(response) {
				$this.removeClass('in-progress');
				md_remove_spinner($this);
				if ( response.success == true ) {
					$input.parent().append('<div class="md-form-message md-success">' + response.data.message + '</div>');
				} else {
					$input.parent().append('<div class="md-form-message md-error">' + response.data.message + '</div>');
				}
			});

		});

		// confirm backup delete
		$(document).on('click', '.md-updates-list_delete_link', function(event) {
			if ( ! confirm( monstroid_dashboard.confirm_alert ) ) {
				event.preventDefault();
			}
		});


	})

}(jQuery));