<?php
/**
 * Template for the Jobs List page
 *
 * This template can be overridden by copying it to yourtheme/jobboardwp/js/jobs-list.php
 *
 * Call:      JB()->get_template_part( 'js/jobs-list', $args )
 * Page:      Jobs
 * Parent:    wrapper.php
 * Shortcode: [jb_jobs /]
 * Shortcode: [jb_recent_jobs /]
 *
 * @package jb\templates
 *
 * @var array $jb_js_jobs_list
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Templates are unique. Template must be included once.
if ( defined( 'JB_TEMPLATE_JOBS_LIST_LINE' ) ) {
	return;
}
define( 'JB_TEMPLATE_JOBS_LIST_LINE', true );
?>

<script type="text/template" id="tmpl-jb-jobs-list-line">
	<# if ( data.length > 0 ) { #>
		<# _.each( data, function( job, key, list ) { #>
			<?php
			/** @noinspection PhpUndefinedVariableInspection */
			if ( $jb_js_jobs_list['no-logo'] ) {
				$list_row_class = ' jb-job-list-no-logo';
			} else {
				$list_row_class = '<# if ( ! job.logo ) { #> jb-job-list-no-logo<# } #>';
			}
			?>

			<div class="jb-job-list-row<?php echo $list_row_class; ?><# if ( job.actions.length > 0 ) { #> jb-job-list-with-actions<# } #>"><?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static output, JS template line::14, line::16 ?>
				<?php if ( ! $jb_js_jobs_list['no-logo'] ) { ?>
					<# if ( job.logo ) { #>
						<div class="jb-job-logo">
							{{{job.logo}}}
						</div>
					<# } #>
				<?php } ?>
				<div class="jb-row-data">
					<div class="jb-job-title">
						<span class="jb-job-title-link-line"><a href="<?php /** @noinspection HtmlUnknownTarget */ ?>{{{job.permalink}}}" class="jb-job-title-link">{{{job.title}}}</a></span>
						<?php if ( ! $jb_js_jobs_list['hide-job-types'] ) { ?>
							<# if ( job.types.length > 0 ) { #>
								<div class="jb-job-types jb-responsive jb-ui-m jb-ui-l jb-ui-xl">
									<# _.each( job.types, function( type, t_key, t_list ) { #>
										<div class="jb-job-type" style="color:{{{type.color}}};background:{{{type.bg_color}}};">
											{{{type.name}}}
										</div>
									<# }); #>
								</div>
							<# } #>
						<?php } ?>
					</div>
					<div class="jb-row-info">
						<div class="jb-row-left-side">
							<# if ( job.company.name ) { #>
								<div class="company">
									<i class="far fa-building"></i>
									<span title="{{{job.company.tagline}}}">
										{{{job.company.name}}}
									</span>
								</div>
							<# } #>

							<# if ( job.location ) { #>
								<div class="location">
									<i class="fas fa-map-marker-alt"></i>
									{{{job.location}}}
								</div>
							<# } #>

							<?php if ( JB()->options()->get( 'job-categories' ) ) { ?>
								<# if ( job.category ) { #>
									<div class="category">
										{{{job.category}}}
									</div>
								<# } #>
							<?php } ?>

							<div class="jb-responsive jb-ui-s jb-ui-xs date" title="<?php esc_attr_e( 'Posted', 'jobboardwp' ); ?>">
								<i class="far fa-calendar-alt"></i>
								<?php esc_html_e( 'Posted', 'jobboardwp' ); ?> {{{job.date}}}
							</div>

							<# if ( job.expires ) { #>
								<div class="expires" title="<?php esc_attr_e( 'Expires', 'jobboardwp' ); ?>">
									<i class="fa fa-calendar-times-o"></i>
									<?php esc_html_e( 'Closing on', 'jobboardwp' ); ?> {{{job.expires}}}
								</div>
							<# } #>
						</div>
						<div class="jb-row-right-side">
							<div class="date jb-responsive jb-ui-m jb-ui-l jb-ui-xl date" title="<?php esc_attr_e( 'Posted', 'jobboardwp' ); ?>">
								{{{job.date}}}
							</div>

							<?php if ( ! $jb_js_jobs_list['hide-job-types'] ) { ?>
								<# if ( job.types.length > 0 ) { #>
									<div class="jb-job-types jb-responsive jb-ui-s jb-ui-xs">
										<# _.each( job.types, function( type, t_key, t_list ) { #>
											<div class="jb-job-type" style="color:{{{type.color}}};background:{{{type.bg_color}}};">
												{{{type.name}}}
											</div>
										<# }); #>
									</div>
								<# } #>
							<?php } ?>
						</div>
					</div>
				</div>
				<# if ( job.actions.length > 0 ) { #>
					<div class="jb-row-actions">
						<# _.each( job.actions, function( action, a_key, a_list ) { #>
							<# if ( action.html ) { #>
								{{{action.html}}}
							<# } else { #>
								<a href="<?php /** @noinspection HtmlUnknownTarget */ ?>{{{action.url}}}" class="{{{action.class}}}">
									<i class="{{{action.img}}}"></i>
								</a>
							<# } #>
						<# }); #>
					</div>
				<# } #>
			</div>
		<# }); #>
	<# } #>
</script>
