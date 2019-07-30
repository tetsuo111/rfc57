<?php

/*
$top       = $_POST['top'];
$order_id  = $_POST['sendid'];
$o_figure  = $_POST['dfigure'];
*/

//▼データ取得
if(($top == "done")AND($order_id)AND($o_figure)){
	
	//▼検索設定
	$w_set = "`user_order_id`='".tep_db_input($order_id)."' AND `state`='1'";
	
	//----- データ確認 -----//
	$query = tep_db_query("
		SELECT
			`user_order_id`          AS `id`,
			`position_id`,
			`user_id`,
			`rank_id`,
			`user_order_sort`        AS `sort`,
			`user_order_condition`   AS `condition`
		FROM  `".TABLE_USER_ORDER."`
		WHERE ".$w_set."
		AND   `user_order_condition` = '1'
	");
	
	if($a = tep_db_fetch_array($query)){
		
		//▼注文者データ
		$pos_id  = $a['position_id'];	//ポジションID
		$u_id    = $a['user_id'];		//会員ID
		$rank_id = $a['rank_id'];		//購入ランク
		
		
		//----- 注文完了 -----//
		//▼登録用配列
		$data_array = array(
			'user_order_date_done'   => 'now()',
			'user_order_condition'   => 'a',
			'user_order_date_figure' => $o_figure,
			'date_update'            => 'now()'
		);
		
		//▼データ更新
		tep_db_perform(TABLE_USER_ORDER ,$data_array ,'update' ,$w_set);
		
		
		//=======================
		//CIS対応
		//=======================
		//▼請求情報取得
		$query_ch = tep_db_query("
			SELECT
				SUM(`user_o_charge_r_amount`) AS `rsum`
			FROM `".TABLE_USER_O_CHARGE."`
			WHERE `state`         = '1'
			AND   `user_order_id` = '".tep_db_input($order_id)."'
		");
		
		$r = tep_db_fetch_array($query_ch);
		
		
		//▼計算基準日追加
		$up_odr0_ar = array(
			'editdate' => 'now()',					//編集日
			'editflag' => '-1',						//編集
			'empid'    => $_COOKIE['master_id'],	//編集者
			'recdate'  => 'now()',					//入金日
			'recmoney' => $r['rsum'],				//入金金額
			'calcdate' => $o_figure					//計算日
		);
		
		$w_odr_set = "`orderid`='".tep_db_input($order_id)."'";
		tep_db_perform(TABLE_ODR00000 ,$up_odr0_ar,'update' ,$w_odr_set);
		
		
		//----- 初回入金 -----//
		if($a['sort'] == 'a'){
			
			//=======================
			//GTW対応
			//=======================
			//▼ステータス更新
			zUserWCStatusUpdate('user_wc_status_buy','1',$u_id);
			
			
			//=======================
			//CIS対応
			//=======================
			//▼会員区分
			$query = tep_db_query("
				SELECT
					`m_rank_bctype` AS `bctype`
				FROM  `".TABLE_M_RANK."`
				WHERE `m_rank_id` = '".tep_db_input($rank_id)."'
				AND   `state`     = '1'
			");
			
			if($b = tep_db_fetch_array($query)){
				
				//▼検索設定
				$up_m0_ar  = array('bctype' => $b['bctype']);
				$w_mem_set = "`memberid`='".tep_db_input($u_id)."'";
				
				//▼DB追加
				tep_db_perform(TABLE_MEM00000,$up_m0_ar,'update',$w_mem_set);
			}
			
		}else if($a['sort'] == 'b'){
			
		}
		
		
		//----- 自動登録対応 -----//
		require('mut_plan_add_auto.php');
		
		//▼情報の登録
		$res = 'ok';
	}
	
}


?>