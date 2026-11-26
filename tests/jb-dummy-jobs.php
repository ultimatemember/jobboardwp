<?php
/**
 * JobBoardWP dummy jobs generator (testing only).
 *
 * Creates published jobs for several companies through the same API that JobBoardWP uses
 * on the frontend job submission (wp_insert_post + meta_input + JB() helpers), so the
 * [jb_jobs] list and the `search` field can be tested after the change_meta_sql() changes.
 *
 * Usage (CLI):
 *   php jb-dummy-jobs.php                 - create jobs (default 6 per company)
 *   php jb-dummy-jobs.php create 10       - create 10 jobs per company
 *   php jb-dummy-jobs.php delete          - delete all jobs created by this script
 *
 * Usage (browser, logged in as administrator):
 *   /jb-dummy-jobs.php?jb_action=create&per_company=6
 *   /jb-dummy-jobs.php?jb_action=delete
 *
 * Remove this file after testing.
 */

if ( 'cli' === PHP_SAPI ) {
	$_SERVER['HTTP_HOST']   = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : 'localhost';
	$_SERVER['REQUEST_URI'] = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '/';
}

require_once '../../../../wp-load.php';

if ( 'cli' !== PHP_SAPI && ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Administrator access required.' );
}

if ( ! function_exists( 'JB' ) ) {
	exit( "JobBoardWP plugin is not active.\n" );
}

const JB_DUMMY_META_FLAG = 'jb-dummy-job';

if ( 'cli' === PHP_SAPI ) {
	$jb_action   = isset( $argv[1] ) ? $argv[1] : 'create';
	$per_company = isset( $argv[2] ) ? absint( $argv[2] ) : 6;
} else {
	header( 'Content-Type: text/plain; charset=utf-8' );
	$jb_action   = isset( $_GET['jb_action'] ) ? sanitize_key( $_GET['jb_action'] ) : 'create';
	$per_company = isset( $_GET['per_company'] ) ? absint( $_GET['per_company'] ) : 6;
}
$per_company = max( 1, $per_company );

/**
 * Companies used for the dummy jobs.
 * The first two are the main ones; the rest contain characters which are important
 * for the search SQL (quote, underscore, percent, slash, regex metacharacters).
 */
$companies = array(
	array(
		'name'      => 'Acme Robotics',
		'website'   => 'https://acme-robotics.example.com',
		'tagline'   => 'Building the robots of tomorrow',
		'twitter'   => 'acmerobotics',
		'facebook'  => 'acmerobotics',
		'instagram' => 'acmerobotics',
		'count'     => $per_company,
	),
	array(
		'name'      => 'Globex Software',
		'website'   => 'https://globex.example.com',
		'tagline'   => 'Software for everyone',
		'twitter'   => 'globexsoft',
		'facebook'  => 'globexsoft',
		'instagram' => 'globexsoft',
		'count'     => $per_company,
	),
	array(
		'name'      => "O'Reilly & Sons",
		'website'   => 'https://oreilly-sons.example.com',
		'tagline'   => 'Family business since 1950',
		'twitter'   => '',
		'facebook'  => '',
		'instagram' => '',
		'count'     => 2,
	),
	array(
		'name'      => 'Data_Lab 100% (A/B) .*[Test]$1',
		'website'   => 'https://datalab.example.com',
		'tagline'   => 'Special characters company',
		'twitter'   => '',
		'facebook'  => '',
		'instagram' => '',
		'count'     => 2,
	),
);

$titles = array(
	'Senior PHP Developer',
	'Frontend Engineer',
	'QA Engineer',
	'DevOps Engineer',
	'Product Manager',
	'UX/UI Designer',
	'Data Analyst',
	'Customer Support Specialist',
	'Marketing Manager',
	'Backend Engineer',
	'Technical Writer',
	'Sales Representative',
);

$locations = array( 'London, UK', 'Berlin, Germany', 'New York, USA', 'Kyiv, Ukraine', 'Paris, France', 'Toronto, Canada' );

$paragraphs = array(
	'We are looking for a motivated professional to join our growing team.',
	'You will work closely with engineers, designers and product owners.',
	'Experience with WordPress, PHP and MySQL is a big plus.',
	'We offer flexible working hours, remote-friendly culture and a learning budget.',
	'Strong communication skills and attention to detail are required.',
	'You will be responsible for delivering high quality results on time.',
);

/**
 * Get or create term IDs for a taxonomy.
 *
 * @param string $taxonomy
 * @param array  $names
 *
 * @return int[]
 */
function jb_dummy_get_terms( $taxonomy, $names ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}

	$ids = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'fields'     => 'ids',
		)
	);
	if ( ! empty( $ids ) && ! is_wp_error( $ids ) ) {
		return array_map( 'absint', $ids );
	}

	$ids = array();
	foreach ( $names as $name ) {
		$term = wp_insert_term( $name, $taxonomy );
		if ( ! is_wp_error( $term ) ) {
			$ids[] = absint( $term['term_id'] );
		}
	}
	return $ids;
}

/**
 * Delete all jobs created by this script.
 */
function jb_dummy_delete_jobs() {
	$ids = get_posts(
		array(
			'post_type'      => 'jb-job',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => JB_DUMMY_META_FLAG, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_query_meta_key
			'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_query_meta_value
		)
	);
	foreach ( $ids as $id ) {
		wp_delete_post( $id, true );
	}
	echo 'Deleted dummy jobs: ' . count( $ids ) . "\n";
}

if ( 'delete' === $jb_action ) {
	jb_dummy_delete_jobs();
	exit;
}

$author_id = get_current_user_id();
if ( empty( $author_id ) ) {
	$admins    = get_users(
		array(
			'role'   => 'administrator',
			'number' => 1,
			'fields' => 'ID',
		)
	);
	$author_id = ! empty( $admins ) ? absint( $admins[0] ) : 0;
}

$type_ids     = jb_dummy_get_terms( 'jb-job-type', array( 'Full-time', 'Part-time', 'Freelance', 'Internship', 'Temporary' ) );
$category_ids = jb_dummy_get_terms( 'jb-job-category', array( 'Development', 'Design', 'Marketing', 'Support' ) );
$use_salary   = (bool) JB()->options()->get( 'job-salary' );

$created = 0;
foreach ( $companies as $company ) {
	for ( $i = 0; $i < $company['count']; $i++ ) {
		$title = $titles[ array_rand( $titles ) ];

		shuffle( $paragraphs );
		$content = '<p>' . implode( '</p><p>', array_slice( $paragraphs, 0, 3 ) ) . '</p>';

		$location_type = (string) wp_rand( 0, 1 ); // '0' - onsite, '1' - remote.
		$location      = '0' === $location_type ? $locations[ array_rand( $locations ) ] : '';

		$expiry = JB()->options()->get( 'individual-job-duration' ) ? '' : JB()->common()->job()->calculate_expiry();

		$meta_input = array(
			'jb-location-type'       => $location_type,
			'jb-location'            => $location,
			'jb-application-contact' => 'jobs@' . wp_parse_url( $company['website'], PHP_URL_HOST ),
			'jb-company-name'        => $company['name'],
			'jb-company-website'     => $company['website'],
			'jb-company-tagline'     => $company['tagline'],
			'jb-company-twitter'     => $company['twitter'],
			'jb-company-facebook'    => $company['facebook'],
			'jb-company-instagram'   => $company['instagram'],
			'jb-expiry-date'         => $expiry,
			'jb-last-edit-date'      => time(),
			JB_DUMMY_META_FLAG       => '1',
		);

		if ( $use_salary ) {
			$min = wp_rand( 20, 60 ) * 1000;
			if ( wp_rand( 0, 1 ) ) {
				$meta_input['jb-salary-type']        = 'fixed';
				$meta_input['jb-salary-amount-type'] = 'numeric';
				$meta_input['jb-salary-amount']      = $min;
			} else {
				$meta_input['jb-salary-type']        = 'recurring';
				$meta_input['jb-salary-amount-type'] = 'range';
				$meta_input['jb-salary-min-amount']  = $min;
				$meta_input['jb-salary-max-amount']  = $min + wp_rand( 5, 30 ) * 1000;
				$meta_input['jb-salary-period']      = 'year';
			}
		}

		$job_data = array(
			'post_type'    => 'jb-job',
			'post_author'  => $author_id,
			'post_parent'  => 0,
			'post_title'   => $title,
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_date'    => gmdate( 'Y-m-d H:i:s', time() - wp_rand( 0, 30 * DAY_IN_SECONDS ) ),
			'meta_input'   => $meta_input,
		);

		$job_id = wp_insert_post( wp_slash( $job_data ), true );
		if ( is_wp_error( $job_id ) ) {
			echo 'Error: ' . $job_id->get_error_message() . "\n";
			continue;
		}

		if ( ! empty( $type_ids ) ) {
			wp_set_post_terms( $job_id, array( $type_ids[ array_rand( $type_ids ) ] ), 'jb-job-type' );
		}
		if ( ! empty( $category_ids ) && JB()->options()->get( 'job-categories' ) ) {
			wp_set_post_terms( $job_id, array( $category_ids[ array_rand( $category_ids ) ] ), 'jb-job-category' );
		}

		++$created;
		echo sprintf( "#%d  %-30s  %s\n", $job_id, $title, $company['name'] );
	}
}

echo "\nCreated dummy jobs: $created\n";
echo "Search examples: 'Acme', 'Globex', 'Engineer', \"O'Reilly\", 'Data_Lab', '100%', 'A/B', '.*[Test]$1'\n";
