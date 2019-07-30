<?php

//▼基準通貨
$base_cur = $cur_ar[0];

while ($o = tep_db_fetch_array($order_query)) {
	
	$odrid = $o['order_id'];
	$order_ar[$odrid]['order'] = $o;
	
	//▼引継ぎ用
	$j_order_ar[$odrid] = array(
		'oid'      => $odrid,
		'fsid'     => $o['login_id'],
		'uid'      => $o['user_id'],
		'uname'    => $o['name'],
		'oamt'     => $o['o_amount'],
		'dappli'   => $o['d_appli'],
		'ramt'     => $o['recmoney'],
		'dreceive' => $o['recdate'],
		'plan'     => json_decode($o['plans'],true)
	);
	
	//'item'     => json_decode($o['items'],true)
}


//----- 表示成形 -----//
foreach($order_ar AS $o => $o_data){
	
	//▼表示用データ
	$a = $o_data['order'];
	
	//▼注文状況
	if($a['o_condition'] == 'a'){
		$condition = '<span class="ok">確認済</span>';
		
	}else if($a['o_condition'] == 'c'){
		$condition = '<span class="alert">キャンセル</span>';
		
	}else{
		$condition = '入金待';
	}
	
	//▼顧客名
	$o_uname = ($a['name'])? $a['name']:'<span class="alert">未登録</span>';
	
	//▼メモ
	$c_remarks  = '';
	
	//▼入金処理
	$operation = '<button type="button" data-id="'.$a['order_id'].'" class="oprBack" onClick="ShowPop();">';
	$operation.= '返品処理';
	$operation.= '</button>';
	
	//▼注文種類
	$o_type     = ($a['o_sort'] == 'a')? '<span class="alert">'.$OrderType[$a['o_sort']].'</span>': $OrderType[$a['o_sort']];

	//▼注文金額
	$o_amt = number_format($a['o_amount']);
	
	//▼ビットコイン対策
	$order_list_tr.='<tr>';
	$order_list_tr.='<td>'.$a['order_id'].'</td>';
	$order_list_tr.='<td>'.$a['user_id'].'</td>';
	$order_list_tr.='<td>'.$o_uname.'</td>';
	$order_list_tr.='<td>'.$a['login_id'].'</td>';
	$order_list_tr.='<td>'.$o_type.'</td>';
	$order_list_tr.='<td>'.$a['d_appli'].'</td>';
	$order_list_tr.='<td class="num_in">'.$o_amt.' '.$base_currency.'</td>';
	$order_list_tr.='<td>'.$a['d_limit'].'</td>';
	$order_list_tr.='<td class="num_in">'.(($o_paid_amt)? $o_paid_amt:'-').'</td>';
	$order_list_tr.='<td>'.(($a['d_done'])? $a['d_done']:'-').'</td>';
	$order_list_tr.='<td>'.(($a['d_figure'])? $a['d_figure']:'-').'</td>';
	$order_list_tr.='<td>'.(($a['d_sendoff1'])? $a['d_sendoff1']:'-').'</td>';
	$order_list_tr.='<td>'.$c_remarks.'</td>';
	$order_list_tr.='<td>'.$condition.'</td>';
	$order_list_tr.='<td>'.$operation.'</td>';
	$order_list_tr.='</tr>';
}

?>