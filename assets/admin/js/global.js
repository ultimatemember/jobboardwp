jQuery.ajaxSetup({
	beforeSend: function( jqXHR, settings ) {
		if ( settings.processData ) {
			if ( settings.data !== '' ) {
				settings.data += '&jb_current_locale=' + jb_admin_data.locale;
			} else {
				settings.data = 'jb_current_locale=' + jb_admin_data.locale;
			}
		} else {
			settings.data = jQuery.extend(
				settings.data,
				{
					jb_current_locale: jb_admin_data.locale
				}
			);
		}

		return true;
	}
});

jQuery( document ).ready( function() {
	jQuery(document.body).on( 'click', '.jb-admin-notice.is-dismissible .notice-dismiss', function() {
		var notice_key = jQuery(this).parents('.jb-admin-notice').data('key');

		wp.ajax.send( 'jb_dismiss_notice', {
			data: {
				key: notice_key,
				nonce: jb_admin_data.nonce
			},
			success: function( data ) {
				return true;
			},
			error: function( data ) {
				return false;
			}
		});
	});

	jQuery(document.body).on( 'click', '.jb-admin-notice.is-dismissible .jb_secondary_dismiss', function(e) {
		e.preventDefault();
		jQuery(this).parents('.jb-admin-notice').find('.notice-dismiss').trigger( 'click' );
	});
});
