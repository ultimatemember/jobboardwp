/**
 * JobBoardWP dependencies
 *
 * @type {Gulp}
 */
const { src, dest, parallel } = require( 'gulp' );
const sass        = require( 'gulp-sass' )( require( 'node-sass' ) );
const uglify      = require( 'gulp-uglify' );
const cleanCSS    = require( 'gulp-clean-css' );
const rename      = require( 'gulp-rename' );

function exclude_css( path ) {
	var exclude = ['assets/' + path + '/sass/*.sass'];

	if ( path === 'admin' ) {

	} else if ( path === 'front' ) {
		exclude.push( '!assets/' + path + '/sass/suggest.sass' );
		exclude.push( '!assets/' + path + '/sass/dropdown.sass' );
		exclude.push( '!assets/' + path + '/sass/notice.sass' );
		exclude.push( '!assets/' + path + '/sass/tooltip.sass' );
	}

	return exclude;
}

function js( path ) {
	return src( ['assets/' + path + '/js/*.js', '!assets/' + path + '/js/*.min.js'] )
		.pipe( uglify() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( dest( 'assets/' + path + '/js' ) );
}

function css( path ) {
	sass.compiler = require( 'node-sass' );

	var src_array = exclude_css( path );
	return src( src_array )
		.pipe( sass().on( 'error', sass.logError ) )
		.pipe( dest( 'assets/' + path + '/css' ) );
}

function min_css( path ) {
	sass.compiler = require( 'node-sass' );

	var src_array = exclude_css( path );
	return src( src_array )
		.pipe( sass().on( 'error', sass.logError ) )
		.pipe( cleanCSS() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( dest( 'assets/' + path + '/css' ) );
}

function fontsmax() {
	return src( 'assets/common/libs/fontawesome/scss/*.scss' )
		.pipe( sass().on( 'error', sass.logError ) )
		.pipe( dest( 'assets/common/libs/fontawesome/css' ) );
}
function fontsmin() {
	return src( 'assets/common/libs/fontawesome/scss/*.scss' )
		.pipe( sass().on( 'error', sass.logError ) )
		.pipe( cleanCSS() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( dest( 'assets/common/libs/fontawesome/css' ) );
}


function minify_libs() {
	return src( ['assets/common/css/jquery-ui.css'] )
		.pipe( cleanCSS() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( dest( 'assets/common/css/' ) );
}

exports.admin_js = js( 'admin' );
exports.admin_css = css( 'admin' );
exports.admin_min_css = min_css( 'admin' );
exports.common_js = js( 'common' );
exports.common_css = css( 'common' );
exports.common_min_css = min_css( 'common' );
exports.front_js = js( 'frontend' );
exports.front_css = css( 'frontend' );
exports.front_min_css = min_css( 'frontend' );
exports.fontsmax = fontsmax;
exports.fontsmin = fontsmin;

exports.minify_libs = minify_libs;

function admin() {
	parallel( js( 'admin' ), css( 'admin' ), min_css( 'admin' ) );
}

function common() {
	parallel( js( 'common' ), css( 'common' ), min_css( 'common' ) );
}

function front() {
	parallel( js( 'frontend' ), css( 'frontend' ), min_css( 'frontend' ) );
}

function common_css() {
	return src( ['assets/common/libs/tipsy/css/*.css', '!assets/common/tipsy/css/*.min.css'] )
		.pipe( cleanCSS() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( dest( 'assets/common/libs/tipsy/css' ) );
}


function common_js() {
	return src( ['assets/common/libs/tipsy/js/*.js', '!assets/common/tipsy/js/*.min.js'] )
		.pipe( uglify() )
		.pipe( rename( { suffix: '.min' } ) )
		.pipe( dest( 'assets/common/libs/tipsy/js' ) );
}

exports.common_css = common_css;
exports.common_js = common_js;

function common_libs( done ) {
	parallel( common_css, common_js );
	done();
}

function fonts() {
	parallel( fontsmax, fontsmin );
}

exports.admin = admin;
exports.front = front;
exports.common = common;
exports.common_libs = common_libs;
exports.fonts = fonts;

function defaultTask( done ) {
	parallel( admin, front, common );
	done();
}
exports.default = defaultTask;
