<?php
foreach($odr_fee_ar AS $fees){
	
	//▼手数料毎に集計
	foreach($fees AS $dtf){
		
		//▼登録用金額
		$b0 = $dtf['amt'];													//計算金額
		$b1 = floor($dtf['amt'] / $rate_ar[$dtf['currencyid']]['rate']);	//基準金額
		
		//1以下は切り捨て
		//▼代引き手数料
		if(
			($dtf['name'] == '代引手数料')
			OR($dtf['name'] == '送料')
		){
			$fee['daib']['org'] += $b0;
			$fee['daib']['base']+= $b1;
		}
		
		//▼出荷事務手数料
		if(
			($dtf['name'] == '出荷事務手数料')
			OR($dtf['name'] == '事務処理手数料')
		){
			$fee['syu']['org']  += $b0;
			$fee['syu']['base'] += $b1;
		}
		
		//▼消費税
		if($dtf['name'] == '消費税'){
			$fee['tax']['org']  += $b0;
			$fee['tax']['base'] += $b1;
		}
		
		//▼合計
		$fee['sum']['org']      += $b0;
		$fee['sum']['base']     += $b1;
	}
}
?>