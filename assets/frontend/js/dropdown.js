jQuery( document ).ready( function($) {

	$('.jb-dropdown').each( function() {
		var menu = $(this);
		var element = menu.data('element');
		var trigger = menu.data('trigger');

		$( document.body ).on( trigger, element, function(e) {
			var obj = $(this);
			if ( obj.data( 'jb-dropdown-show' ) === true ) {
				obj.data( 'jb-dropdown-show', false );
				obj.find( '.jb-dropdown' ).hide();
			} else {
				$('.jb-dropdown').hide();
				$('.jb-dropdown').parent().data( 'jb-dropdown-show', false );

				if ( ! obj.find( '.jb-dropdown' ).length ) {
					var dropdown_layout = menu.clone();

					dropdown_layout.css({
						top : '20px',
						width: '150px',
						right: 0
					});

					obj.append( dropdown_layout );

					obj.trigger( 'jb_dropdown_render', { dropdown_layout:dropdown_layout, trigger:trigger, element:element, obj:obj} );

					dropdown_layout.show();
				} else {
					obj.find( '.jb-dropdown' ).css({
						top : '20px',
						width: '150px',
						right: 0
					}).show();
				}

				obj.data( 'jb-dropdown-show', true );

				$( document.body ).bind( 'click', function( event ) {
					if ( typeof jQuery( event.target ).attr('class') === 'undefined' || ( jQuery('.jb-dropdown').find( '.' + jQuery( event.target ).attr('class').trim().replace( ' ', '.' ) ).length === 0 &&
                        '.' + jQuery( event.target ).attr('class').trim() !== element ) ) {

						//event = ev;
						$('.jb-dropdown').hide();
						$('.jb-dropdown').parent().data( 'jb-dropdown-show', false );
						$( document.body ).unbind( event );

					}
				});
			}
		});

		$( document.body ).on( 'click', '.jb-dropdown a', function(e) {
			$(this).parents('.jb-dropdown').hide();
			$(this).parents('.jb-dropdown').parent().data( 'jb-dropdown-show', false );
			$('body').trigger('click');
			e.stopPropagation();
		});
	});
});