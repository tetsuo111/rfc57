<?php
// config --------------------------------------------------

//サーチエンジン対策のURLを使用(開発中)
define('SEARCH_ENGINE_FRIENDLY_URLS', 'false'); //サイトの全リンクでサーチエンジン対策用URLの使用・不使用設定. true (使用) false(不使用)

//最小値
define('ACCOUNT_NAME_MIN_LENGTH', '1'); // 名前の文字数の最小値設定
define('ACCOUNT_DOB_MIN_LENGTH', '10'); // 生年月日の文字数の最小値設定
define('ACCOUNT_EMAIL_ADDRESS_MIN_LENGTH', '6'); // E-Mail アドレスの文字数の最小値設定
define('ACCOUNT_ADDRESS_MIN_LENGTH', '1'); // 住所の文字数の最小値設定
define('ACCOUNT_COMPANY_LENGTH', '2'); // 会社名の文字数の最小値設定
define('ACCOUNT_ZIP_A_LENGTH', '3'); // 郵便番号の文字数の最小値設定
define('ACCOUNT_ZIP_B_LENGTH', '4'); // 郵便番号の文字数の最小値設定
define('ACCOUNT_TEL_MIN_LENGTH', '3'); // 電話番号の文字数の最小値設定
define('PASSWORD_MIN_LENGTH', '4'); // パスワードの文字数の最小値設定
define('TRANSACTION_MIN_LENGTH', '25'); //パスワードの文字数の最小値設定

define('ENTRY_ARTICLE_MIN_LENGTH', '1');
define('ENTRY_ARTICLE_TITLE_MAX_LENGTH', '20');
define('ENTRY_ARTICLE_OUTLINE_MAX_LENGTH', '40');
define('ENTRY_ARTICLE_DETAIL_MAX_LENGTH', '200');
define('ENTRY_ARTICLE_IMAGE_MAX_NUM', '6');



//画像サイズを計算
define('CONFIG_CALCULATE_IMAGE_SIZE', 'true'); //画像サイズを自動的に計算 ('true', 'false'),

//ページ・パース時間を記録
define('PAGE_PARSE_TIME', 'false'); //ページのパースに要した時間をログに記録する場合に設定します ('true', 'false'),

//ログの格納先
define('PAGE_PARSE_TIME_LOG', '/var/log/www/tep/page_parse_time.log'); // ページのパースログを保存するディレクトリとファイル名設定

//ログ日付形式
define('PARSE_DATE_TIME_FORMAT', '%d/%m/%Y %H:%M:%S'); // ログに記録する日付形式設定

//ページ・パース時間を表示
define('DISPLAY_PAGE_PARSE_TIME', 'true'); // ページ下にパース時間表示設定 ('true', 'false')

//データベース問い合わせを記録
define('DB_TRANSACTIONS', 'false'); // ログにデータベース問い合わせを記録 (PHP4のみ) 設定 ('true', 'false')

//キャッシュを使用
define('USE_CACHE', 'false'); // キャッシュ機能の使用・不使用設定

//キャッシュ・ディレクトリ
define('DIR_FS_CACHE', '/tmp/'); // キャッシュ・ファイルが保存されるディレクトリ設定

//E-Mail送信設定
define('EMAIL_TRANSPORT', 'smtp'); // E-Mail 送信にsendmailへのローカル接続を使用するか TCP/IP 経由の SMTP接続を使用するか('sendmail', 'smtp')

//E-Mailの改行
define('EMAIL_LINEFEED', 'LF'); // メール・ヘッダを区切る改行コード指定 ('LF', 'CRLF')

//メール送信にMIME HTMLを使用
define('EMAIL_USE_HTML', 'false'); // E-MailのHTML形式送信設定 ('true', 'false')

//E-MailアドレスをDNSで確認
define('ACCOUNT_EMAIL_ADDRESS_CHECK', 'false'); // E-MailアドレスのDNSサーバ問い合わせ設定 ('true', 'false')

//E-Mailを送信
define('SEND_EMAILS', 'true'); // E-Mailを外部に送信 ('true', 'false')

//GZip圧縮を使用する
define('GZIP_COMPRESSION', 'false'); // HTTP GZip 圧縮ページ送出設定 ('true', 'false')

//圧縮レベル
define('GZIP_LEVEL', '5'); // 使用する圧縮レベル (0 = 最小, 9 = 最大)

//セッションの再生成
define('SESSION_RECREATE', 'false'); // ログオンまたはアカウント作成時のセッション再生成設定 ('True', 'False')

//Google Maps API Key
//define('GMAP_API_KEY', 'ABQIAAAAmYRbUbZoTXONxt5eD4Z2wxTnKSfjwIJtGhYoXMufIDSA7mstHRQAz83zN_PuTSmtaMTJoSrwzFpcaQ');

//master password
//define('MASTER_PASSWORD', '');


//▼ウォレット注文文字
define('WC_ORDER_PREFIX','NW');

//▼必須表示
define('I_MUST','*');

// languages --------------------------------------------------

?>