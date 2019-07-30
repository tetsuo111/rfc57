<?php
require('includes/application_top.php');
	
//▼初期設定
$res = "err";
$top       = $_POST['top'];
$send_ids  = $_POST['sendids'];
$o_group   = $_POST['ogroup'];


//▼データ取得
if(($_POST['top'] == "porder")AND($send_ids)AND(isset($o_group))){
	
	//▼値を取得
	$o_first       = ($_POST['o_first'])?       $_POST['o_first']       : 'null';
	$o_after       = ($_POST['o_after'])?       $_POST['o_after']       : 'null';
	$o_limit_times = ($_POST['o_limit_times'])? $_POST['o_limit_times'] : 'null';
	$o_limit_piece = ($_POST['o_limit_piece'])? $_POST['o_limit_piece'] : 'null';
	$o_autoship    = ($_POST['o_autoship'])?    $_POST['o_autoship']    : 'null';
	$o_must        = ($_POST['o_must'])?        $_POST['o_must']        : 'null';
	$o_caution     = ($_POST['o_caution'])?     $_POST['o_caution']     : 'null';
	
	//▼登録用配列
	$data_array = array(
		'm_plan_o_first'       => $o_first,
		'm_plan_o_after'       => $o_after,
		'm_plan_o_limit_times' => $o_limit_times,
		'm_plan_o_limit_piece' => $o_limit_piece,
		'm_plan_o_autoship'    => $o_autoship,
		'm_plan_o_must'        => $o_must,
		'm_plan_o_caution'     => $o_caution,
		'date_update'          => 'now()'
	);
	
	
	//▼DB登録
	$db_table = TABLE_M_PLAN;
	foreach($send_ids AS $v){
		$w_set    = "`m_plan_id`='".tep_db_input($v)."' AND `state`='1'";
		tep_db_perform($db_table ,$data_array ,'update' ,$w_set);
	}
	
	//▼情報の登録
	$res = 'ok';
}

$string = 'top:'.$top.'>>sendids:'.$send_id.'>ogroup:'.$o_group;
write_log($string,'w');

echo $res;
?>