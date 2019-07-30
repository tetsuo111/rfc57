<?php

if(count($cur_ar) == 1){
	
	
	//▼会員区分を取得
	$query =  tep_db_query("
		SELECT
			`bctype` AS `bc`
		FROM  `".TABLE_MEM00000."`
		WHERE `memberid` = ".tep_db_input($user_id)."
	");
	
	$pbct = tep_db_fetch_array($query);
	
	//▼支払制限追加
	require('inc_payment_restriction.php');
	
	$pay_ar_edit = $pay_way_ar[0];
	foreach($pay_way_ar[0] AS $k => $v){
		
		//注文種類＞bctype＞支払方法＞支払通貨
		$restid = $js_sort.'_'.$pbct['bc'].'_'.$k.'_0';
		
		//制限対象の支払方法を削除
		if($PayRestrictArray[$restid]){
			unset($pay_ar_edit[$k]);
		}
	}
	
	//▼通貨別支払方法
	foreach($pay_ar_edit AS $k => $v){
		$on_limit = ($v == '振込み')? 1:0;
		$selIn.= '<p><input type="radio" class="i_radio" onchange="pWSelectCheck();" name="pay[0]" on-limit='.$on_limit.' value="'.$k.'"> '.$v.'</p>';
	}
	
	$ppww.= '<tr '.$cl.'>';
	$ppww.= '<td>'.$sum_ar[0]['sum'].' '.$base_cur.'の支払</td>';
	$ppww.= '<td>'.$selIn.'</td>';
	$ppww.= '</tr>';

	
	//事前に登録
	$in_way = '<div id="pWay">';
	$in_way.= '<table class="notable">'.$ppww.'</table>';
	$in_way.= '</div>';
	
	
}else{
	
	//選択時に入力
	$in_way = '<div id="pWay"></div>';
}

?>