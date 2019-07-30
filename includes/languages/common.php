<?php
/*
  $Id: japanese.php,v 1.24 2003/06/06 01:34:50 ptosh Exp $
  utf8
*/

//
// mb_internal_encoding() is set for PHP-4.3.x(Zend Multibyte)
//
// A compatible module is loaded for environment without mbstring-extension
//
if (extension_loaded('mbstring')) {
  mb_internal_encoding('utf8'); // 内部コードを指定
} else {
  include_once(DIR_WS_LANGUAGES . 'jcode.phps');
  include_once(DIR_WS_LANGUAGES . 'mbstring_wrapper.php');
}

// look in your $PATH_LOCALE/locale directory for available locales..
// on RedHat try 'en_US'
// on FreeBSD try 'en_US.ISO_8859-1'
// on Windows try 'en', or 'English'
@setlocale(LC_TIME, 'ja_JP');
define('DATE_FORMAT_SHORT', '%Y/%m/%d');  // this is used for strftime()
//define('DATE_FORMAT_LONG', '%Y年%B%e日 %A'); // this is used for strftime()
define('DATE_FORMAT_LONG', '%Y年 %m月 %d日'); // this is used for strftime()
define('DATE_FORMAT', 'Y/m/d'); // this is used for date()
define('DATE_TIME_FORMAT', DATE_FORMAT_SHORT . ' %H:%M:%S');

////
// Return date in raw format
// $date should be in format mm/dd/yyyy
// raw date is in format YYYYMMDD, or DDMMYYYY
function tep_date_raw($date, $reverse = false) {
  if ($reverse) {
    return substr($date, 8, 2) . substr($date, 5, 2) . substr($date, 0, 4);
  } else {
    return substr($date, 0, 4) . substr($date, 5, 2) . substr($date, 8, 2);
  }
}

// Global entries for the <html> tag
//define('HTML_PARAMS','dir="LTR" lang="ja"');
define('HTML_PARAMS','lang="ja" xml:lang="ja"');

// charset for web pages and emails
define('CHARSET', 'utf-8');    // Shift_JIS / euc-jp / iso-2022-jp

//ERROR
define('ERROR_TEP_MAIL', 'エラー: 指定されたSMTP サーバからメールを送信できません。 php.ini のSMTP サーバ設定を確認して、必要があれば修正してください。');

//WARNING
define('ICON_WARNING', '警告');
define('WARNING_INSTALL_DIRECTORY_EXISTS', '警告: インストール・ディレクトリ(/install)が存在したままです: ' . dirname($_SERVER['SCRIPT_FILENAME']) . '/install. ディレクトリはセキュリティ上の危険がありますので削除してください。');
define('WARNING_CONFIG_FILE_WRITEABLE', '警告: 設定ファイルに書き込み権限が設定されたままです: '. 'configure.phpファイルのユーザ権限を変更してください。');
define('WARNING_CONFIGINC_FILE_WRITEABLE', '警告: 設定ファイルに書き込み権限が設定されたままです: '. 'config.inc.phpファイルのユーザ権限を変更してください。');
define('WARNING_SESSION_DIRECTORY_NON_EXISTENT', '警告: セッション・ディレクトリが存在しません: '. tep_session_save_path() . '. セッションを利用するためにディレクトリを作成してください。');
define('WARNING_SESSION_DIRECTORY_NOT_WRITEABLE', '警告: セッション・ディレクトリに書き込みができません: ' . tep_session_save_path() . '. セッション・ディレクトリに正しいユーザ権限を設定してください。');
define('WARNING_SESSION_AUTO_START', '警告: セッション・オートスタートが有効になっています。設定ファイル（php.ini）で無効に設定し、ウェブサーバをリスタートしてください。');
?>
