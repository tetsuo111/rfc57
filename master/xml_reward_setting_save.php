<?php
require('includes/application_top.php');
	
//▼初期設定
$res = "err";
$top    = $_POST['top'];
$r_type = $_POST['rtype'];
$sendid = $_POST['sendid'];
$setval = $_POST['setval'];


//▼データ取得
if(($_POST['top'] == "rewset")AND($r_type)AND($sendid)AND($setval)){
	
	//▼値の設定
	$r_val = ($setval == 'a')? $setval: 'null';
	
	//▼変更パラメータ
	if($r_type == 'uni'){
		$r_param = 'm_point_r_uni';
	}else if($r_type == 'point'){
		$r_param = 'm_point_r_point';
	}
	
	//▼登録用配列
	$data_array = array(
		$r_param      => $r_val,
		'date_update' => 'now()'
	);
	
	
	//▼DB更新
	$db_table = TABLE_M_POINT;
	$w_set    = "`m_point_id`='".tep_db_input($sendid)."' AND `state`='1'";
	tep_db_perform($db_table ,$data_array ,'update' ,$w_set);
	
	//▼情報の登録
	$res = 'ok';
}

$string = 'top:'.$top.'>>sendids:'.$sendid.'>rtype:'.$r_type;
//write_log($string,'w');

echo $res;
?>