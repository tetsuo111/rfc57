<?php
require('includes/application_top.php');
	
//▼初期設定
$res  = "err";
$id   = $_POST['id'];
$able = $_POST['able'];

//▼データ取得
if($id && $able){
	
	//▼変換用
	$ch_ar = array('a'=>'b','b'=>'a');
	
	//▼登録配列
	$up_ar = array(
		'm_plan_condition' => $ch_ar[$able],
		'date_update'      => 'now()'
	);
	
	//▼変更用
	$w_set = "`m_plan_id` = '".tep_db_input($id)."' AND `state`=1";
	tep_db_perform(TABLE_M_PLAN,$up_ar,'update',$w_set);
	
	$res = 'ok';
}

//▼結果を格納
echo $res;
?>