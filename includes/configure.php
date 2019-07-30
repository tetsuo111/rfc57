<?php

//サイト名
define('MM_SITENAME' ,'マナーズサウンド協会');
define('MM_SITEHOSITE','user.manners-assoc.com');
//define('MM_SITEHOSITE','manners.cis-co.tokyo');
define('MAIL_DOMAIN','manners-assoc.com');

if (mb_strpos($_SERVER["SERVER_NAME"], MM_SITEHOSITE) === FALSE) {


} else {
	
	//----- サーバー設定 -----//
	define('DOMAIN', ''.MM_SITEHOSITE.'');
	define('HTTP_SERVER' , 'http://'.MM_SITEHOSITE.'/');
	define('HTTPS_SERVER', 'https://'.MM_SITEHOSITE.'/');
	
	//----- サイト設定 -----//
	define('SITE_NAME' , MM_SITENAME);								//サイト名
	define('SITE_OWNER', MM_SITENAME);								//サイト運営責任者名
	define('TITLE'     , MM_SITENAME);								//ブラウザ表示タイトル
	define('LOGO_TITLE', MM_SITENAME);								//サイト名
	define('SITE_ROLE' , 'dev');									//サイト役割 rel：本番用 dev：開発用
	
	//----- メール設定 -----//
	define('EMAIL_FROM', MM_SITENAME.'<info@'.MAIL_DOMAIN.'>');	//顧客に送られるメールの差出人名とアドレス

	define('SITE_OWNER_EMAIL'   , 'info@'.MAIL_DOMAIN);			//サイトで使用するメールアドレス
	define('SITE_OWNER_EMAIL2'  , 'info@'.MAIL_DOMAIN);			//問合せで使用するメールアドレス
	define('SITE_CUSTOMER_EMAIL', 'support@'.MAIL_DOMAIN);		//顧客対応で使用するメールアドレス
	define('SITE_REQUEST_EMAIL' , 'info@'.MAIL_DOMAIN);			//代理店対応で使うメールアドレス
	define('SITE_LOG_EMAIL'     , '');								//履歴用に使うメールアドレス
	
	define('DEV_LOG_EMAIL','saitou@japanfaa.com');					//開発確認用メールアドレス
	
	define('SITE_OWNER_TELL', '');  
	define('SITE_OWNER_NAME', MM_SITENAME);  
}

define('ENABLE_SSL'           , true);								// secure webserver for checkout procedure?
define('DIR_WS_DOCUMENT_ROOT' , '/');								// absolute path required
define('DIR_WS_IMAGES'        , 'images/');
define('DIR_WS_ADMIN_ROOT'    , 'master/');							// absolute path required
define('DIR_WS_ICONS'         , DIR_WS_IMAGES . 'icons/');
define('DIR_WS_BUTTONS'       , DIR_WS_IMAGES . 'buttons/');
define('DIR_WS_INCLUDES'      , 'includes/');
define('DIR_WS_FUNCTIONS'     , DIR_WS_INCLUDES . 'functions/');
define('DIR_WS_CLASSES'       , DIR_WS_INCLUDES . 'classes/');
define('DIR_WS_MODULES'       , DIR_WS_INCLUDES . 'modules/');
define('DIR_WS_LANGUAGES'     , DIR_WS_INCLUDES . 'languages/');

define('DIR_WS_UPLOADS'               , 'uploads/');
define('DIR_WS_UPLOADS_ORG'           , 'uploads/org/');
define('DIR_WS_UPLOADS_IDENTIFICATION', 'uploads/identification/');
define('DIR_WS_UPLOADS_DOCS'          , 'uploads/docs/');
define('DIR_WS_UPLOADS_IMAGES'          , 'uploads/images/');

define('DIR_WS_ARTICLE_IMAGES'        , DIR_WS_UPLOAD. 'article/');
define('DIR_WS_ARTICLE_THUMBNAIL'     , DIR_WS_UPLOAD. DIR_WS_ARTICLE_IMAGES. 'thumbnail/');

define('CERT_EMAIL_FROM_ADDR','info@manners-assoc.com');
//define('CERT_EMAIL_FROM_ADDR','info@manners02.sakura.ne.jp');
define('CERT_EMAIL_FROM_NAME','マナーズサウンド協会');
define('CERT_EMAIL_ENCODING','base64');
define('CERT_EMAIL_SMTP_HOST','manners02.sakura.ne.jp');
//define('CERT_EMAIL_SMTP_HOST','www1565.sakura.ne.jp');
define('CERT_EMAIL_PASSWORD','yuri3363');
define('CERT_EMAIL_SMTP_SECURE','tls');  //tls(587) or ssl(465)
define('CERT_EMAIL_PORT',587);
define('CERT_EMAIL_CHARSET','UTF-8');



//===========================
//ユーザーID文字列
define('USER_ID_PREFIX','rst');
define('MY_CODE_PREFIX','rtiv');
//===========================

define('I_MUST','*');


// define our database connection
if (mb_strpos($_SERVER["SERVER_NAME"], MM_SITEHOSITE) === FALSE) {


} else {

	define('DB_SERVER'          , '127.0.0.1'); // eg, localhost - should not be empty for productive servers
	define('DB_SERVER_USERNAME' , 'gateway');
	define('DB_SERVER_PASSWORD' , 'k8Kr%epPJ4zi');
	define('DB_DATABASE'        , 'rfc_tes00');

}

define('USE_PCONNECT'   , 'true'); // use persistent connections?
define('STORE_SESSIONS' , 'mysql'); // leave empty '' for default handler or set to 'mysql'

?>