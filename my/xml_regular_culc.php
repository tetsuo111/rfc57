<?php
require('includes/application_top.php');

//▼初期設定
$res      = 'err';
$cul_sum  = 0;

//▼データ取得
$top      = $_POST['top'];
$rg_state = $_POST['rg_state'];			//定期購入自体　a：しない　b：する
$deliver  = $_POST['deliverytype'];		//配送間隔　1：毎月 2：2ヵ月 3：3ヵ月
$memtype  = $_POST['membertype'];		//支払方法　5クレジットカード
$num      = $_POST['num'];				//選択個数
$sort     = $_POST['sort'];				//注文種類
$bcty     = $_POST['bcty'];				//会員区分

//▼エラーチェック
$err = false;

if(!$rg_state){
	$err = true;
	
}else if($rg_state == 'b'){
	
	//▼定期購入を申し込む場合
	if(!$deliver){$err = true;}
	if(!$memtype){$err = true;}
	if(!$num)    {$err = true;}
}


//----- 計算実行 -----//
if(($err == false)AND($top == "rgedit")AND($num)){
	
	//----- 商品金額 -----//
	//▼商品情報計算
	$plan_ar   = zGetTypePlanData($bcty,$sort,$num);
	$sum_total = 0;
	
	foreach($plan_ar AS $k => $v){
		
		//▼合計金額
		$sum_total+= $v['total'];
		
		//▼費用取得用
		$for_get_plan.= (($for_get_plan)? ",'":"'").$k."'";
	}
	
	
	//----- 手数料 -----//
	//▼通貨レート
	$rate_ar  = zRateList();
	
	//▼支払手数料
	$query =  tep_db_query("
		SELECT
			`pf`.`culc_id`,
			`pf`.`m_payment_fee_name` AS `cname`
		FROM      `".TABLE_M_PAYMENT."`     `p0`
		LEFT JOIN `".TABLE_M_PAYMENT_FEE."` `pf` ON `pf`.`payment_id` = `p0`.`m_payment_id`
		WHERE `p0`.`m_payment_code` = '".tep_db_input($memtype)."'
		AND   `p0`.`state` = '1'
		AND   `pf`.`state` = '1'
	");
	
	while($a = tep_db_fetch_array($query)){
		//▼計算用配列
		$culc_ar[] = $a;
	}
	
	//▼商品別、品目別金額
	$query = tep_db_query("
		SELECT
			`pi`.`m_plan_id` AS `id`,
			`pi`.`m_item_id` AS `itemid`,
			SUM(`mi`.`m_item_fixamount` * `pi`.`m_plan_item_num`) AS `sum`
		FROM      `".TABLE_M_PLAN_ITEM."` `pi`
		LEFT JOIN `".TABLE_M_ITEM."`      `mi` ON `mi`.`m_item_id` = `pi`.`m_item_id`
		WHERE `pi`.`state` = '1'
		AND   `mi`.`state` = '1'
		AND   `pi`.`m_plan_id` IN(".$for_get_plan.")
		GROUP BY `pi`.`m_plan_id`,`pi`.`m_item_id`
	");
	
	while($a = tep_db_fetch_array($query)) {
		//品目別金額　品目 * 選択個数
		$item_ar[$a['itemid']] = $a['sum'] * $num[$a['id']];
	}
	
	
	//▼手数料計算
	$keep_total = $sum_total;		//計算用
	
	//▼計算実行
	//設定されている費用毎に計算
	foreach($culc_ar AS $cdt){
		
		//▼計算データ
		$culcid = $cdt['culc_id'];
		$cname  = $cdt['cname'];
		
		//▼計算設定開始
		$clc = new ClucFeeAmount($culcid);
		$tgt = $clc->pGetItemId();					//計算品目を取得
		
		//▼取得id
		$id1 = $tgt['target1_id'];
		$id2 = $tgt['target2_id'];
		
		//▼金額を設定
		$clc->amount1 = ($id1)? $item_ar[$id1]:$keep_total;
		$clc->amount2 = ($id2)? $item_ar[$id2]:0;
		
		//▼手数料計算
		$tsum    = $clc->pStartCulc();
		
		if($tsum){
			
			//▼費用振分け
			$t_ar[] = array('culcid'=> $culcid,'name'=>$cname,'amt'=>$tsum,'currencyid'=>0);
		}
	}
	
	
	//▼手数料を追加
	$fee_ar[]     = $t_ar;
	$odr_fee_ar = $fee_ar;
	
	//▼手数料振り分け
	require('../util/inc_cart_prc_fee.php');
	
	//▼合計金額
	$sum_total+= $fee['syu']['base']+$fee['daib']['base'];
	
	//▼結果を追加
	$tmp_ar['plan'] = $plan_ar;					//商品詳細
	$tmp_ar['syu']  = $fee['syu']['base'];		//出荷手数料
	$tmp_ar['daib'] = $fee['daib']['base'];		//代引き手数料
	$tmp_ar['sttl'] = $sum_total;				//合計金額
	
	$res = 'ok';
}

$res_ar['state'] = $res;
$res_ar['adata'] = $tmp_ar;

/*
function lzzString($ar){
	foreach($ar AS $k => $v){
		
		if(is_array($v)){
			$string.= lzzString($v);
		}else{
			$string.= $k.'>'.$v."\n";
		}
	}
	return $string;
}

$string = 'top:'.$top."\n";
$string.= 'state:'.$rg_state."\n";
$string.= 'deliver:'.$deliver."\n";
$string.= 'memtype:'.$memtype."\n";
$string.= 'sort:'.$sort."\n";
$string.= 'bcty:'.$bcty."\n";
$string.= "-----------------\n";
$string.= lzzString($num);
$string.= "-----------------\n";
*/
$string.= json_encode($fee);

write_log($string,'w');


echo json_encode($res_ar);
?>