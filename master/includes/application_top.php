<?php
// start the timer for the page parse time log
define('PAGE_PARSE_START_TIME', microtime());

// set the level of error reporting
//error_reporting(E_ALL & ~E_NOTICE);
error_reporting(0);

// check if register_globals is enabled.
// since this is a temporary measure this message is hardcoded. The requirement will be removed before 2.2 is finalized.
if (function_exists('ini_get')) {
// ini_get('register_globals') or exit('FATAL ERROR: register_globals is disabled in php.ini, please enable it!');
	!ini_get('register_globals') or exit('FATAL ERROR: register_globals is enabled in php.ini, please disable it!');
}

// disable use_trans_sid as tep_href_link() does this manually
if (function_exists('ini_set')) @ini_set('session.use_trans_sid', 0);

// Set the local configuration parameters - mainly for developers
if (file_exists('includes/local/configure.php')) include('includes/local/configure.php');

// include server parameters
require('includes/configure.php');
require('../includes/configure.php');

// SpiderKiller r8
require(DIR_WS_INCLUDES. 'spider_configure.php');
//SpiderKiller r8_eof

// define the project version
define('PROJECT_VERSION', 'V3A Embedded.V2R &beta');

// set the type of request (secure or not)
$request_type = (getenv('HTTPS') == 'on') ? 'SSL' : 'NONSSL';

// define the filenames used in the project

// define the database table names used in the project
require(DIR_WS_INCLUDES. 'db_table.php');

// check if sessions are supported, otherwise use the php3 compatible session class
if (!function_exists('session_start')) {
	define('PHP_SESSION_NAME', 'sID');
	define('PHP_SESSION_SAVE_PATH', '/tmp');
	include(DIR_WS_CLASSES. 'sessions.php');
}

// define how the session functions will be used
require(DIR_WS_FUNCTIONS. 'sessions.php');
tep_session_name('sessionId');

// include the database functions
require(DIR_WS_FUNCTIONS. 'database.php');

// make a connection to the database... now
tep_db_connect() or die('Unable to connect to database server!');

// set the application parameters (can be modified through the administration tool)
require('includes/define.inc.php');
//require('../includes/define.inc.php');

// if gzip_compression is enabled, start to buffer the output
if ( (GZIP_COMPRESSION == 'true') && ($ext_zlib_loaded = extension_loaded('zlib')) && (PHP_VERSION >= '4') ) {
	if (($ini_zlib_output_compression = (int)ini_get('zlib.output_compression')) < 1) {
		if (PHP_VERSION >= '4.0.4') {
			ob_start('ob_gzhandler');
		} else {
			include(DIR_WS_FUNCTIONS . 'gzip_compression.php');
			ob_start();
			ob_implicit_flush();
		}
	} else {
		ini_set('zlib.output_compression_level', GZIP_LEVEL);
	}
}

// set the HTTP GET parameters manually if search_engine_friendly_urls is enabled
if (SEARCH_ENGINE_FRIENDLY_URLS == 'true') {
	if (strlen(getenv('PATH_INFO')) > 1) {
		$GET_arrays = array();
		$PHP_SELF = str_replace(getenv('PATH_INFO'), '', $_SERVER['PHP_SELF']);
		$vars = explode('/', substr(getenv('PATH_INFO'), 1));
		for ($i=0, $n=sizeof($vars); $i<$n; $i++) {
			if (strpos($vars[$i], '[]')) {
				$GET_arrays[substr($vars[$i], 0, -2)][] = $vars[$i+1];
			} else {
				$_GET[$vars[$i]] = $vars[$i+1];
			}
			$i++; 
		}

		if (sizeof($GET_arrays) > 0) {
			while (list($key, $value) = each($GET_arrays)) {
				$_GET[$key] = $value;
			}
		}
	}
} else {
	$PHP_SELF = $_SERVER['PHP_SELF'];
}

// include cache functions if enabled
if (USE_CACHE == 'true') include(DIR_WS_FUNCTIONS . 'cache.php');

// include navigation history class
require(DIR_WS_CLASSES. 'navigation_history.php');

// some code to solve compatibility issues
require(DIR_WS_FUNCTIONS. 'compatibility.php');

// lets start our session
if (isset($_POST[tep_session_name()])) {
	tep_session_id($_POST[tep_session_name()]);
} elseif ( (getenv('HTTPS') == 'on') && isset($_GET[tep_session_name()]) ) {
	tep_session_id($_GET[tep_session_name()]);
}

/* r5
if (function_exists('session_set_cookie_params')) {
	session_set_cookie_params(0, substr(DIR_WS_DOCUMENT_ROOT, 0, -1));
}
r5_eof r8 */
if (function_exists('session_set_cookie_params')) {
	$cookie_path = substr(DIR_WS_DOCUMENT_ROOT, 0, -1);
	if (!$cookie_path) $cookie_path = '/';
	session_set_cookie_params(0, $cookie_path);
}
// r8_eof

tep_session_start();

// include the mail classes
require(DIR_WS_CLASSES. 'mime.php');
require(DIR_WS_CLASSES. 'email.php');

// include the language translations
require(DIR_WS_LANGUAGES. 'common.php');

// define our general functions used application-wide
require(DIR_WS_FUNCTIONS. 'general.php');
require(DIR_WS_FUNCTIONS. 'html_output.php');
//require(DIR_WS_ADMIN_FUNCTIONS. 'enhance.inc.php');
require(DIR_WS_FUNCTIONS. 'enhance.inc.php');

// navigation history
if (tep_session_is_registered('navigation')) {
	if (PHP_VERSION < 4) {
		$broken_navigation = $_SESSION['navigation'];
		$_SESSION['navigation'] = new navigationHistory;
		$_SESSION['navigation']->unserialize($broken_navigation);
	}
} else {
	tep_session_register('navigation');
	$_SESSION['navigation'] = new navigationHistory;
}
$_SESSION['navigation']->add_current_page();

// include the who's online functions
//require('functions/whos_online.php');
//tep_update_whos_online();

// include the password crypto functions
require(DIR_WS_FUNCTIONS. 'password_funcs.php');

// include validation functions (right now only email address)
require(DIR_WS_FUNCTIONS. 'validations.php');

// split-page-results
require(DIR_WS_CLASSES. 'split_page_results.php');

/*
		// infobox
		require(DIR_WS_CLASSES. 'boxes.php');
*/

// include the breadcrumb class and start the breadcrumb trail
require(DIR_WS_CLASSES. 'breadcrumb.php');
//$breadcrumb = new breadcrumb;
//define('HEADER_TITLE_DEFAULT_ADMIN', '管理画面TOP');
//$breadcrumb->add(HEADER_TITLE_DEFAULT_ADMIN, tep_href_link(FILENAME_DEFAULT_ADMIN_SUPPLIER));

// set which precautions should be checked
define('WARN_INSTALL_EXISTENCE', 'true');
define('WARN_CONFIG_WRITEABLE', 'true');
define('WARN_SESSION_DIRECTORY_NOT_WRITEABLE', 'true');
define('WARN_SESSION_AUTO_START', 'true');

if (!tep_session_is_registered('customer_id')) {
	$_SESSION['navigation']->set_snapshot();
//	tep_redirect(tep_href_link(FILENAME_OS_LOGIN, '', 'SSL'));
}

// setcookie
require(DIR_WS_FUNCTIONS.'setcookie.php');

// unique
require('../includes/unique_array.php');
require('../includes/unique_email.php');
require('../includes/unique_function.php');

// next_permisstion
//require(DIR_WS_CLASSES . 'next_permission.php');

//▼最大代理店階層
define('MAX_AGENT_LAYER',zGetSysSetting("sys_max_agent_layer"));

//usehttps
if(getenv('HTTPS') != 'on' && SITE_ROLE == 'rel'){
	$surl = HTTPS_SERVER.substr($_SERVER['REQUEST_URI'],1);
	tep_redirect($surl,'', 'SSL');
}

////SEO
$title = TITLE;
$meta_description = '';
$meta_keywords = '';
$head_h1 = '';
//$favicon = '<link rel="shortcut icon" type="image/vnd.microsoft.icon" href="../images/favicon/favicon1.ico">';
$favicon = '';
?>
