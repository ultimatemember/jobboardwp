(function($, undefined) {

	// Extract vars.
	var editor = wp.data.dispatch( 'core/editor' );
	var editorSelect = wp.data.select( 'core/editor' );
	var notices = wp.data.dispatch( 'core/notices' );

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

			wp.ajax.send( 'jb-validate-job-data', {
				data:  "serialize form jQuery('#editor')",
				success: function( answer ) {
					if ( answer.valid ) {
						notices.removeNotice( 'jbwp-validation' );
						// Resolve promise and allow savePost().
						resolve( 'Validation bypassed.' );
						editor.unlockPostSaving( 'jbwp' );
						notices.removeNotice( 'jbwp-validation' );
					} else {
						editor.lockPostSaving( 'jbwp' );
						notices.createErrorNotice( answer.notice, {
							id: 'jbwp-validation',
							isDismissible: true
						});
					}
				},
				error: function( data ) {
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

})(jQuery);
