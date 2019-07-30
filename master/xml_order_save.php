<?php
require('includes/application_top.php');
	
//▼初期設定
$res = "err";
$top       = $_POST['top'];
$send_id   = $_POST['sendid'];
$o_group   = $_POST['ogroup'];


//▼データ取得
if(($_POST['top'] == "porder")AND($send_id)AND(isset($o_group))){

	//▼登録用配列
	$data_array = array(
		'm_plan_o_group' => $o_group,
		'date_update'    => 'now()'
	);
	
	//▼登録DB
	$db_table = TABLE_M_PLAN;
	$w_set    = "`m_plan_id`='".$send_id."' AND `state`='1'";
	tep_db_perform($db_table ,$data_array ,'update' ,$w_set);
	
	//▼情報の登録
	$res = 'ok';
}

$string = 'top:'.$top.'>>sendid:'.$send_id.'>ogroup:'.$o_group;
//write_log($string,'w');

echo $res;
?>