<?php
require('includes/application_top.php');
	
//▼初期設定
$res = "err";
$user_id = $_POST['user_id'];

//▼データ取得
if(($_POST['act'] == "process")AND(!empty($_POST['user_id']))){

	//▼エラーチェック
	$err = false;
	$res = 'nodata';
	
	$user_wallet_card_email = trim($_POST["user_wallet_card_email"]);
	
	if(empty($user_wallet_card_email))                        {$err = true; $res='登録会員IDを入力してください';}
	if(empty($_POST["user_identification_number"]))           {$err = true; $res='証明書番号を入力してください';}
	if(empty($_POST["user_identification_date_issue"]))       {$err = true; $res='発行年月日を入力してください';}
	if(empty($_POST["user_identification_date_expire"]))      {$err = true; $res='有効期限を入力してください';}

	if(empty($_POST["user_address_certification_type"]))      {$err = true; $res='住所証明書種類を選択してください';}
	if(empty($_POST["user_address_certification_date_issue"])){$err = true; $res='住所証明書発行日を入力してください';}
	
	
	//▼DB登録
	if($err == false){
		
		/*-------------登録準備-------------*/
		//▼検索条件
		$w_set = "`user_id` = '".tep_db_input($user_id)."' AND `state` = '1'";
		
		
		/*-------------WC情報登録-------------*/
		//▼WC更新用
		$wc_data_array = array(
			'user_id' => $user_id,
			'user_wallet_card_own'              => $_POST['user_own'],
			'user_wallet_card_email'            => $user_wallet_card_email,
			'user_wallet_card_date_application' => 'now()',
			'user_wallet_card_condition'        => 'a',
			'user_wallet_card_accept'           => '1',
			'date_create'                       => 'now()',
			'state'                             => '1'
		);

		//▼DB更新登録
		zDBUpdate2(TABLE_USER_WALLET_CARD,$wc_data_array,$w_set);
		
		
		/*-------------身分証情報登録-------------*/
		//▼身分証情報登録用
		$ident_data_array = array(
			'user_id' => $user_id,
			'user_identification_number'      => $_POST['user_identification_number'],
			'user_identification_date_issue'  => $_POST['user_identification_date_issue'],
			'user_identification_date_expire' => $_POST['user_identification_date_expire'],
			'user_identification_condition'   => '1',
			'date_create'                     => 'now()',
			'state'                           => '1'
		);
		
		
		//▼登録DB
		$dbt = TABLE_USER_IDENTIFICATION;
		
		//▼データの確認
		if(zDBCheckReg($dbt,$user_id)){
			//データがある　＞　更新登録
			zDBUpdate2($dbt,$ident_data_array,$w_set);
		
		}else{
			//データがない　＞　新規登録
			tep_db_perform($dbt,$ident_data_array);
		}
		
		
		
		/*-------------住所証明書情報登録-------------*/
		//▼住所証明書登録用
		$ac_data_array = array(
			'user_id'                               => $user_id,
			'user_address_certification_type'       => $_POST['user_address_certification_type'],
			'user_address_certification_date_issue' => $_POST['user_address_certification_date_issue'],
			'date_create'                           => 'now()',
			'state'                                 => '1'
		);
		
		
		//▼登録DB
		$dbt1 = TABLE_USER_ADDRESS_CERTIFICATION;
		
		//▼データ登録
		if(zDBCheckReg($dbt1,$user_id)){
			//データがある　＞　更新登録
			zDBUpdate2($dbt1,$ac_data_array,$w_set);
		
		}else{
			//データがない　＞　新規登録
			tep_db_perform($dbt1,$ac_data_array);
		}
		
		
		/*------------- ステータス確認 -------------*/
		//▼ステータス登録確認
		$aa = zUserStatusCheck($user_id);
		
		if(
			(!$aa['reg'])AND(!$aa['ident'])AND(!$aa['addr'])
		){
			//▼ステータス更新
			if($aa['reg']   == 'n'){zUserWCStatusUpdate('user_wc_status_reg','u',$user_id);}					//カード情報
			if($aa['ident'] == 'n'){zUserWCStatusUpdate('user_wc_status_identification','u',$user_id);}			//身分証
			if($aa['addr']  == 'n'){zUserWCStatusUpdate('user_wc_status_address_certification','u',$user_id);}	//住所証明書
			
		}else{
			
			//▼登録用配列
			$sql_status_array = array(
				'user_wc_status_reg'                   => '1',
				'user_wc_status_identification'        => '1',
				'user_wc_status_address_certification' => '1'
			);
			
			//▼登録DB
			$dbt2 = TABLE_USER_WC_STATUS;
			
			if($aa){
				//更新登録
				tep_db_perform($dbt2, $sql_status_array,'update',$w_set);
				
			}else{
				//ステータス自体がない　＞提出済みに変更
				$sql_status_array['user_id']     = $user_id;
				$sql_status_array['date_create'] = 'now()';
				$sql_status_array['state']       = '1';
				
				//新規登録
				tep_db_perform($dbt2, $sql_status_array);
			}
		}
		
		//▼情報の登録
		$res = 'ok';
	}
}

echo $res;
?>