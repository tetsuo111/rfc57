<?php

//▼購入商品一覧
$query = tep_db_query("
	SELECT
		`plan_id` AS `p_id`
	FROM  `".TABLE_USER_O_CART."`
	WHERE `order_id` = '".tep_db_input($order_id)."'
	AND   `state`     = '1'
	AND   `user_o_cart_condition` = 'a'
");

while($b = tep_db_fetch_array($query)){
	$for_get_add.= (($for_get_add)? ",'":"'").$b['p_id']."'";
}

if($for_get_add){
	
	//▼自動登録確認
	$query_add = tep_db_query("
		SELECT 
			`m_plan_id`          AS `add_pid`,
			`m_plan_add_amount`  AS `add_amt`
		FROM  `".TABLE_M_PLAN_ADD."`
		WHERE `state` = '1'
		AND   `m_plan_add_plan_id` IN(".$for_get_add.")
		ORDER BY `m_plan_id` ASC
	");
	
	while($b = tep_db_fetch_array($query_add)){
		$tmp_add[] = $b;
	}
	
	if($tmp_add){
		
		for($i=0;$i<3;$i++){
			
			if($tmp_add[$i]){
				//▼データ取得
				$dt = $tmp_add[$i];		//自動追加データ
				$mi = $i+1;				//追加番号
				
				//▼asitem00000用
				$as_add_ar = array(
					'memberid'       => $u_id,
					'itemid'         => $dt['add_pid'],
					'qty'            => $dt['add_amt'],
					'input_datetime' => 'now()',
					'flg'            => '1'
				);
				
				//▼定期購入追加
				tep_db_perform(TABLE_ASITEM00000,$as_add_ar);
				
				//▼mem登録用
				$m0_ar['asitem00'.$mi]   = $dt['add_pid'];
				$m0_ar['asitemqty00'.$mi] = $dt['add_amt'];
			}
		}
		
		//▼m0データ更新
		$m0_ar['editdate']  = 'now()';								//変更日
		$m0_ar['asregdate'] = 'now()';								//登録日
		$w_set_m0 = "`memberid`='".tep_db_input($u_id)."'";			//検索設定
		tep_db_perform(TABLE_MEM00000,$m0_ar,'update',$w_set_m0);	//データ登録
	}
}
?>