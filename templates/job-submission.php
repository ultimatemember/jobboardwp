<?php if ( ! defined( 'ABSPATH' ) ) exit;

$edit = false;
if ( ! empty( $jb_job_submission['job'] ) ) {
	$edit = true;
}

if ( ! is_user_logged_in() && $jb_job_submission['account_required'] && ! $jb_job_submission['registration_enabled'] ) { ?>
	<label>
		<?php _e( 'You must sign in to create a new job.', 'jobboardwp' ) ?>
		<a class="button" href="<?php echo esc_url( wp_login_url( get_permalink() ) ) ?>"><?php esc_attr_e( 'Sing in', 'jobboardwp' ) ?></a>
	</label>
<?php } else {
	$types = get_terms( [
		'taxonomy'      => 'jb-job-type',
		'hide_empty'    => false,
	] );

	$categories = [];
	if ( $jb_job_submission['job_categories'] ) {
		$categories = get_terms( [
			'taxonomy'      => 'jb-job-category',
			'hide_empty'    => false,
		] );
	}

	if ( $edit ) {
		$data = JB()->common()->job()->get_raw_data( $jb_job_submission['job']->ID );

		$job_title = ! empty( $_POST['job_title'] ) ? $_POST['job_title'] : $data['title'];
		$job_location_type = isset( $_POST['job_location_type'] ) ? $_POST['job_location_type'] : $data['location_type'];
		$job_location = ! empty( $_POST['job_location'] ) ? $_POST['job_location'] : $data['location'];
		$job_type = ! empty( $_POST['job_type'] ) ? $_POST['job_type'] : $data['type'];
		$job_category = ! empty( $_POST['job_category'] ) ? [ $_POST['job_category'] ] : $data['category'];
		$job_description = ! empty( $_POST['job_description'] ) ? $_POST['job_description'] : $data['description'];
		$job_application = ! empty( $_POST['job_application'] ) ? $_POST['job_application'] : $data['app_contact'];

		$company_name = ! empty( $_POST['company_name'] ) ? $_POST['company_name'] : $data['company_name'];
		$company_website = ! empty( $_POST['company_website'] ) ? $_POST['company_website'] : $data['company_website'];
		$company_tagline = ! empty( $_POST['company_tagline'] ) ? $_POST['company_tagline'] : $data['company_tagline'];
		$company_twitter = ! empty( $_POST['company_twitter'] ) ? $_POST['company_twitter'] : $data['company_twitter'];
		$company_facebook = ! empty( $_POST['company_facebook'] ) ? $_POST['company_facebook'] : $data['company_facebook'];
		$company_instagram = ! empty( $_POST['company_instagram'] ) ? $_POST['company_instagram'] : $data['company_instagram'];
		$company_logo = ! empty( $_POST['company_logo'] ) ? $_POST['company_logo'] : $data['company_logo'];
	} else {
		$company_data = JB()->common()->user()->get_company_data();

		$author_email = ! empty( $_POST['author_email'] ) ? $_POST['author_email'] : '';
		$author_username = ! empty( $_POST['author_username'] ) ? $_POST['author_username'] : '';
		$author_fname = ! empty( $_POST['author_first_name'] ) ? $_POST['author_first_name'] : '';
		$author_lname = ! empty( $_POST['author_last_name'] ) ? $_POST['author_last_name'] : '';

		$job_title = ! empty( $_POST['job_title'] ) ? stripslashes( $_POST['job_title'] ) : '';
		$job_location_type = isset( $_POST['job_location_type'] ) ? $_POST['job_location_type'] : '0';
		$job_location = ! empty( $_POST['job_location'] ) ? stripslashes( $_POST['job_location'] ) : '';
		$job_type = ! empty( $_POST['job_type'] ) ? $_POST['job_type'] : [];
		$job_category = ! empty( $_POST['job_category'] ) ? [ $_POST['job_category'] ] : [];
		$job_description = ! empty( $_POST['job_description'] ) ? stripslashes( $_POST['job_description'] ) : '';
		$job_application = ! empty( $_POST['job_application'] ) ? stripslashes( $_POST['job_application'] ) : '';

		$company_name = ! empty( $_POST['company_name'] ) ? stripslashes( $_POST['company_name'] ) : $company_data['name'];
		$company_website = ! empty( $_POST['company_website'] ) ? stripslashes( $_POST['company_website'] ) : $company_data['website'];
		$company_tagline = ! empty( $_POST['company_tagline'] ) ? stripslashes( $_POST['company_tagline'] ) : $company_data['tagline'];
		$company_twitter = ! empty( $_POST['company_twitter'] ) ? stripslashes( $_POST['company_twitter'] ) : $company_data['twitter'];
		$company_facebook = ! empty( $_POST['company_facebook'] ) ? stripslashes( $_POST['company_facebook'] ) : $company_data['facebook'];
		$company_instagram = ! empty( $_POST['company_instagram'] ) ? stripslashes( $_POST['company_instagram'] ) : $company_data['instagram'];
		$company_logo = ! empty( $_POST['company_logo'] ) ? $_POST['company_logo'] : $company_data['logo'];
	}

	?>

	<div id="jb-job-submission-form-wrapper">
		<?php if ( JB()->frontend()->forms()->has_notices() ) { ?>
			<?php foreach ( JB()->frontend()->forms()->get_notices() as $notice ) { ?>
				<span class="jb-job-submission-form-notice"><?php echo $notice ?></span>
			<?php } ?>
		<?php }

		if ( JB()->frontend()->forms()->has_error( 'global' ) ) { ?>
			<?php foreach ( JB()->frontend()->forms()->get_errors( 'global' ) as $error ) { ?>
				<span class="jb-job-submission-form-error"><?php echo $error ?></span>
			<?php } ?>
		<?php } ?>

		<form action="" method="post" name="jb-job-submission" id="jb-job-submission">
			<?php if ( $jb_job_submission['registration_enabled'] && ! is_user_logged_in() ) { ?>
				<h3><?php _e( 'Your Details', 'jobboardwp' ) ?></h3>
				<p>
				<?php if ( $jb_job_submission['account_required'] ) {
					if ( $jb_job_submission['use_username'] ) {
						printf( __( 'If you don\'t have an account you can create one below by entering your email address/username or <a href="%s">sign in</a>.', 'jobboardwp' ), wp_login_url( get_permalink() ) );
					} else {
						printf( __( 'If you don\'t have an account you can create one below by entering your email address or <a href="%s">sign in</a>.', 'jobboardwp' ), wp_login_url( get_permalink() ) );
					}
				} else {
					if ( $jb_job_submission['use_username'] ) {
						printf( __( 'If you don\'t have an account you can optionally create one below by entering your email address/username or <a href="%s">sign in</a>', 'jobboardwp' ), wp_login_url( get_permalink() ) );
					} else {
						printf( __( 'If you don\'t have an account you can optionally create one below by entering your email address or <a href="%s">sign in</a>.', 'jobboardwp' ), wp_login_url( get_permalink() ) );
					}
				} ?>

				</p>

				<?php $author_error = JB()->frontend()->forms()->has_error( 'author_email' ) ? 'jb-form-error-row' : ''; ?>
				<p class="<?php echo $author_error ?>">
					<label for="jb_author_email"><?php _e( 'Your Email', 'jobboardwp' ) ?></label>
					<span class="jb-form-field-content">
						<input type="text" id="jb_author_email" name="author_email" value="<?php echo esc_attr( $author_email ) ?>" required />

						<?php if ( ! empty( $author_error ) ) { ?>
							<span class="jb-form-field-error">
								<?php echo JB()->frontend()->forms()->get_errors( 'author_email' ) ?>
							</span>
						<?php } ?>
					</span>
				</p>

				<?php if ( $jb_job_submission['use_username'] ) {

					$username_error = JB()->frontend()->forms()->has_error( 'author_username' ) ? 'jb-form-error-row' : ''; ?>
					<p class="<?php echo $username_error ?>">
						<label for="jb_author_username"><?php _e( 'Username', 'jobboardwp' ) ?></label>
						<span class="jb-form-field-content">
						<input type="text" id="jb_author_username" name="author_username" value="<?php echo esc_attr( $author_username ) ?>" required />

						<?php if ( ! empty( $username_error ) ) { ?>
							<span class="jb-form-field-error">
								<?php echo JB()->frontend()->forms()->get_errors( 'author_username' ) ?>
							</span>
						<?php } ?>
					</span>
					</p>
				<?php } ?>

				<?php $fname_error = JB()->frontend()->forms()->has_error( 'author_fname' ) ? 'jb-form-error-row' : ''; ?>
				<p class="<?php echo $fname_error ?>">
					<label for="jb_author_fname"><?php _e( 'First Name', 'jobboardwp' ) ?></label>
					<span class="jb-form-field-content">
						<input type="text" id="jb_author_fname" name="author_first_name" value="<?php echo esc_attr( $author_fname ) ?>" />

						<?php if ( ! empty( $fname_error ) ) { ?>
							<span class="jb-form-field-error">
								<?php echo JB()->frontend()->forms()->get_errors( 'author_fname' ) ?>
							</span>
						<?php } ?>
					</span>
				</p>

				<?php $lname_error = JB()->frontend()->forms()->has_error( 'author_lname' ) ? 'jb-form-error-row' : ''; ?>
				<p class="<?php echo $lname_error ?>">
					<label for="jb_author_lname"><?php _e( 'Last Name', 'jobboardwp' ) ?></label>
					<span class="jb-form-field-content">
						<input type="text" id="jb_author_lname" name="author_last_name" value="<?php echo esc_attr( $author_lname ) ?>" />

						<?php if ( ! empty( $lname_error ) ) { ?>
							<span class="jb-form-field-error">
								<?php echo JB()->frontend()->forms()->get_errors( 'author_lname' ) ?>
							</span>
						<?php } ?>
					</span>
				</p>

				<?php if ( ! $jb_job_submission['use_standard_password_email'] ) {

					$password_error = JB()->frontend()->forms()->has_error( 'author_password' ) ? 'jb-form-error-row' : ''; ?>
					<p class="<?php echo $password_error ?>">
						<label for="jb_author_password"><?php _e( 'Password', 'jobboardwp' ) ?></label>
						<span class="jb-form-field-content">
							<input type="password" id="jb_author_password" name="author_password" value="" required />

							<?php if ( ! empty( $password_error ) ) { ?>
								<span class="jb-form-field-error">
									<?php echo JB()->frontend()->forms()->get_errors( 'author_password' ) ?>
								</span>
							<?php } ?>
						</span>
					</p>

					<?php $password_confirm_error = JB()->frontend()->forms()->has_error( 'author_password_confirm' ) ? 'jb-form-error-row' : ''; ?>

					<p class="<?php echo $password_confirm_error ?>">
						<label for="jb_author_password_confirm"><?php _e( 'Confirm Password', 'jobboardwp' ) ?></label>
						<span class="jb-form-field-content">
							<input type="password" id="jb_author_password_confirm" name="author_password_confirm" value="" required />

							<?php if ( ! empty( $password_confirm_error ) ) { ?>
								<span class="jb-form-field-error">
									<?php echo JB()->frontend()->forms()->get_errors( 'author_password_confirm' ) ?>
								</span>
							<?php } ?>
						</span>
					</p>
				<?php }

				if ( $jb_job_submission['use_standard_password_email'] ) { ?>
					<p><?php esc_html_e( 'Your account details will be confirmed via email.', 'jobboardwp' ); ?></p>
				<?php }

			} elseif ( is_user_logged_in() ) {
				$current_userdata = get_userdata( get_current_user_id() ); ?>
				<h3><?php _e( 'Your Details', 'jobboardwp' ) ?></h3>
				<p><?php printf( __( 'You are currently signed in as <strong>%s</strong>', 'jobboardwp' ), $current_userdata->display_name ); ?></p>
			<?php } ?>

			<h3><?php _e( 'Job Details', 'jobboardwp' ) ?></h3>

			<?php $title_error = JB()->frontend()->forms()->has_error( 'job_title' ) ? 'jb-form-error-row' : ''; ?>
			<p class="<?php echo $title_error ?>">
				<label for="jb_job_title"><?php _e( 'Job Title', 'jobboardwp' ) ?></label>
				<span class="jb-form-field-content">
					<input type="text" id="jb_job_title" name="job_title" value="<?php echo esc_attr( $job_title ) ?>" required />

					<?php if ( ! empty( $title_error ) ) { ?>
						<span class="jb-form-field-error">
							<?php echo JB()->frontend()->forms()->get_errors( 'job_title' ) ?>
						</span>
					<?php } ?>
				</span>
			</p>

			<?php $location_error = JB()->frontend()->forms()->has_error( 'job_location' ) ? 'jb-form-error-row' : ''; ?>
			<p class="<?php echo $location_error ?>">
				<label for="job_location_type"><?php _e( 'Job Location', 'jobboardwp' ) ?></label>
				<span class="jb-form-field-content">
					<label>
						<input type="radio" data-location_field="onsite" name="job_location_type" value="0" <?php checked( $job_location_type === '0' ) ?> />
						<?php _e( 'Onsite', 'jobboardwp' ) ?>
					</label>
					<span class="jb-onsite-location jb-locations-fields">
						<label for="jb_job_location"><?php _e( 'Location', 'jobboardwp' ) ?></label>
						<input type="text" id="jb_job_location" name="job_location"
							   value="<?php echo esc_attr( $job_location ) ?>" placeholder="<?php esc_attr_e( 'City, State, or Country', 'jobboardwp' ) ?>" />
					</span>
					<label>
						<input type="radio" data-location_field="remote" name="job_location_type" value="1" <?php checked( $job_location_type === '1' ) ?> />
						<?php _e( 'Remote', 'jobboardwp' ) ?>
					</label>
					<span class="jb-remote-location jb-locations-fields">
						<label for="jb_job_location_preferred"><?php _e( 'Preferred Location', 'jobboardwp' ) ?></label>
						<input type="text" id="jb_job_location_preferred" name="job_location"
							   value="<?php echo esc_attr( $job_location ) ?>" placeholder="<?php esc_attr_e( 'City, State, or Country', 'jobboardwp' ) ?>" />
					</span>
					<label>
						<input type="radio" data-location_field="onsite-remote" name="job_location_type" value="" <?php checked( $job_location_type === '' ) ?> />
						<?php _e( 'Onsite or Remote', 'jobboardwp' ) ?>
					</label>
					<span class="jb-onsite-remote-location jb-locations-fields">
						<label for="jb_location_onsite-remote_preferred"><?php _e( 'Preferred Location', 'jobboardwp' ) ?></label>
						<input type="text" id="jb_location_onsite-remote_preferred" name="job_location"
							   value="<?php echo esc_attr( $job_location ) ?>" placeholder="<?php esc_attr_e( 'City, State, or Country', 'jobboardwp' ) ?>" />
					</span>

					<?php if ( ! empty( $location_error ) ) { ?>
						<span class="jb-form-field-error">
							<?php echo JB()->frontend()->forms()->get_errors( 'job_location' ) ?>
						</span>
					<?php } ?>
				</span>
			</p>

			<?php $type_error = JB()->frontend()->forms()->has_error( 'job_type' ) ? 'jb-form-error-row' : ''; ?>
			<p class="<?php echo $type_error ?>">
				<label for="jb_job_type"><?php _e( 'Job Type', 'jobboardwp' ) ?></label>
				<span class="jb-form-field-content">
					<select class="jb-s1" id="jb_job_type" name="job_type[]" multiple required>
						<?php foreach ( $types as $type ) { ?>
							<option value="<?php echo esc_attr( $type->term_id ) ?>" <?php selected( in_array( $type->term_id, $job_type ) ) ?>>
								<?php echo $type->name ?>
							</option>
						<?php } ?>
					</select>

					<?php if ( ! empty( $type_error ) ) { ?>
						<span class="jb-form-field-error">
							<?php echo JB()->frontend()->forms()->get_errors( 'job_type' ) ?>
						</span>
					<?php } ?>
				</span>
			</p>

			<?php if ( count( $categories ) ) { ?>
				<?php $category_error = JB()->frontend()->forms()->has_error( 'job_category' ) ? 'jb-form-error-row' : ''; ?>
				<p class="<?php echo $category_error ?>">
					<label for="jb_job_category"><?php _e( 'Job Category', 'jobboardwp' ) ?></label>
					<span class="jb-form-field-content">
						<select class="jb-s2" id="jb_job_category" name="job_category" placeholder="<?php esc_attr_e( 'Select a Job Category', 'jobboardwp' ) ?>">
							<option value=" "><?php _e( '(None)' ) ?></option>
							<?php foreach ( $categories as $category ) { ?>
								<option value="<?php echo esc_attr( $category->term_id ) ?>" <?php selected( in_array( $category->term_id, $job_category ) ) ?>>
									<?php echo $category->name ?>
								</option>
							<?php } ?>
						</select>

						<?php if ( ! empty( $category_error ) ) { ?>
							<span class="jb-form-field-error">
								<?php echo JB()->frontend()->forms()->get_errors( 'job_category' ) ?>
							</span>
						<?php } ?>
					</span>
				</p>
			<?php } ?>

			<?php $description_error = JB()->frontend()->forms()->has_error( 'job_description' ) ? 'jb-form-error-row' : ''; ?>
			<p class="<?php echo $description_error ?>">
				<label for="jb_job_description"><?php _e( 'Description', 'jobboardwp' ) ?></label>
				<span class="jb-form-field-content">
					<?php JB()->frontend()->forms()->render_editor( $job_description ); ?>

					<?php if ( ! empty( $description_error ) ) { ?>
						<span class="jb-form-field-error">
							<?php echo JB()->frontend()->forms()->get_errors( 'job_description' ) ?>
						</span>
					<?php } ?>
				</span>
			</p>

			<?php $application_error = JB()->frontend()->forms()->has_error( 'job_application' ) ? 'jb-form-error-row' : ''; ?>
			<p class="<?php echo $application_error ?>">
				<label for="jb_job_application"><?php _e( 'Application Contact', 'jobboardwp' ) ?></label>
				<span class="jb-form-field-content">
					<input type="text" id="jb_job_application" name="job_application" value="<?php echo esc_attr( $job_application ) ?>" required />

					<?php if ( ! empty( $application_error ) ) { ?>
						<span class="jb-form-field-error">
							<?php echo JB()->frontend()->forms()->get_errors( 'job_application' ) ?>
						</span>
					<?php } ?>
				</span>
			</p>

			<h3><?php _e( 'Company Details', 'jobboardwp' ) ?></h3>

			<?php $company_name_error = JB()->frontend()->forms()->has_error( 'company_name' ) ? 'jb-form-error-row' : ''; ?>
			<p class="<?php echo $company_name_error ?>">
				<label for="jb_company_name"><?php _e( 'Name', 'jobboardwp' ) ?></label>
				<span class="jb-form-field-content">
					<input type="text" id="jb_company_name" name="company_name" value="<?php echo esc_attr( $company_name ) ?>" required />

					<?php if ( ! empty( $company_name_error ) ) { ?>
						<span class="jb-form-field-error">
							<?php echo JB()->frontend()->forms()->get_errors( 'company_name' ) ?>
						</span>
					<?php } ?>
				</span>
			</p>

			<?php $company_website_error = JB()->frontend()->forms()->has_error( 'company_website' ) ? 'jb-form-error-row' : ''; ?>
			<p class="<?php echo $company_website_error ?>">
				<label for="jb_company_website"><?php _e( 'Website', 'jobboardwp' ) ?></label>
				<span class="jb-form-field-content">
					<input type="text" id="jb_company_website" name="company_website" value="<?php echo esc_attr( $company_website ) ?>" />

					<?php if ( ! empty( $company_website_error ) ) { ?>
						<span class="jb-form-field-error">
							<?php echo JB()->frontend()->forms()->get_errors( 'company_website' ) ?>
						</span>
					<?php } ?>
				</span>
			</p>

			<?php $company_tagline_error = JB()->frontend()->forms()->has_error( 'company_tagline' ) ? 'jb-form-error-row' : ''; ?>
			<p class="<?php echo $company_tagline_error ?>">
				<label for="jb_company_tagline"><?php _e( 'Tagline', 'jobboardwp' ) ?></label>
				<span class="jb-form-field-content">
					<input type="text" id="jb_company_tagline" name="company_tagline" value="<?php echo esc_attr( $company_tagline ) ?>" />

					<?php if ( ! empty( $company_tagline_error ) ) { ?>
						<span class="jb-form-field-error">
							<?php echo JB()->frontend()->forms()->get_errors( 'company_tagline' ) ?>
						</span>
					<?php } ?>
				</span>
			</p>

			<?php $company_twitter_error = JB()->frontend()->forms()->has_error( 'company_twitter' ) ? 'jb-form-error-row' : ''; ?>
			<p class="<?php echo $company_twitter_error ?>">
				<label for="jb_company_twitter"><?php _e( 'Twitter username', 'jobboardwp' ) ?></label>
				<span class="jb-form-field-content">
					<input type="text" id="jb_company_twitter" name="company_twitter" value="<?php echo esc_attr( $company_twitter ) ?>" />

					<?php if ( ! empty( $company_twitter_error ) ) { ?>
						<span class="jb-form-field-error">
							<?php echo JB()->frontend()->forms()->get_errors( 'company_twitter' ) ?>
						</span>
					<?php } ?>
				</span>
			</p>

			<?php $company_facebook_error = JB()->frontend()->forms()->has_error( 'company_facebook' ) ? 'jb-form-error-row' : ''; ?>
			<p class="<?php echo $company_facebook_error ?>">
				<label for="jb_company_facebook"><?php _e( 'Facebook username', 'jobboardwp' ) ?></label>
				<span class="jb-form-field-content">
					<input type="text" id="jb_company_facebook" name="company_facebook" value="<?php echo esc_attr( $company_facebook ) ?>" />

					<?php if ( ! empty( $company_facebook_error ) ) { ?>
						<span class="jb-form-field-error">
							<?php echo JB()->frontend()->forms()->get_errors( 'company_facebook' ) ?>
						</span>
					<?php } ?>
				</span>
			</p>

			<?php $company_instagram_error = JB()->frontend()->forms()->has_error( 'company_instagram' ) ? 'jb-form-error-row' : ''; ?>
			<p class="<?php echo $company_instagram_error ?>">
				<label for="jb_company_instagram"><?php _e( 'Instagram username', 'jobboardwp' ) ?></label>
				<span class="jb-form-field-content">
					<input type="text" id="jb_company_instagram" name="company_instagram" value="<?php echo esc_attr( $company_instagram ) ?>" />

					<?php if ( ! empty( $company_instagram_error ) ) { ?>
						<span class="jb-form-field-error">
							<?php echo JB()->frontend()->forms()->get_errors( 'company_instagram' ) ?>
						</span>
					<?php } ?>
				</span>
			</p>

			<?php $company_logo_error = JB()->frontend()->forms()->has_error( 'company_logo' ) ? 'jb-form-error-row' : ''; ?>
			<p class="<?php echo $company_logo_error ?>">
				<label for="jb_company_logo"><?php _e( 'Logo', 'jobboardwp' ) ?></label>

				<span class="jb-company-logo-wrapper<?php if ( ! empty( $company_logo ) ) { ?> jb-company-logo-uploaded<?php } ?>">
					<?php $thumb_w = get_option( 'thumbnail_size_w' );
					$thumb_h = get_option( 'thumbnail_size_h' );
					$thumb_crop = get_option( 'thumbnail_crop', false ); ?>

					<span class="jb-company-logo-image-wrapper" style="width: <?php echo $thumb_w ?>px;height: <?php echo $thumb_h ?>px;">
						<img src="<?php echo ! empty( $company_logo ) ? esc_url( $company_logo ) : ''; ?>"
                             alt="<?php esc_attr_e( 'Company logo image', 'jobboardwp' ) ?>" <?php if ( $thumb_crop ) { ?>style="object-fit: cover;" <?php } ?>/>
					</span>
					<a class="jb-change-logo" href="javascript:void(0);"><?php _e( 'Change', 'jobboardwp' ) ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;
					<a class="jb-clear-logo" href="javascript:void(0);"><?php _e( 'Remove', 'jobboardwp' ) ?></a>
				</span>

				<span class="jb-uploader<?php if ( ! empty( $company_logo ) ) { ?> jb-company-logo-uploaded<?php } ?>">
					<span id="jb_company_logo_filelist" class="jb-uploader-dropzone">
						<span><?php _e( 'Drop files to upload', 'jobboardwp' ) ?></span>
						<span><?php _e( 'or', 'jobboardwp' ) ?></span>
						<input type="button" id="jb_company_logo_plupload" value="<?php esc_attr_e( 'Select file', 'jobboardwp' ) ?>" />
					</span>
					<span id="jb_company_logo_errorlist">
						<?php if ( ! empty( $company_logo_error ) ) { ?>
							<span class="jb-form-field-error">
								<?php echo JB()->frontend()->forms()->get_errors( 'company_logo' ) ?>
							</span>
						<?php } ?>
					</span>
				</span>
				<input type="hidden" name="company_logo" id="jb_company_logo" value="<?php echo $company_logo ?>" />
				<input type="hidden" name="company_logo_hash" id="jb_company_logo_hash" value="" />
			</p>

			<p class="jb-submit-row">
				<input type="hidden" name="jb-action" value="job-submission" />
				<input type="hidden" name="jb-job-submission-step" value="draft||preview" />
				<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'jb-job-submission' ) ) ?>" />
				<input type="submit" value="<?php esc_attr_e( 'Preview', 'jobboardwp' ) ?>" class="jb-job-preview-submit" name="preview" />
				<input type="submit" value="<?php esc_attr_e( 'Save Draft', 'jobboardwp' ) ?>" class="jb-job-draft-submit" name="draft" />
			</p>
		</form>
	</div>
<?php }