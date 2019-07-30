<?php
	/*
$email_com_query = tep_db_query("
	SELECT com.Name
	FROM (". TABLE_STAFF. " c 
	LEFT JOIN ". TABLE_STAFF. " com ON c.CompanyAdminNum = com.AdminNum)
	WHERE c.AdminNum = '".$new_admin_num."'
");
$email_com = mysql_fetch_assoc($email_com_query);
$company_name = $email_com['Name'];

$url_form_mail = 'http://www.ripplejp.us/';
$url_admin_mail = 'http://www.ripplejp.us/admin/';

//------メール内容ここから
$email_subject = 'RippleWallet　営業登録が完了しました！';

$email_head = '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'. "\n";
$email_head.= 'RippleWallet　営業登録が完了しました！ '. "\n";
$email_head.= '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'. "\n";

$email_text = 'お世話になっております。'. "\n";
$email_text.= 'RippleWalletの営業登録が完了しましたので'. "\n";
$email_text.= 'ご確認ください。'. "\n";
$email_text.= "\n";
$email_text.= "\n";
$email_text.= '【登録内容】'. "\n";
$email_text.= '■ 管理番号：　'.$new_admin_num. "\n";
$email_text.= '■ 氏名(カナ)：　'.$name_kana.'様'. "\n";
$email_text.= '■ 氏名：　'.$name.'様'. "\n";
$email_text.= '■ パスワード：　'.$password. "\n";
$email_text.= '■ 郵便番号：　'.$postcode. "\n";
$email_text.= '■ 住所：　'.$residence. "\n";
$email_text.= '■ TEL：　'.$phone_number. "\n";
$email_text.= '■ 連絡用PCアドレス：　'.$pc_adress. "\n";
$email_text.= "\n";
$email_text.= "\n";
$email_text.= '【RippleWallet 申込み】'. "\n";
$email_text.= '■ URL(申込みフォーム)：　'.$url_form_mail. "\n";
$email_text.= '■ 管理番号：　'.$new_admin_num. "\n";
$email_text.= "\n";
$email_text.= "\n";
$email_text.= '【管理画面】'. "\n";
$email_text.= '■ URL(管理画面)：　'.$url_admin_mail. "\n";
$email_text.= '■ 管理番号：　'.$new_admin_num. "\n";
$email_text.= '■ パスワード：　'.$password. "\n";
$email_text.= "\n";
$email_text.= "\n";
$email_text.= '【参考】'. "\n";
$email_text.= 'Rippleは革命的発想と高度な技術により、瞬く間に世界中に広まっています。'. "\n";
$email_text.= '近い将来、２１世紀の新しい金融システムとして認知される日を'. "\n";
$email_text.= '私どもは、楽しみにしています。'. "\n";
$email_text.= "\n";
$email_text.= 'ripple walletに関するご不明な点がございましたら、'. "\n";
$email_text.= '弊社までお気軽にお問い合わせ下さい。'. "\n";
$email_text.= "\n";
	
$email_foot = '━━━━━━━━━━━━━━━━━━━━━━━━'. "\n";
$email_foot.= $company_name. "\n";
$email_foot.= 'ripple事業部'. "\n";
$email_foot.= 'e-mail　：　 info@ripplejp.us'. "\n";
$email_foot.= '━━━━━━━━━━━━━━━━━━━━━━━━'. "\n";

$email_text = $email_head. $email_text. $email_foot;

$array = array(SITE_OWNER_EMAIL,$pc_adress);
foreach ($array as $var) {
	tep_mail($name, $var, $email_subject, $email_text, SITE_OWNER, SITE_OWNER_EMAIL);
}
*/
?>