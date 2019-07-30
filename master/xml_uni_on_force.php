<?php
require('includes/application_top.php');
	
//▼初期設定
$res = "err";
$top       = $_POST['top'];
$send_id   = $_POST['sendid'];
$rank_id   = $_POST['rankid'];

//▼データ取得
if(($_POST['top'] == "union")AND($send_id)AND($rank_id)){

	$check_uni = tep_db_query("
		SELECT 
			`position_id`
		FROM `".TABLE_P_UNI_LEVEL."`
		WHERE `state`       = '1'
		AND   `position_id` = '".tep_db_input($send_id)."'
	");
	
	//▼登録済みUNILEVELの確認
	if(!tep_db_num_rows($check_uni)){
	
		//▼ポジション別ユーザー情報
		$user_query =  tep_db_query("
			SELECT 
				`u`.`user_id`,
				`u`.`fs_id`,
				`u`.`user_email`,
				`p`.`position_id`,
				`p`.`position_inviter`       AS `inviter`,
				`pul`.`p_uni_level_up_list`  AS `pu_list` ,
				`pul`.`p_uni_level_absolute` AS `pu_absolute`,
				CONCAT(`ui`.`user_name`,'　',`ui`.`user_name2`) AS `name`
			FROM      `".TABLE_POSITION."`    AS `p`
			LEFT JOIN `".TABLE_P_UNI_LEVEL."` AS `pul` ON `pul`.`position_id` = `p`.`position_inviter`
			LEFT JOIN `".TABLE_USER."`        AS `u`   ON   `u`.`user_id` = `p`.`user_id`
			LEFT JOIN `".TABLE_USER_INFO."`   AS `ui`  ON  `ui`.`user_id` = `p`.`user_id`
			WHERE `u`.`state` = '1'
			AND   `u`.`user_permission` = 'u'
			AND   ((`ui`.`state` = '1')OR(`ui`.`state` IS NULL))
			AND   `p`.`position_id` = '".tep_db_input($send_id)."'
			AND   `p`.`state`       = '1'
			AND   `pul`.`state`     = '1'
		");

		if($a = tep_db_fetch_array($user_query)){
			
			$user_id = $a['user_id'];
			
			
			/*----- ポジションをアクティブに変更 -----*/
			//▼登録用
			$position_ar = array(
				'position_condition'   => 'a',
				'position_date_active' => 'now()'
			);
			
			//▼検索設定
			$pw_set = "`position_id`='".tep_db_input($send_id)."' AND `state`='1'";
			tep_db_perform(TABLE_POSITION,$position_ar,'update',$pw_set);
			
			
			/*----- ユニポジション発行 -----*/
			//▼登録用ステータス
			$p_uni_absolute = $a['pu_absolute'] + 1;
			$p_uni_up_id    = $a['inviter'];
			$p_uni_up_list  = $a['pu_list'].$a['position_id'].'-';
			 
			//▼登録用配列
			$uni_array = array(
				'position_id'          => $send_id,
				'p_uni_level_absolute' => $p_uni_absolute,
				'p_uni_level_up_id'    => $p_uni_up_id,
				'p_uni_level_up_list'  => $p_uni_up_list,
				'date_create'          => 'now()',
				'state'                => '1'
			);
			
			//▼登録DB
			$db_table = TABLE_P_UNI_LEVEL;
			zDBNewUniqueID($db_table,$uni_array,'p_uni_level_ai_id','p_uni_level_id');
			
			
			/*----- ステータスを追加＆更新 -----*/
			//▼ユニステータス
			$uni_status = array(
				'position_id'              => $send_id,
				'user_id'                  => $user_id,
				'p_uni_status_rank_id'     => $rank_id,
				'p_uni_status_date_reckon' => 'now()',
				'date_create'              => 'now()',
				'state'                    => '1'
			);
			
			//▼登録DB
			$db_table_ul = TABLE_P_UNI_STATUS;
			zDBNewUniqueID($db_table_ul,$uni_status,'p_uni_status_ai_id','p_uni_status_id');
			
			//▼情報の登録
			$res = 'ok';
		}
	}
}

$string = 'top:'.$top.'>>sendid:'.$send_id.'>>rankid:'.$rank_id."\n";
write_log($string,'a');

echo $res;
?>