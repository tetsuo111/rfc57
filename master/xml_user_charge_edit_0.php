<?php
require('includes/application_top.php');

//▼初期設定
$res = "err";
$top            = $_POST['top'];
$charge_id      = $_POST['sendid'];		//請求番号
$peyment_amount = $_POST['damt'];		//入金金額
$date_payment   = $_POST['drec'];		//入金確認日
$memo           = $_POST['memo'];		//メモ


//▼データ取得
if(($_POST['top'] == 'send')AND($charge_id)AND($peyment_amount)AND($date_payment)){
	
	//▼登録データベース
	$db_table = TABLE_USER_O_CHARGE;
	
	//▼登録データ
	$data_array = array(
		'user_o_charge_r_amount'       => $peyment_amount,
		'user_o_charge_r_date'         => $date_payment,
		'user_o_charge_condition'      => 'a',
		'user_o_charge_remarks'        => (($memo)? $memo:'null'),
		'date_update'                  => 'now()'
	);
	
	
	//▼請求情報取得
	$query_ch = tep_db_query("
		SELECT
			*
		FROM `".TABLE_USER_O_CHARGE."`
		WHERE `state` = '1'
		AND   `user_o_charge_id` = '".tep_db_input($charge_id)."'
	");
	
	if($a = tep_db_fetch_array($query_ch)){
		$d_smail   = $a['user_o_charge_date_mail_send'];
		$uorder_id = $a['user_order_id'];
	}
	
	
	/*----- 入金確認メール送信 -----*/
	if($a && !$d_smail){
		
		/*--- 請求情報 ---*/
		$query_e = tep_db_query("
			SELECT
				`c_amount`,
				`c_currency_name` AS `cur_name`
			FROM `".VIEW_CHARGE."`
			WHERE `charge_id` = '".tep_db_input($charge_id)."'
		");
		
		$e = tep_db_fetch_array($query_e);
		
		
		/*--- 注文情報取得 ---*/
		$order_query = tep_db_query("
			SELECT
				`u`.`email`,
				(CASE WHEN (`u`.`name1` is not null) THEN `u`.`name1` WHEN (`u`.`name2` is not null) THEN `u`.`name2` ELSE NULL end) AS `name`,
				`u`.`login_id`,
				`o`.`user_order_id`        AS `order_id`,
				`o`.`plan_id`              AS `o_plan_id`,
				`o`.`user_order_num`       AS `o_num`,
				`o`.`user_order_amount`    AS `o_amount`,
				`o`.`user_order_condition` AS `o_condition`,
				`o`.`user_order_remarks`   AS `o_remarks`
			FROM      `".TABLE_USER_ORDER."` `o` 
			LEFT JOIN `".TABLE_MEM00000."`   `u` ON  `u`.`memberid`  = `o`.`user_id`
			WHERE  `o`.`state` = '1'
			AND    `o`.`user_order_id` = '".tep_db_input($uorder_id)."'
		");
		
		$o = tep_db_fetch_array($order_query);
		
		
		/*--- メール送信 ---*/
		//▼送信設定
		$Eemail           = $o['email'];
		$Euser_name       = $o['name'];
		$Efs_id           = $o['login_id'];
		$Eorder_id        = $o['order_id'];
		$Echarge_amount   = $e['c_amount'];
		$Echarge_currency = $e['cur_name'];
		$Epaid_amount     = $peyment_amount;
		$Epaid_date       = $date_payment;
		
		
		Email_Payment_Confirm(
			$EmailHead,
			$EmailFoot,
			$Eemail,
			$Euser_name,
			$Efs_id,
			$Eorder_id,
			$Echarge_amount,
			$Echarge_currency,
			$Epaid_amount,
			$Epaid_date
		);
		
		//▼DB追加用
		$data_array['user_o_charge_date_mail_send'] = 'now()';
	}
	
	
	//▼検索設定
	$w_set = "`user_o_charge_id` = '".tep_db_input($charge_id)."' AND `state` = '1'";
	
	//▼DB登録
	if($a && $d_smail){
		
		//▼過去データ無効化
		$old_ar = array('date_update'=>'now()','state'=>'y');
		tep_db_perform($db_table,$old_ar,'update',$w_set);
		
		//▼データ追加
		unset($a['user_o_charge_id']);
		unset($a['date_update']);
		
		foreach($a AS $k => $v){
			
			//対応するデータを登録
			if($data_array[$k]){
				$up_ar[$k] = $data_array[$k];
				
			}else{
				$up_ar[$k] = (is_null($v))? 'null':$v;
			}
		}
		
		//▼更新日登録
		$up_ar['date_update'] = 'now()';
		
		//▼新規登録
		tep_db_perform($db_table,$up_ar);
		
	}else{
		
		//▼初回　＞追加登録
		tep_db_perform($db_table,$data_array,'update',$w_set);
	}
	
	
	//▼終了処理
	$res = 'ok';
}

$string = 'top:'.$top.'>>sendnm:'.$send_name;
//write_log($string,'w');

echo $res;
?>