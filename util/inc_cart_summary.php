<?php

//----- 商品集計 -----//
//▼一覧作成
foreach($cart_ar AS $k => $v){
	
	//▼商品情報
	$planid       = $v['plan_id'];					//商品ID
	$plan_base_a  = '';								//商品単体合計
	$plan_base_b  = '';								//商品個数合計
	$sel_num      = $p_num[$k];						//選択個数
	$ttl_resource = $v['resource'] * $sel_num;		//個数コミッション原資
	$pl_resource += $ttl_resource;					//注文コミッション合計
	
	//▼獲得ランク
	if($v['sort'] == 'a'){
		$grank_id = $v['grankid'];
		$o_sort   = $v['sort'];
		
	}else if($v['sort'] == 'c'){
		$o_sort = $v['sort'];
	}
	
	//▼データ追加
	$cart_ar[$k]['num']         = $sel_num;			//注文個数
	$cart_ar[$k]['sumresource'] = $ttl_resource;	//商品別コミッション原資
	
	//▼商品単価
	$tplan      = $plan_ar[$planid];		//「計算」商品別　＞　通貨別配列
	$sum_in     = '';						//「表示」商品価格
	$total_in   = '';						//「表示」商品合計
	
	
	//▼品目の合計を計算
	//設定通貨毎に金額を計算
	foreach($tplan AS $curid => $va){
		
		//--- 品目別集計 ---//
		//▼各金額計算
		$a  = $va['plan_sum'];						//「通貨別」商品の品目合計
		$b  = $va['plan_sum'] * $sel_num;			//「通貨別」合計金額　商品価格 *　個数
		$c  = $cur_ar[$curid];						//通貨名
		$d  = $rate_ar[$curid]['rate'];				//通貨レート
		$a1 = $a / $d;								//単体基準
		$b1 = $b / $d;								//個数基準
		
		//▼基準通貨価格
		$plan_base_a+= $a1;		//商品単体合計
		$plan_base_b+= $b1;		//商品個数合計
		
		
		//▼合計配列　＞ odr00000に対応
		$sum_ar[$va['cur_id']]['sum'] += $b;		//個数合計
		$sum_ar[$va['cur_id']]['cur']  = $c;		//通貨名
		$sum_ar[$va['cur_id']]['rate'] = $d;		//通貨レート
		$sum_ar[$va['cur_id']]['base']+= $b1;		//基準通貨価格合計
		
		
		//▼商品別配列　＞ odr00001に対応
		//通貨別に集計
		if(!$cart_ar[$k]['price'][$curid]){
			
			//単体通常
			$cart_ar[$k]['price'][$curid] = array(
				'curid'  => $curid,
				'name'   => $c,
				'amt_a'  => $a,
				'base_a' => $a1,
				'amt_b'  => $b,
				'base_b' => $b1
			);
		}else{
			
			$cart_ar[$k][$curid]['price']['amt_a'] += $a;		//単体通常
			$cart_ar[$k][$curid]['price']['base_a']+= $a1;		//単体基準
			$cart_ar[$k][$curid]['price']['amt_b'] += $b;		//個数通常
			$cart_ar[$k][$curid]['price']['base_b']+= $b1;		//個数基準
		}
	}
	
	
	//▼商品手数料の合計を計算
	$query =  tep_db_query("
		SELECT
			`m_cost_plan_id` AS `plan_id`,
			`m_cost_name`    AS `name`,
			`m_cost_culc_id` AS `culc_id`
		FROM  `".TABLE_M_COST."`
		WHERE `state` = '1'
		AND   `m_cost_plan_id` = ".tep_db_input($planid)."
	");
	
	
	//▼初期化
	$for_get_culc = '';
	$cost_ar      = '';
	
	while($cdt = tep_db_fetch_array($query)) {
		$cost_ar[$cdt['culc_id']] = $cdt;
		$for_get_culc.= (($for_get_culc)? ",'":"'").$cdt['culc_id']."'";
	}
	
	//▼商品手数料の設定があれば計算
	//商品単体で計算　＞　個数分別途加算
	if($cost_ar){
		
		//▼計算内容を取得
		$query = tep_db_query("
			SELECT 
				`m_culc_id` AS `id`
			FROM  `".TABLE_M_CULC."`
			WHERE `state` = '1'
			AND   `m_culc_id` IN (".$for_get_culc.")
		");
		
		while($cu = tep_db_fetch_array($query)){
			$culc_ar[] = $cu['id'];
		}
	
		//▼手数料計算用
		$keep_total = $plan_base_a;
		
		//▼設定手数料毎に計算
		//商品手数料はすべて基準通貨で計算
		foreach($culc_ar AS $culcid){
			
			//▼通貨設定
			$b_cur_id = 0;								//基準通貨
			$b_rate   = $rate_ar[$b_cur_id]['rate'];	//通貨レート
			$b_name   = $cur_ar[$b_cur_id];				//通貨名
			
			
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
			$tsum_a  = $clc->pStartCulc();				//単体通常
			$tsum_b  = $tsum_a * $sel_num;				//個数通常
			$tsum_a1 = $tsum_a / $b_rate;				//単体基準
			$tsum_b1 = $tsum_b / $b_rate;				//個数基準
			
			
			//▼合計配列　＞ odr00000に対応
			//基準通貨に追加
			$sum_ar[$b_cur_id]['sum'] += $tsum_b;		//個数通常
			$sum_ar[$b_cur_id]['base']+= $tsum_b1;		//個数基準
			
			
			//▼商品別配列　＞ odr00001に対応
			//基準通貨に追加
			if(!$cart_ar[$k]['price'][$b_cur_id]){
				
				//単体通常
				$cart_ar[$k]['price'][$b_cur_id] = array(
					'curid'  => $b_cur_id,
					'name'   => $b_name,
					'amt_a'  => $tsum_a,
					'base_a' => $tsum_a1,
					'amt_b'  => $tsum_b,
					'base_b' => $tsum_b1
				);
				
			}else{
				
				$cart_ar[$k][$b_cur_id]['price']['amt'] += $tsum_a;		//単体通常
				$cart_ar[$k][$b_cur_id]['price']['base']+= $tsum_a1;	//単体基準
				$cart_ar[$k][$b_cur_id]['price']['amt'] += $tsum_b;		//個数通常
				$cart_ar[$k][$b_cur_id]['price']['base']+= $tsum_b1;	//個数基準
			}
			
			
			//▼商品合計に加算
			$plan_base_a+= $tsum_a;		//商品単体合計
			$plan_base_b+= $tsum_b;		//商品個数合計
			
			
			//▼詳細登録用
			$o_detail['culc'][$planid][$culcid] = array(
				'id'      => $culcid,							//計算ID
				'name'    => $cost_ar[$culcid]['name'],			//計算名
				'amt'     => $tsum_a,							//単体通常
				'curname' => $base_cur							//通貨名
			);
		}
	}
	
	
	//▼最終結果を追加
	$cart_ar[$k]['base_a'] = $plan_base_a;						//単体基準
	$cart_ar[$k]['tax_a']  = zCulcTax($plan_base_a,$tax_rate);	//単体基準消費税
	$cart_ar[$k]['base_b'] = $plan_base_b;						//単体個数
	$cart_ar[$k]['tax_b']  = zCulcTax($plan_base_b,$tax_rate);	//単体個数消費税
}

?>