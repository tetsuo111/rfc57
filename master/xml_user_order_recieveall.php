<?php
require('includes/application_top.php');
	
//▼初期設定
$res = "err";
$top       = $_POST['top'];
$order_id  = $_POST['sendid'];
$o_figure  = $_POST['dfigure'];


//▼データ取得
if(($_POST['top'] == "done")AND($order_id)AND($o_figure)){

	//▼入金完了処理
	require('./mutil/mut_order_recieveall.php');
}

$string = 'top:'.$top."\n";
$string.= 'sendid:'.$order_id."\n";
$string.= 'ofigure:'.$o_figure."\n";

//write_log($string,'w');

echo $res;
?>