<?php

//▼個数選択
foreach($cart_ar AS $k => $v){
	
	$planid = $v['plan_id'];
	
	//▼商品単価
	$tplan = $plan_ar[$planid];
	$sum_in = '';
	
	foreach($tplan AS $ka => $va){
		//通貨毎に表示
		$sum_in.= '<p>'.$va['plan_sum'].'<span class="spc10_l">'.$cur_ar[$va['cur_id']].'</span></p>';
	}
	
	
	//▼個数選択
	if($v['l_piece']){
		//選択個数を生成
		for($i=1;$i<$v['l_piece']+1;$i++){$sarray[$i] = $i;}
		
	}else{
		$sarray = $LimitNum;
	}
	
	$sel_num = zSelectCart($sarray,$v['num'],'num['.$k.']');
	
	//▼削除
	$operation = '<input type="button" class="btn ctDel" value="削除" id-data="'.$v['id'].'">';
	
	$list_in.= '<tr>';
	$list_in.= '<th>'.$planid.'</td>';
	$list_in.= '<td>'.$v['name'].'</td>';
	$list_in.= '<td>'.$sum_in.'</td>';
	$list_in.= '<td>'.$sel_num.'</td>';
	$list_in.= '<td>'.$operation.'</td>';
	$list_in.= '</tr>';
}

?>