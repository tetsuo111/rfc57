<?php

//----- エラーチェック -----//
$err = false;

//★表示名
if(!$p_id)  {$err = true; $err_text.= '<span class="alert">商品番号を入力してください</span>';}
if(!$p_name){$err = true; $err_text.= '<span class="alert">商品名を入力してください</span>';}

$ok_itm = false;
foreach($p_item AS $v){if($v > 0){$ok_itm = true;}}
if(!$ok_itm){$err = true; $err_text.= '<span class="alert">購入品目を選択してください</span>';}

if(!((!$p_add_plan_id && !$p_add_amount)OR($p_add_plan_id && $p_add_amount))){
	$err = true;
	$err_text.= '<span class="alert">自動追加の設定が正しくありません</span>';
}


//▼品目登録用
function lzPItem($ar,$plan_id,$update=''){
	
	//▼初期設定
	$db_table = TABLE_M_PLAN_ITEM;
	
	if($plan_id){
		
		//まとめて更新
		if($update == 'update'){
			$old_ar = array('date_update'=>'now()','state'=>'y');
			$w_set  = "`m_plan_id`='".tep_db_input($plan_id)."' AND `state`=1";
			
			//更新
			tep_db_perform($db_table,$old_ar,'update',$w_set);
		}
		
		
		//新たに登録
		foreach($ar AS $vrow){
			$data_array = array(
				'm_plan_id'       => $plan_id,
				'm_item_id'       => $vrow['id'],
				'm_plan_item_num' => $vrow['num'],
				'date_create'     => 'now()',
				'state'           => '1'
			);
			
			tep_db_perform($db_table,$data_array);
		}
		
		$res = true;
	}else{
		$res = false;
	}
	
	return $res;
}


//▼ポイント登録用
function lzPPoint($ar,$plan_id,$update=''){
	
	//▼初期設定
	$db_table = TABLE_M_PLAN_POINT;
	
	if($plan_id){
		
		//▼まとめて更新
		if($update == 'update'){
			//登録設定
			$old_ar = array('date_update'=>'now()','state'=>'y');
			$w_set  = "`m_plan_id`='".tep_db_input($plan_id)."' AND `state`=1";
			
			//更新
			tep_db_perform($db_table,$old_ar,'update',$w_set);
		}
		
		
		//▼新たに登録
		foreach($ar AS $vrow){
			$data_array = array(
				'm_plan_id'           => $plan_id,
				'm_point_id'          => $vrow['id'],
				'm_plan_point_amount' => $vrow['amt'],
				'date_create'         => 'now()',
				'state'               => '1'
			);
			
			tep_db_perform($db_table,$data_array);
		}
		
		$res = true;
	}else{
		$res = false;
	}
	
	return $res;
}


//▼表示設定
if(($err == false)AND(!$_POST['act_cancel'])){    //エラーなし
	
	//----- 初期設定 -----//
	//★
	$db_table = TABLE_M_PLAN;			//登録DB設定
	$t_id    = 'm_plan_id';				//テーブルID
	
	//▼CIS用
	$cis_table = TABLE_ITEM00000;
	$cis_id    = 'itemid';
	
	//▼登録チェック
	$query_check = tep_db_query("
		SELECT 
			`".$t_id."`
		FROM  `".$db_table."`
		WHERE `state` = '1'
		AND   `".$t_id."` = '".tep_db_input($m_plan_id)."'
	");
	
	//----- 登録用配列 -----//
	//▼データ計算用
	//▼詳細登録用
	$plan_total = 0;
	foreach($p_item AS $k => $v){
		if($v > 0){
			$p_item_ar[$k] = array('id'=>$k,'num'=>$v);
			
			//▼cis合計金額計算用 単価 * 個数
			$plan_total+= $item_ar[$k]['famt'] * $v; 
		}
	}
	
	//▼ポイント登録
	if($p_point){
		foreach($p_point AS $k => $v){
			if($v > 0){$p_point_ar[] = array('id'=>$k,'amt'=>$v);}
		}
	}
	
	//▼情報登録
	$sql_data_array = array(
		'm_plan_id'            => $p_id,
		'm_plan_name'          => $p_name,
		'm_plan_limited_id'    => $p_limited_id==''?0:$p_limited_id,
		'm_plan_rank_id'       => $p_rank_id==''?0:$p_rank_id,
		'm_plan_grank_id'      => (($p_grank_id == '')? 'null':$p_grank_id),
		'm_plan_sort'          => $sort,
		'm_plan_sum_resource'  => (($p_sum_resource)? $p_sum_resource: 0),
		'm_plan_o_must'        => zSetNull($p_o_must),
		'm_plan_o_limit_times' => zSetNull($p_o_limit_times),
		'm_plan_o_limit_piece' => zSetNull($p_o_limit_piece),
		'm_plan_taxtype'       => $p_plan_taxtype,
		'm_plan_caution'       => $p_caution,
		'm_plan_condition'     => 'a',
		'date_create' => 'now()',
		'state'       => '1'
	);
	
	
	//----- CIS対応 -----//
	//▼検索設定
	$wcis_set = "`itemid` = '".tep_db_input($m_plan_id)."'";
	
	//▼CIS登録確認
	$check_cis = tep_db_query("
		SELECT 
			`itemid`
		FROM  `".$cis_table."`
		WHERE ".$wcis_set
	);
	
	$ch_cis = tep_db_num_rows($check_cis);
	
	
	//----- 自動登録確認 -----//
	$db_table_add = TABLE_M_PLAN_ADD;
	
	//▼自動登録確認
	$query_add = tep_db_query("
		SELECT 
			`m_plan_id`
		FROM  `".TABLE_M_PLAN_ADD."`
		WHERE `state` = '1'
		AND   `".$t_id."` = '".tep_db_input($p_id)."'
	");
	
	$add = tep_db_fetch_array($query_add);
	
	
	//----- DB登録 -----//
	//▼検索設定
	$w_set = "`".$t_id."`='".tep_db_input($m_plan_id)."' AND `state`='1'";
	
	if($_POST['act_del']){
		
		//▼GTW削除
		$del_array = array(
			'date_update' => 'now()',
			'state'       => 'z'
		);
		tep_db_perform($db_table,$del_array,'update',$w_set);
		
		
		//▼CIS削除
		if($ch_cis){
			
			//▼削除設定
			$del_cis = tep_db_query("DELETE FROM `".$cis_table."` ".$wcis_set);
			tep_db_fetch_array($del_cis);
		}
		
		
		//▼終了テキスト
		$end_text = '削除しました';

		
	}else{
		
		//▼GTW対応
		if ($b = tep_db_fetch_array($query_check)){
			$old_ar = array('date_update' => 'now()','state' => 'y');
			tep_db_perform($db_table,$old_ar,'update',$w_set);
			
			//更新登録
			tep_db_perform($db_table,$sql_data_array);
			
		}else{
			//新規登録
			$m_plan_id = tep_db_perform($db_table,$sql_data_array);
		}
		
		
		//----- 詳細登録 -----
		$update = ($b)? 'update':'';
		
		//▼品目
		lzPItem($p_item_ar,$p_id,$update);
		
		//▼ポイント
		if($p_point_ar){lzPPoint($p_point_ar,$p_id,$update);}
		
		
		//----- 手数料登録 -----
		//▼手数料登録
		if($culc){
			$db_fee   = TABLE_M_COST;
			$wfee_set = "`state` = '1' AND `m_cost_plan_id` = '".tep_db_input($m_plan_id)."'";
			
			//▼過去の設定を無効化
			$query_check = tep_db_query("
				SELECT 
					`m_cost_ai_id`
				FROM  `".$db_fee."`
				WHERE ".$wfee_set);
			
			if(tep_db_num_rows($query_check)){
				$old_ar = array('date_update' =>'now()','state'=>'z');
				tep_db_perform($db_fee,$old_ar,'update',$wfee_set);
			}
			
			//▼新規登録
			foreach($culc AS $cdt){
				
				$data_ar = array(
					'm_cost_plan_id' => $m_plan_id,
					'm_cost_name'    => $cdt['name'],
					'm_cost_culc_id' => $cdt['id'],
					'date_create'    => 'now()',
					'state'          => '1'
				);
				
				tep_db_perform($db_fee,$data_ar);
				
				//▼取得用
				$for_get_culc.= (($for_get_culc)? ",'":"'").$cdt['id']."'";
			}
			
			
			//--- 手数料計算 ---//
			/*
			計算まではしない
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
			
			
			//▼設定金額ごとに計算
			foreach($culc_ar AS $cid){
				
				//▼計算設定開始
				$clc = new ClucFeeAmount($cid);
				$tgt = $clc->pGetItemId();		//計算品目を取得
				
				//▼取得id
				$id1 = $tgt['target1_id'];
				$id2 = $tgt['target2_id'];
				
				//▼金額を設定
				$clc->amount1 = ($id1)? $item_ar[$id1]:$plan_total;
				$clc->amount2 = ($id2)? $item_ar[$id2]:0;
				
				//▼計算実行
				$res = $clc->pStartCulc();
				$tfee+= $res['amt'];
			}
			*/
		}
		
		/*====================
			追加登録
		====================*/
		//▼過去データ変更
		if($add){
			
			//▼検索設定
			$w_set_add = "`m_plan_id`='".tep_db_input($p_id)."' AND `state`='1'";
			
			if($p_add_plan_id && $p_add_amount){
				
				//▼データ更新
				$add_del_ar = array('date_update' => 'now()','state' => 'y');
				
			}else if(!$p_add_plan_id && !$p_add_amount){
				
				//▼データ削除
				$add_del_ar = array('date_update' => 'now()','state' => 'z');
			}
			
			//▼データ変更
			tep_db_perform($db_table_add,$add_del_ar,'update',$w_set_add);
		}
		
		//▼データ登録
		if($p_add_plan_id && $p_add_amount){
			
			//▼登録内容
			$add_data_ar = array(
				'm_plan_id'          => $p_id,					//設定されている商品ID
				'm_plan_add_plan_id' => $p_add_plan_id,			//追加する商品ID
				'm_plan_add_amount'  => $p_add_amount,			//追加する数量
				'm_plan_add_sort'    => 'c',					//追加先
				'date_create'        => 'now()',
				'state'              => '1'
			);
			
			//▼DB登録
			tep_db_perform($db_table_add,$add_data_ar);
		}
		
		
		/*====================
			CIS対応
		====================*/
		//▼登録データ
		$cis_data_ar = array(
			'itemid'       => $p_id,
			'itemname'     => $p_name,
			'fixedprice'   => $plan_total + $tfee,
			'point'        => $p_point_ar[0]['amt']==''?0:$p_point_ar[0]['amt'],
			'taxtype'      => $p_plan_taxtype
		);
		
		//▼データ登録
		if($ch_cis){
			//更新登録
			tep_db_perform($cis_table,$cis_data_ar,'update',$wcis_set);
			
		}else{
			//新規登録
			tep_db_perform($cis_table,$cis_data_ar);
		}
		
		//▼終了テキスト
		$end_text = '登録しました';
	}
	
	//▼終了設定
	$end = 'end';
}
?>