<?php
/*
$email_query = tep_db_query("
SELECT 
	c.Name,
	c.TranceferAmount,
	c.Ripple_Rate,
	c.Ripple_XRP,
	c.WalletName,
	c.Passphrase,
	c.Ripple_Address,
	c.Ripple_Secret_Key,
	c.MobileAdress,
	c.PcAdress,
	com.Name AS Company_name 
FROM ((". TABLE_CUSTOMER. " c
LEFT JOIN ". TABLE_STAFF. " s ON c.StaffId = s.StaffId)
LEFT JOIN ". TABLE_STAFF. " com ON s.CompanyAdminNum = com.AdminNum)
WHERE c.State != 'z'
AND c.CustomerId = '".$_POST['CustomerId']."'
");
$email = mysql_fetch_assoc($email_query);

$email_subject = 'RippleWallet作成　および　XRP反映が完了しました';

$email_head = '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'. "\n";
$email_head.= 'RippleWallet作成　および　XRP反映が完了しました！ '. "\n";
$email_head.= '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━'. "\n";

$email_text = 'お世話になっております。'. "\n";
$email_text.= '大変お待たせしました、 RippleWallet作成　および　XRP反映が完了しましたので'. "\n";
$email_text.= 'ご確認ください。'. "\n";
$email_text.= "\n";
$email_text.= "\n";
$email_text.= '【PC版　ブラウザー】'. "\n";
$email_text.= 'GoogleChromeでのログインをお願いいたします。'. "\n";
$email_text.= 'PCでは、Googlechromeからログイン可能です。'. "\n";
$email_text.= "\n";
$email_text.= '【モバイル版　ブラウザー】'. "\n";
$email_text.= 'モバイル、スマホなどからのログインは、環境により大変異なる場合がございます。'. "\n";
$email_text.= '確実に動作するとRippleが保障はしていませんが'. "\n";
$email_text.= 'ログイン時にSecret Account Keyをお試しください。'. "\n";
$email_text.= '（操作方法はアプリにより異なる）'. "\n";
$email_text.= ' '. "\n";
$email_text.= '【申込み情報】'. "\n";
$email_text.= '■ 名前：　'.$email['Name'].'様'. "\n";
$email_text.= '■ 入金額：　'.number_format($email['TranceferAmount']).'円'. "\n";
$email_text.= '■ XRP：　'.$email['Ripple_XRP']. "\n";
$email_text.= '■ 買い付け価格：　@'.$email['Ripple_Rate'].'円 '. "\n";
$email_text.= "\n";
$email_text.= "\n";
$email_text.= '【ログイン】'. "\n";
$email_text.= 'https://ripple.com/client/#/login'. "\n";
$email_text.= '■ wallet name：　'.$email['WalletName']. "\n";
$email_text.= '■ passpharase：　'.$email['Passphrase']. "\n";
$email_text.= '■ ripple address：　'.$email['Ripple_Address']. "\n";
$email_text.= '■ Secret Account Key：　'.$email['Ripple_Secret_Key']. "\n";
$email_text.= "\n";
$email_text.= "\n";
$email_text.= '【ログイン時の注意事項】'. "\n";
$email_text.= '「ログインする」ボタンの右を確認ください。'. "\n";
$email_text.= '１．Payward'. "\n";
$email_text.= '２．Payward, Local Browser'. "\n";
$email_text.= '３．Local Browser'. "\n";
$email_text.= 'ログインには３種類の方法があります。'. "\n";
$email_text.= '２番目の「Payward, Local Browser」を選択し「OK」を押してから'. "\n";
$email_text.= 'ログインしてください。'. "\n";
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
$email_foot.= $email['Company_name']. "\n";
$email_foot.= 'ripple事業部'. "\n";
$email_foot.= 'e-mail　：　 info@ripplejp.us'. "\n";
$email_foot.= '━━━━━━━━━━━━━━━━━━━━━━━━'. "\n";

$email_text = $email_head. $email_text. $email_foot;

$array = array(SITE_OWNER_EMAIL,$email['MobileAdress'],$email['PcAdress']);
foreach ($array as $var) {
	tep_mail($email['Name'], $var, $email_subject, $email_text, SITE_OWNER, SITE_OWNER_EMAIL);
}
*/
?>