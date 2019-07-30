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
$cont_set       = '?qa_id='.$_GET['qa_id'];

//▼区分
$cat_ar = zQAtag();


//▼値取得
$qa_id = $_GET['qa_id'];
$a_q   = $_POST['a_q'];
$a_a   = $_POST['a_a'];
$a_tag = $_POST['a_tag'];
$p_target = $_POST['target'];

//var_dump($p_target);

/*-------- データ処理 --------*/
if($_POST['act'] == 'process'){
	
	/*----- 初期設定 -----*/
	$db_table = TABLE_A_QANDA;			//登録DB設定
	$w_set = "`a_qanda_id`='".tep_db_input($qa_id)."' AND `state`='1'";
	
	/*----- 登録 -----*/
	if($_POST['act_del']){
		
		//▼DB登録削除
		$del_array = array('date_update' => 'now()','state'       => 'z');
		tep_db_perform($db_table,$del_array,'update',$w_set);

		//紐付けレコードの削除
		tep_db_query("DELETE FROM tag00000 WHERE article_type=1 AND article_id='$qa_id'");

		//▼終了テキスト
		$end_text = '削除しました';
	
	}else{
			
		/*----- エラーチェック -----*/
		$err    = false;
		
		if(!$a_q)  {$err = true;$err_text = '<p class="alert">質問を入力してください</p>';}
		if(!$a_a)  {$err = true;$err_text.= '<p class="alert">回答を入力してください</p>';}
		if(!$a_tag){$err = true;$err_text.= '<p class="alert">質問区分を入力してください</p>';}
		
		if($err == false){
			
			//▼登録情報
			$sql_data_array = array(
				'a_qanda_q'      => $a_q,
				'a_qanda_a'      => $a_a,
				'a_qanda_tag_id' => $a_tag,
				'date_create'    => 'now()',
				'state'          => '1'
			);
			
			if($qa_id){
				
				unset($sql_data_array['date_create']);
				unset($sql_data_array['state']);
				$sql_data_array['date_update'] = 'now()';
				
				//更新登録
				tep_db_perform($db_table,$sql_data_array,'update',$w_set);



			}else{
				//新規登録
				tep_db_perform($db_table,$sql_data_array);
				$qa_id = tep_db_insert_id();

			}

			tep_db_tagging('1',$qa_id,$p_target);
			
			//▼終了テキスト
			$end_text = '登録しました';
		}
	}
	
	
	//▼終了処理
	if($end_text){$end = 'end';}
	
	
}else{


	//▼初期設定
	$query =  tep_db_query("
		SELECT
			`a_qanda_id`     AS `id`,
			`a_qanda_q`      AS `q`,
			`a_qanda_a`      AS `a`,
			`a_qanda_tag_id` AS `c`
		FROM  `".TABLE_A_QANDA."`
		WHERE `state` = '1'
		ORDER BY `a_qanda_tag_id`
	");
	
	//▼表示設定
	while($a = tep_db_fetch_array($query)) {

		$query2 = tep_db_query("SELECT * FROM tag00000 INNER JOIN typ01004 ON tag00000.bctype = typ01004.id WHERE article_type=1 AND article_id='".$a['id']."' ORDER BY bctype ASC");

		$qa_target_array = [];
		$qa_target_array[$a['id']] = '';
		while($b = tep_db_fetch_array($query2)) {

			$qa_target_array[$a['id']] .= $qa_target_array[$a['id']]==''?$b['tag']:','.$b['tag'];


			if($a['id'] == $qa_id){
				$p_target[] = $b['bctype'];
			}
		}

		$operation = '<a href="'.$form_action_to.'?qa_id='.$a['id'].'"><button type="button">編集する</button></a>';
		
		$list_in.= '<tr>';
		$list_in.= '<td>'.$a['id'].'</td>';
		$list_in.= '<td>'.$qa_target_array[$a['id']].'</td>';
		$list_in.= '<td>'.$cat_ar[$a['c']]['name'].'</td>';
		$list_in.= '<td>'.$a['q'].'</td>';
		$list_in.= '<td nowrap>'.$operation.'</td>';
		$list_in.= '</tr>';
		
		if($a['id'] == $qa_id){
			$a_q   = $a['q'];
			$a_a   = $a['a'];
			$a_tag = $a['c'];
		}
	}
}



/*----- 表示フォーム -----*/
if($end == 'end'){
	
	$input_form = '<p>'.$end_text.'</p>';
	$input_form.= '<a href="'.$form_action_to.'">登録を続ける</a>';
	
}else{

	//対象区分
	$query =  tep_db_query("SELECT * FROM typ01004 ORDER BY id asc");

	$target_array = [];
	while($a = tep_db_fetch_array($query)) {

		$target_array[$a['id']] = $a['tag'];

	}


	/*-------- 登録済み一覧 --------*/
	//▼表示リスト
	$list_head = '<th style="width:30px;">番号</th>';
	$list_head.= '<th>対象</th>';
	$list_head.= '<th>区分</th>';
	$list_head.= '<th>質問</th>';
	$list_head.= '<th>操作</th>';

	$input_list = '<table class="input_list">'  ;
	$input_list.= '<tr>'.$list_head.'</tr>';
	$input_list.= $list_in;
	$input_list.= '</table>' ;
	

	/*-------- フォーム表示 --------*/
	//▼自動入力要素
	$input_auto = '<input type="hidden" name="act" value="process">';


	//▼削除ボタン
	$button_del = '<input type="submit" class="form_submit" name="act_del" value="削除">';

	//▼登録ボタン
	$input_button = '<input type="submit" class="form_submit" name="act_send" value="この内容で登録する">';
	$input_button.= ($_GET['qa_id'])? $button_del:'';
	$input_button.= '<a class="spc10_l" href="'.$form_action_to.'">クリア</a>';
	
	//▼入力必須
	$required     = ($_GET['qa_id'])? '':'required';
	
	//▼入力項目
	$input_q = '<input type="text" name="a_q" value="'.$a_q.'" style="width:400px; padding:3px 5px;">';
	$input_a = '<textarea name="a_a" class="in_txt">'.$a_a.'</textarea>';
	
	//▼登録用
	$tag_ar = [];
	foreach($cat_ar AS $k => $v){
		$tag_ar[$k] = $v['name'];
	}
	
	$input_tag = zSelectListSet($tag_ar,$a_tag,'a_tag','▼区分','','','','required');

	//▼入力項目(チェックボックスに変更）
	$e_target  = zCheckboxSet2($target_array,$p_target ,'target[]');




	//▼登録フォーム
	$input_form = '<form action="'.$form_action_to.$cont_set.'" method="POST">';
	$input_form.= $input_auto;
	$input_form.= '<table class="input_form">';
	$input_form .= '<tr><th nowrap>※対象</th><td>'.$e_target.'</td></tr>';
	$input_form.= '<tr><th>区分</th><td>'.$input_tag.'</td></tr>';
	$input_form.= '<tr><th>質問</th><td>'.$input_q.'</td></tr>';
	$input_form.= '<tr><th>回答</th><td>'.$input_a.'</td></tr>';
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

	<script src="../js/jquery-3.2.1.min.js"          charset="UTF-8"></script>
	<script src="../js/jquery-migrate-1.4.1.min.js" charset="UTF-8"></script>
	
	<style>
		.input_list{width:100%;}
		.in_txt{width:400px; height:300px;resize:none;padding:5px;}

		#wrapper{
			min-height:120%;
		}

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
					
					<h2>Q and Aを登録</h2>
					<div class="m_area">
						<div class="m_list_area">
							<?php echo $input_list;?>
						</div>
						<div class="m_input_area">
							<div class="m_inner">
								<?php echo $err_text;?>
								<?php echo $input_form;?>
							</div>
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
