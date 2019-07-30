<?php
require('includes/application_top.php');

//▼初期設定
$res = "err";
$top            = $_POST['top'];
$charge_id      = $_POST['sendid'];		//請求番号
$peyment_amount = $_POST['damt'];		//入金金額
$date_payment   = $_POST['drec'];		//入金確認日
$memo           = $_POST['memo'];		//メモ


//▼データ取得
if(($_POST['top'] == 'send')AND($charge_id)AND($peyment_amount)AND($date_payment)){
	
	//▼請求入金処理
	require('./mutil/mut_edit_charge.php');
}

$string = 'top>'.$top."\n";
$string.= 'chid>'.$charge_id."\n";
$string.= 'amt>'.$peyment_amount."\n";
$string.= 'd_pay>'.$date_payment."\n";

//write_log($string,'w');

echo $res;
?>