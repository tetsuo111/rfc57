<?php
require('includes/application_top.php');
	
//▼初期設定
$res     = "";
$top     = $_POST['top'];
$plan_id = $_POST['plan_id'];	//商品ID
$num     = $_POST['num'];		//選択個数
$cul_sum = 0;

//▼エラーチェック
$err     = false;

/*----- 計算要素を取得 -----*/
//▼費用配列を取得
$query = tep_db_query("
	SELECT
		`m_plan_item`    AS `item`,
		`m_plan_item_pay`AS `pay_item`
	FROM `".TABLE_M_PLAN."`
	WHERE `state`     = '1'
	AND   `m_plan_id` = '".tep_db_input($plan_id)."' 
");

if($a = tep_db_fetch_array($query)){
	$citem_ar = zJSToArry($a['item']);		//設定した費用項目
	$cpay_ar  = zJSToArry($a['pay_item']);	//合計計算用の費用項目
}else{
	$err = true;
}


/*----- 計算実行 -----*/
if(($err == false)AND($top == "culc")AND($num)){
	
	//▼合計計算用の費用別に計算
	foreach($cpay_ar AS $kp => $vppt){
		
		if($vppt == 'culc'){
			
			//▼初期設定
			$cal1 = new CulcItemAmount;

			$cal1->culcid = zGetCulcIDFromItem($kp);	//計算式番号
			$target       = $cal1->GetItemIDs();		//対象のIDを取得
			$cul_sum      = 0;
			
			//▼値を配分
			$cal1->zSetTarget($citem_ar);
			
			//▼個数処理
			$cal1->amount1 = $cal1->amount1 * $num; 
			$cal1->amount2 = $cal1->amount2 * $num;
			
			//▼金額計算
			$cul_sum+= $cal1->zCulcItemAmount();
			
		}else{
			
			//▼固定値の場合 は数値をそのまま
			$cul_sum+= $vppt;
		}
	}
	
	$res = $cul_sum;
}

$string = 'top:'.$top.'>>planid:'.$plan_id.'>>num:'.$num.'>>res:'.$res;
//write_log($string,'w');
echo $res;
?>