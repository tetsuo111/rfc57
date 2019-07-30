<?php
require('includes/application_top.php');

//▼初期設定
$res           = "nodata";

$top       = $_POST['top'];
$plan_id   = $_POST['planid'];		//商品ID
$sort      = $_POST['oSort'];		//注文種類
$order_num = $_POST['oNum'];		//注文個数
$cart_id   = $_POST['cartid'];		//カートID


/*----- エラーチェック -----*/
if(($top == "cart")AND($plan_id)AND($order_num)){
	
	//▼共通設定
	$db_table = TABLE_USER_O_CART;		//登録DB設定
	$t_ai_id  = 'user_o_cart_ai_id';		//自動登録ID
	$t_id     = 'user_o_cart_id';		//テーブルID
	
	
	//----- 登録確認 -----//
	$err = false;
	
	//▼注文個数
	if(!$order_num) {$err=true;}
	
	if($err){
		$res = 'err';
		
	}else{
		
		$user_id     = $_COOKIE['user_id'];
		$position_id = $_COOKIE['position_id'];
		
		//▼登録情報
		$data_array = array(
			'position_id'           => $position_id,
			'user_id'               => $user_id,
			'plan_id'               => $plan_id,
			'user_o_cart_number'    => $order_num,
			'user_o_cart_sort'      => $sort,
			'user_o_cart_date_in'   => 'now()',
			'user_o_cart_condition' => '1',
			'date_create'           => 'now()',
			'state'                 => '1'
		);
		
		
		//----- カート登録確認 -----//
		//▼商品確認
		$query_plan = tep_db_query("
			SELECT 
				`".$t_id."`
			FROM  `".$db_table."`
			WHERE `state`   = '1'
			AND   `plan_id`     = '".tep_db_input($plan_id)."'
			AND   `position_id` = '".tep_db_input($position_id)."'
			AND   `user_o_cart_condition` = '1'
		");
		
		//▼登録確認
		if($c = tep_db_fetch_array($query_plan)){
			//カートID取得
			$c_cart_id = $c[$t_id];
		}
		
		
		//▼登録チェック
		if($cart_id){
			
			$query_check = tep_db_query("
				SELECT 
					`".$t_id."`
				FROM  `".$db_table."`
				WHERE `state` = '1'
				AND   `".$t_id."`   = '".tep_db_input($cart_id)."'
				AND   `position_id` = '".tep_db_input($position_id)."'
				AND   `user_o_cart_condition` = '1'
			");
			
			if (tep_db_num_rows($query_check)){
				$c_cart_id = $cart_id;
			}
		}
		
		
		//----- 登録実行 -----//
		if($c_cart_id){
			//更新登録
			zDBUpdate($db_table,$data_array,$c_cart_id);
			
		}else{
			//新規登録
			zDBNewUniqueID($db_table,$data_array,$t_ai_id,$t_id);
		}
		
		$res = 'ok';
	}
}

echo $res;
?>