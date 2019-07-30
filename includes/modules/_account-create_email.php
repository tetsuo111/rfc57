<?php
/*
$url = 'http://a-cafe.jp/';
$email_subject = '【'.SITE_NAME. '】'. $new_customer_name. '様の登録が完了しました';

$email_head = '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'. "\n";
$email_head.= '■'. SITE_NAME. ' : '. tep_ymjwhis(). '送信'. "\n";
$email_head.= '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'. "\n";
$email_head.= '　'. $new_customer_name. '様'."\n";
$email_head.= '　この度は青山CAFEにお申し込み頂き、誠にありがとうございます。'."\n";
$email_head.= "\n";
$email_head.= '　本メールは当サービスをご利用いただくにあたり非常に重要なお知らせで'."\n";
$email_head.= '　ございます。'."\n";
$email_head.= '　お取り扱いには十分にご注意の上、'."\n";
$email_head.= '　大切に保管してくださいますようお願い申し上げます。'."\n";
$email_head.= "\n";

$email_text = '1. ログイン'. "\n";
$email_text.= '￣￣￣￣￣￣￣￣￣￣￣￣￣'. "\n";
$email_text.= ''. "\n";
$email_text.= 'ログインID:　'. $customer_email_address_1. "\n";
$email_text.= 'パスワード:　'. $newpass. "\n";
$email_text.= 'URL:　'. $url. "\n";
$email_text.= "\n\n";

$email_text.= '2. 登録内容'. "\n";
$email_text.= '￣￣￣￣￣￣￣￣￣￣￣￣￣'. "\n";
$email_text.= 'お名前:　'. $new_customer_name. "\n";
$email_text.= '電話番号:　'. $customer_tel_1. "\n";
$email_text.= 'メールアドレス:　'. $customer_email_address_1. "\n";
$email_text.= '登録日:　'. tep_ymjwhis(). "\n";
$email_text.= "\n\n";

$email_foot.= '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'. "\n";
$email_foot.= 'このメールに返信しないようお願いいたします。'. "\n";
$email_foot.= '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'. "\n";
$email_foot.= "\n";

$email_text = $email_head. $email_text. $email_foot;

tep_mail($new_customer_name. '様', $customer_email_address_1, $email_subject, $email_text, SITE_OWNER, SITE_OWNER_EMAIL);

$array = array(SITE_OWNER_EMAIL, ARCHITECTURE_EMAIL);
foreach ($array as $var) {
	tep_mail($new_customer_name. '様', $var, $email_subject, $email_text, SITE_OWNER, SITE_OWNER_EMAIL);
}
*/
?>
