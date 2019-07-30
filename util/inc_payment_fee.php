<?php

//▼支払データを復元
$pwsel_ar = json_decode(str_replace('\\','' ,$pay_js),true);
$ok_fee   = false;		//手数料検出


//▼支払方法を検出 
foreach($pwsel_ar AS $kcur_id => $pw){
	
	//▼支払手数料
	$query =  tep_db_query("
		SELECT
			`payment_id`,
			`culc_id`,
			`m_payment_fee_name` AS `name`
		FROM  `".TABLE_M_PAYMENT_FEE."`
		WHERE `state` = '1'
		AND   `payment_id` = '".tep_db_input($pw['payid'])."'
	");
	
	$tmp = '';
	while($a = tep_db_fetch_array($query)) {
		$tmp[] = $a;
	}
	
	
	if($tmp){
		//▼全体に追加
		$pwsel_ar[$kcur_id]['pfee'] = $tmp;
		$ok_fee = true;
	}
}


//▼品目一覧
if($ok_fee){
	$query =  tep_db_query("
		SELECT
			`m_item_id`          AS `id`,
			`m_item_name`        AS `name`,
			`m_item_fixamount`   AS `famt`,
			`m_item_currency_id` AS `cur_id`
		FROM  `".TABLE_M_ITEM."`
		WHERE `state` = '1'
		ORDER BY `m_item_id` ASC
	");

	while($a = tep_db_fetch_array($query)) {
		$item_ar[$a['id']] = $a;
	}
}


//▼支払手数料毎に計算
foreach($pwsel_ar AS $kcur_id => $dt){
	
	$string.="dt:".json_encode($dt)."\n";
	
	//▼計算設定
	if($dt['pfee']){
		
		//▼合計金額
		$itotal = $dt['amt'];
		$c_ar   = $dt['pfee'];
		$t_ar   = '';
		
		
		//▼設定毎に手数料を計算
		foreach($c_ar AS $cdt){
			
			//▼初期設定
			$cid   = $cdt['culc_id'];	//計算ID
			$cname = $cdt['name'];		//手数料名
			
			//--- 手数料計算 ---//
			//▼計算設定開始
			$clc = new ClucFeeAmount($cid);
			$tgt = $clc->pGetItemId();		//計算品目を取得
			
			//▼取得id
			$id1 = $tgt['target1_id'];
			$id2 = $tgt['target2_id'];
			
			//▼金額を設定
			$clc->amount1 = ($id1)? $item_ar[$id1]:$itotal;
			$clc->amount2 = ($id2)? $item_ar[$id2]:0;
			
			//▼計算実行
			$tsum = $clc->pStartCulc();
			
			if($tsum){
				$t_ar[] = array('culcid'=> $cid,'name'=>$cname,'amt'=>$tsum,'currencyid'=>$kcur_id);
			}
		}
		
		//▼結果を格納
		if($t_ar){
			$fee_ar[] = $t_ar;
		}
	}
}
?>