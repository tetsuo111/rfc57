<?php 
require('includes/application_top.php');

if(($_COOKIE['master_id']) && ($_COOKIE['master_permission'])){
	$master_id = $_COOKIE['master_id'];
	$head_master_name = $_COOKIE['master_name'].'様';
}else{
	//$head_master_name = 'ゲスト様';
	tep_redirect('logout.php', '', 'SSL');
}


/*-------- 全体設定 --------*/
//▼とび先設定
$form_action_to = basename($_SERVER['PHP_SELF']);
$cont_set       = '?doc_id='.$_GET['doc_id'];

//▼値取得
$doc_id     = $_GET['doc_id'];
$a_instruct = $_POST['a_instruct'];
$a_from     = $_POST['a_from'];
$a_to       = $_POST['a_to'];
$p_target       = $_POST['target'];


/*-------- データ処理 --------*/
if($_POST['act'] == 'process'){
	
	/*----- 初期設定 -----*/
	$db_table = TABLE_A_DOC;			//登録DB設定
	$t_ai_id  = 'a_doc_ai_id';			//自動登録ID
	$t_id     = 'a_doc_id';				//テーブルID
	
	if($doc_id!=''){
		//▼登録チェック
		$query_check = tep_db_query("
		SELECT 
			`".$t_id."`,
			`a_doc_file_name`,
			`a_doc_file_org`
		FROM  `".$db_table."`
		WHERE `state` = '1'
		AND   `".$t_id."` = '".tep_db_input($doc_id)."'
	");

		//▼登録情報
		$b = tep_db_fetch_array($query_check);

	}else{
		$b = null;
	}


	/*----- 登録 -----*/
	if($_POST['act_del']){
		
		//▼ファイル削除
		$del_path = '../'.DIR_WS_UPLOADS_DOCS.$b['a_doc_file_name'];
		unlink($del_path);
		
		//▼DB登録削除
		$del_array = array(
			'date_update' => 'now()',
			'state'       => 'z'
		);
		
		$w_set = "`".$t_id."`='".$doc_id."' AND `state`='1'";
		tep_db_perform($db_table,$del_array,'update',$w_set);

		//紐付けレコードの削除
		tep_db_query("DELETE FROM tag00000 WHERE article_type=2 AND article_id='$doc_id'");

		//▼終了テキスト
		$end_text = '削除しました';
	
	}else{
			
		/*----- エラーチェック -----*/
		$err    = false;
		$onfile = false;

//		var_dump($_FILES);
		//▼ファイルアップロード
		if($_FILES['upfile']['size'] > 0){
			
			//▼ファイルサイズ
			if($_FILES["upfile"]["error"] != 0){
				$err = true;
				$file_size = $_FILES["upfile"]["size"];
				
				if($_FILES["upfile"]["error"] == 2){
					$err_text = '<span class="input_alert">登録するファイルの容量は50MB以下にしてください</span>';
				}else{
					$err_text = '<span class="input_alert">エラーコード：'.$_FILES["upfile"]["error"].'</span>';
				}

			}
			
			//▼ファイル種類
			if(is_uploaded_file($_FILES["upfile"]["tmp_name"])){
				
				//▼ファイルパス
				$path = $_FILES['upfile']['tmp_name'];
				
				//▼ファイル種類の獲得
				$mime = shell_exec('file -bi '.escapeshellcmd($path));
				$mime = trim($mime);
				$mime = preg_replace("/ [^ ]*/", "", $mime);
				$mime = str_replace(";", "", $mime);
				
				if((preg_match("/\/*(pdf)$/", $mime))OR(preg_match("/(\/acrobat)$/", $mime))){
				
					//▼登録ファイル名
					$extension   = substr(strrchr($_FILES["upfile"]["name"], '.'), 1);	//拡張子を取得
					$extension   = mb_strtolower($extension);							//小文字化
					$upfile_name = 'a_'.time().'.'.$extension;
					
					//▼保存先設定
					$file_path     = $_FILES["upfile"]["tmp_name"];						//tmpファイル名
					$file_name_org = $_FILES["upfile"]["name"];							//選択したファイの名前
					$out_file_path = '../'.DIR_WS_UPLOADS_DOCS.$upfile_name;			//最終保存ファイルパス

//					echo $out_file_path;
					//▼ファイルのアップロード
					if(move_uploaded_file($file_path,$out_file_path)){
						$onfile = true;
					}else{

						$err = true;
						$err_text = '<span class="input_alert">ファイルコピーエラー</span>';
					}

				}else{
					$err = true;
					$err_text = '<span class="input_alert">登録できるファイルは「.pdf」のみです</span>';
				}
				
			}else{
				$err = true;
				$err_text = '<span class="input_alert">不正なアップロードです</span>';
			}
		}


		if($err == false){
			
			//▼登録情報
			$sql_data_array = array(
				'a_doc_file_name'   => (($onfile)? $upfile_name   : $b['a_doc_file_name']),
				'a_doc_file_org'    => (($onfile)? $file_name_org : $b['a_doc_file_org']),
				'a_doc_instruction' => $a_instruct,
				'a_doc_date_from'   => (($a_from)? $a_from :'null'),
				'a_doc_date_to'     => (($a_to)?   $a_to   :'null'),
				'a_doc_condition'   => 'a',
				'date_create'       => 'now()',
				'state'             => '1'
			);


			if ($b){
				
				//▼古いファイルを削除
				if($onfile){
					$del_path = '../'.DIR_WS_UPLOADS_DOCS.$b['a_doc_file_name'];
					unlink($del_path);
				}
				
				//更新登録
				zDBUpdate($db_table,$sql_data_array,$b[$t_id]);

			}else{
				//新規登録
				$doc_id = zDBNewUniqueID($db_table,$sql_data_array,$t_ai_id,$t_id);
//				var_dump($doc_id);


			}

//			var_dump($p_target);
			tep_db_tagging('2',$doc_id,$p_target);
			//▼終了テキスト
			$end_text = '登録しました';

		}else{
			echo 'アップロードエラー';
		}
	}
	
	
	//▼終了処理
	if($end_text){$end = 'end';}
	
	
}else{

	//▼初期設定
	$query =  tep_db_query("
		SELECT
			`a_doc_id`          AS `id`,
			`a_doc_file_org`    AS `name`,
			`a_doc_instruction` AS `instruct`,
			DATE_FORMAT(`a_doc_date_from`,'%Y-%m-%d') AS `from`,
			DATE_FORMAT(`a_doc_date_to`  ,'%Y-%m-%d') AS `to`
		FROM  `".TABLE_A_DOC."`
		WHERE `state` = '1'
		ORDER BY `a_doc_id` ASC
	");
	
	//▼
	while($a = tep_db_fetch_array($query)) {

		//対象
		$query2 = tep_db_query("SELECT * FROM tag00000 INNER JOIN typ01004 ON tag00000.bctype = typ01004.id WHERE article_type=2 AND article_id='".$a['id']."' ORDER BY bctype ASC");

		$doc_target_array = [];
		$doc_target_array[$a['id']] = '';
		while($b = tep_db_fetch_array($query2)) {

			$doc_target_array[$a['id']] .= $doc_target_array[$a['id']]==''?$b['tag']:','.$b['tag'];


			if($a['id'] == $doc_id){
				$p_target[] = $b['bctype'];
			}
		}


		$operation = '<a href="'.$form_action_to.'?doc_id='.$a['id'].'">編集する</a>';
		
		$list_in.= '<tr>';
		$list_in.= '<td>'.$a['name'].'</td>';
		$list_in.= '<td>'.$doc_target_array[$a['id']].'</td>';
		$list_in.= '<td>'.$a['instruct'].'</td>';
		$list_in.= '<td>'.$a['from'].'</td>';
		$list_in.= '<td>'.$a['to'].'</td>';
		$list_in.= '<td>'.$operation.'</td>';
		$list_in.= '</tr>';
		
		if($a['id'] == $doc_id){
			$a_instruct = $a['instruct'];
			$a_from     = $a['from'];
			$a_to       = $a['to'];
		}
	}
}



/*----- 表示フォーム -----*/
if($end == 'end'){
	
	$input_form = '<p>'.$end_text.'</p>';
	$input_form.= '<a href="'.$form_action_to.'">資料の登録を続ける</a>';
	
}else{

	/*-------- フォーム表示 --------*/
	//▼自動入力要素
	$input_auto = '<input type="hidden" name="act" value="process">';
	$input_auto.= '<input type="hidden" name="MAX_FILE_SIZE" value="50000000">';


	//▼削除ボタン
	$button_del = '<input type="submit" class="form_submit" name="act_del" value="削除">';

	//▼登録ボタン
	$input_button = '<input type="submit" class="form_submit" name="act_send" value="この内容で登録する">';
	$input_button.= ($_GET['doc_id'])? $button_del:'';
	$input_button.= '<a class="spc10_l" href="'.$form_action_to.'">クリア</a>';
	
	//▼入力必須
	$required     = ($_GET['doc_id'])? '':'required';
	
	//▼入力項目
	$input_file    = '<input type="file" name="upfile"     value="" accept="application/pdf" '.$required.'>';
	$input_explane = '<input type="text" name="a_instruct" value="'.$a_instruct.'" style="width:400px;">';
	$input_from    = '<input type="text" name="a_from"     value="'.$a_from.'"     class="in_date" id="sFrom">';
	$input_to      = '<input type="text" name="a_to"       value="'.$a_to.'"       class="in_date" id="sTo">';
	
	
	/*-------- フォーム表示 --------*/
	//▼表示リスト
	$list_head = '<th>資料</th><th>対象</th><th>説明</th><th>表示開始日</th><th>表示終了日</th><th>操作</th>';

	$input_list = '<table class="input_list">'  ;
	$input_list.= '<tr>'.$list_head.'</tr>';
	$input_list.= $list_in;
	$input_list.= '</table>' ;


	//対象区分
	$query =  tep_db_query("SELECT * FROM typ01004 ORDER BY id asc");

	$target_array = [];
	while($a = tep_db_fetch_array($query)) {

		$target_array[$a['id']] = $a['tag'];

	}

	//▼入力項目(チェックボックスに変更）
	$e_target  = zCheckboxSet2($target_array,$p_target ,'target[]');

	//▼登録フォーム
	$input_form = '<form action="'.$form_action_to.$cont_set.'" method="POST" enctype="multipart/form-data">';
	$input_form.= $input_auto;
	$input_form.= '<table class="input_form">';
	$input_form.= '<tr><th>対象</th><td>'.$e_target.'</td></tr>';
	$input_form.= '<tr><th>登録ファイル</th><td>'.$input_file.'</td></tr>';
	$input_form.= '<tr><th>ファイルの説明</th><td>'.$input_explane.'</td></tr>';
	$input_form.= '<tr><th>表示開始日</th><td>'.$input_from.'</td></tr>';
	$input_form.= '<tr><th>表示終了日</th><td>'.$input_to.'</td></tr>';
	$input_form.= '</table>';
	$input_form.= '<div class="spc20">';
	$input_form.= $input_button;
	$input_form.= '</div>';
	$input_form.= '</form>';
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<?php echo $favicon."\n"; ?>
	<title><?php echo $title;?></title>
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="robots" content="noindex,nofollow,noarchive">
	<meta name="format-detection" content="telephone=no">
	<meta name="format-detection" content="email=no">
	<link rel="stylesheet" type="text/css" href="../css/cssreset.css" media="all">
	<link rel="stylesheet" type="text/css" href="../css/common.css"   media="all">
	<link rel="stylesheet" type="text/css" href="../css/master.css"   media="all">
	<link rel="stylesheet" type="text/css" href="../js/jquery-ui/jquery-ui.min.css">
	
	<script src="../js/jquery-3.2.1.min.js"          charset="UTF-8"></script>
	<script src="../js/jquery-migrate-1.4.1.min.js" charset="UTF-8"></script>
	<script src="../js/jquery-ui/jquery-ui.min.js"  charset="UTF-8"></script>
	<script type="text/javascript">
		$(function() {
			$('#sFrom').datepicker({ dateFormat: 'yy-mm-dd', });
			$('#sTo').datepicker({ dateFormat: 'yy-mm-dd', });
		});
	</script>
	
	<style>
		.in_date{width:80px;}
		.list_area{margin:50px 0;}
		.list_area .input_list{width:100%;}
	</style>

</head>
<body id="body">
<div id="wrapper">
	
	<div id="header">
		<?php require('inc_master_header.php');?>
	</div>
	<div id="head_line">
		<?php require('inc_master_head_line.php');?>
	</div>
	
	<div id="content">
		<div class="content_outer">
			<div id="left1">
				<div class="inner">
					<?php require('inc_master_left.php'); ?>
				</div>
			</div>
		
			<div id="left2">
				<div class="inner">
				
					<div class="admin_menu">
						<?php require('inc_master_menu.php');?>
					</div>
					
					<h2>顧客用資料登録</h2>
					<div>
						<div class="input_area">
							<div class="m_inner">
								<?php echo $input_form;?>
								<?php echo $err_text;?>
							</div>
						</div>
						<div class="list_area">
							<?php echo $input_list;?>
						</div>
					</div>
				</div>
			</div>

			<div class="float_clear"></div>
		</div>
	</div>
	
<!--	<div id="footer">-->
<!--		--><?php //require('inc_master_footer.php'); ?>
<!--	</div>-->
</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
