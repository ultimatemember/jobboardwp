(function($, undefined) {

	// Extract vars.
	var editor = wp.data.dispatch( 'core/editor' );
	var editorSelect = wp.data.select( 'core/editor' );
	var notices = wp.data.dispatch( 'core/notices' );
	var locked = false;

	// Backup original method.
	var savePost = editor.savePost;

	// Listen for changes to post status and perform actions:
	// a) Enable validation for "publish" action.
	// b) Remember last non "publish" status used for restoring after validation fail.
	var useValidation = false;
	var lastPostStatus = '';
	wp.data.subscribe(function() {
		var postStatus = editorSelect.getEditedPostAttribute( 'status' );
		useValidation = ( postStatus === 'publish' || postStatus === 'future' );
		lastPostStatus = ( postStatus !== 'publish' ) ? postStatus : lastPostStatus;
	});

	// Create validation version.
	editor.savePost = function( options ){
		options = options || {};

		// Backup vars.
		var _this = this;
		var _args = arguments;

		// Perform validation within a Promise.
		return new Promise(function( resolve, reject ) {

			// Bail early if is autosave or preview.
			if ( options.isAutosave || options.isPreview ) {
				return resolve( 'Validation ignored (autosave).' );
			}

			// Bail early if validation is not needed.
			if ( ! useValidation ) {
				return resolve( 'Validation ignored (draft).' );
			}

			var formdata = $('form.metabox-location-normal').serializeArray();
			var data = {};
			$(formdata ).each(function(index, obj){
				var name = obj.name.substring(
					obj.name.lastIndexOf("[") + 1,
					obj.name.lastIndexOf("]")
				);
				data[name] = obj.value;
			});

			var description = wp.data.select( "core/editor" ).getEditedPostContent();

			wp.ajax.send( 'jb-validate-job-data', {
				data: {
					description: description,
					data: data,
					nonce: jb_admin_data.nonce
				},
				success: function( answer ) {
					$('.jb-forms-line .jb-forms-field').css('border', '#8c8f94 solid 1px');
					$('.jb-forms-line .jb-forms-field').parent().find('p.description').css('color', '#2c3338');
					$('.is-root-container').css('background-color', '#ffffff');

					if ( answer.valid ) {
						notices.removeNotice( 'jbwp-validation' );
						// Resolve promise and allow savePost().
						resolve( 'Validation bypassed.' );
						editor.unlockPostSaving( 'jbwp' );
						notices.removeNotice( 'jbwp-validation' );
						locked = false;
					} else {
						if ( answer.empty ) {
							answer.empty.forEach(function (item, i, arr) {
								console.log(i + " - " + item);
								$('#jb-job-meta_' + item).css('border', '#d63638 solid 1px');
								if ( item === 'description' ) {
									$('.is-root-container').css('background-color', '#f4a2a2');
								}
							});
						}
						if ( answer.wrong ) {
							answer.wrong.forEach(function (item, i, arr) {
								$('#jb-job-meta_' + item).css('border', '#d63638 solid 1px');
								$('#jb-job-meta_' + item).parent().find('p.description').css('color', '#d63638');
							});
						}

						editor.lockPostSaving( 'jbwp' );
						notices.createErrorNotice( answer.notice, {
							id: 'jbwp-validation',
							isDismissible: true
						});
						locked = true;
					}
				},
				error: function( data ) {
					console.log(data);
					// Always unlock the form after AJAX error.
					editor.unlockPostSaving( 'jbwp' );
				}
			});

			// Validate the editor form.
			// var valid = acf.validateForm({
			// 	form: $('#editor'),
			// 	reset: true,
			// 	complete: function( $form, validator ){
			// 		// Always unlock the form after AJAX.
			// 		editor.unlockPostSaving( 'jbwp' );
			// 	},
			// 	failure: function( $form, validator ){
			//
			// 		// Get validation error and append to Gutenberg notices.
			// 		var notice = validator.get('notice');
			// 		notices.createErrorNotice( notice.get('text'), {
			// 			id: 'acf-validation',
			// 			isDismissible: true
			// 		});
			// 		notice.remove();
			//
			// 		// Restore last non "publish" status.
			// 		if( lastPostStatus ) {
			// 			editor.editPost({
			// 				status: lastPostStatus
			// 			});
			// 		}
			//
			// 		// Rejext promise and prevent savePost().
			// 		reject( 'Validation failed.' );
			// 	},
			// 	success: function(){
			// 		notices.removeNotice( 'acf-validation' );
			//
			// 		// Resolve promise and allow savePost().
			// 		resolve( 'Validation success.' );
			// 	}
			// });
		}).then(function(){
			return savePost.apply(_this, _args);
		}).catch(function(err){
			// Nothing to do here, user is alerted of validation issues.
		});
	};

	$('.jb-forms-line .jb-forms-field').on('keyup', function () {
		if ( locked ) {
			$(this).css("border", "#8c8f94 solid 1px");
			$(this).parent().find('p.description').css('color', '#2c3338');
			$('.is-root-container').css('background-color', '#ffffff');
			editor.unlockPostSaving('jbwp');
		}
	});

})(jQuery);
