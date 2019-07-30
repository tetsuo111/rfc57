<?php
require('includes/application_top.php');

//▼初期設定
$res           = "nodata";

$top       = $_POST['top'];
$cart_id   = $_POST['cartid'];		//カートID


/*----- エラーチェック -----*/
if(($top == "cartdel")AND($cart_id)){
	
		//▼削除配列
		$del_array = array(
			'user_o_cart_date_out' => 'now()',
			'date_update'          => 'now()',
			'state'                => 'c'		//キャンセルはc 返品はb
		);
		
		
		//▼検索設定
		$w_set = "`user_o_cart_id`='".tep_db_input($cart_id)."'";
		$w_set.= "AND `user_o_cart_condition`='1'";
		$w_set.= "AND `state`='1'";
		
		//▼削除実行
		tep_db_perform(TABLE_USER_O_CART,$del_array,'update',$w_set);
		
		
		$res = 'ok';
}

echo $res;
?>