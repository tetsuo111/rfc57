<?php
require('includes/application_top.php');


//▼初期設定
$res     = "登録できるデータがありません";
$user_id = $_POST['user_id'];


//▼データ登録
if((($_POST['certif_type'] == "a")OR($_POST['certif_type'] == "b")OR($_POST['certif_type'] == "c"))
	AND(!empty($_POST['user_id']))
){

	//▼エラーチェック
	//$_FILES[ユーザー定義]['name']　　　＞　選択したファイル名
	//$_FILES[ユーザー定義]['tmp_name']　＞　サーバーの一時保管場所のファイル名
	//$_FILES[ユーザー定義]['size']　　　＞　ファイルサイズ
	//$_FILES[ユーザー定義]['type']　　　＞　ファイルタイプ
	
	
	if(empty($_FILES['upfile']['tmp_name'])){
		$err = true;
		$res = 'ファイルを選択してください';
		
	}else{
	
		//Po ポストデータ
		//▼データ取得
		$Po_name    = $_FILES['upfile']['name'];				//名前配列
		$Po_tmp     = $_FILES['upfile']['tmp_name'];			//種別配列
		$Po_size    = $_FILES['upfile']['size'];				//サイズ配列
		
		
		//▼エラーチェック
		$check_file = z_check_upfile($Po_name,$Po_tmp,$Po_size);
		$err_flag   = ($check_file['err'] * 1);
		
		
		//▼最終エラー判定
		if($err_flag > 0){
			$err = true;
			$res = $check_file['text'];
		}
		
	}
	
	//▼DB登録
	if($err == false){
		
		//▼ファイルアップロード実行
		if($web_f_name = z_resize_jpg_img(DIR_WS_UPLOADS_IDENTIFICATION,DIR_WS_UPLOADS_ORG,$Po_name,$Po_tmp,$Po_size,$user_id,$_POST['certif_type'])){
			
			//▼登録用配列
			$data_array = array(
				'memberid' => $user_id,
				'user_certification_type'             => $_POST['certif_type'],
				'user_certification_date_application' => 'now()',
				'user_certification_file_name'        => $web_f_name,
				'user_certification_file_org'         => $Po_name,
				'user_certification_condition'        => '1',
				'date_create'                         => 'now()',
				'state'                               => '1'
			);
			
			//▼新規登録
			zDBNewUniqueID(TABLE_USER_CERTIFICATION,$data_array,'user_certification_ai_id','user_certification_id');
			
			
			/*------------- ステータス確認 -------------*/
			$aa    = zUserStatusCheck($user_id);
			$param = 'user_wc_status_certification';
			
			//▼提出状況を取得
			$certif_check = tep_db_query("
				SELECT
					`user_certification_type`      AS `ty`,
					COUNT(`user_certification_id`) AS `num`
				FROM `".TABLE_USER_CERTIFICATION."`
				WHERE `user_id` = '".tep_db_input($user_id)."'
				AND   `state`   = '1'
				GROUP BY `user_certification_type`
			 ");
			
			while($a = tep_db_fetch_array($certif_check)){
				$tmp_ar[$a['ty']] = $a['num'];
			}
			
			//▼エラーチェック
			$err = false;
			foreach($certif_list AS $kc => $vc){
				if(!$tmp_ar[$kc]){$err = true;}
			}
			
			//▼証明書
			if($aa){
				
				if($err == false){
					if($aa['certif'] != 'u'){
						//▼DB登録
						$sc = ($aa['certif'] == 'n')? 'u':'1';
						zUserWCStatusUpdate($param,$sc,$user_id);
					}
				}else{
					//エラーの場合は元に戻す
					zUserWCStatusUpdate($param,'null',$user_id);
				}
			
			}else{
				//▼ステータス自体がない　＞新規登録
				zUserWCStatusNew($param,'1',$user_id);
			}
			
			$res = 'アップロードしました';
		}
	}
}


//▼ログ出力用
//write_log($string,'w');

echo $res;
?>