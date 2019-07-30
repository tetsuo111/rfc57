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

//★
$rank_id  = $_GET['m_detail_id'];
$cont_set = '?m_detail_id='.$rank_id;


//★変数設定
$m_detail_name = $_POST['m_detail_name'];



/*-------- データ処理 --------*/
if($_POST['act'] == 'process'){
	
	/*----- エラーチェック -----*/
	$err = false;
	
	//★表示名
	if(!$m_detail_name){$err = true; $err_text.= '<span class="input_alert">社内内訳名を入力してください</span>';}
	
	
	//▼表示設定
	if(($err == false)OR(!$_POST['act_cancel'])){    //エラーなし
		
		/*----- 初期設定 -----*/
		//★
		$db_table = TABLE_M_DETAIL;			//登録DB設定
		$t_ai_id = 'm_detail_ai_id';			//自動登録ID
		$t_id    = 'm_detail_id';				//テーブルID
		
		
		//▼登録チェック
		$query_check = tep_db_query("
			SELECT 
				`".$t_id."`
			FROM  `".$db_table."`
			WHERE `state` = '1'
			AND   `".$t_id."` = '".tep_db_input($rank_id)."'
		");

		//★登録情報
		$sql_data_array = array(
			'm_detail_name' => $m_detail_name,
			'date_create' => 'now()',
			'state'       => '1'
		);
		
		
		/*----- DB登録 -----*/
		if ($b = tep_db_fetch_array($query_check)){
			//更新登録
			zDBUpdate($db_table,$sql_data_array,$b[$t_id]);

		}else{
			//新規登録
			zDBNewUniqueID($db_table,$sql_data_array,$t_ai_id,$t_id);
		}
		
		//▼終了テキスト
		$end = 'end';
		
	}
	
	
}else{

	//★初期設定
	$query =  tep_db_query("
		SELECT
			`m_detail_id`   AS `id`,
			`m_detail_name` AS `name`
		FROM  `".TABLE_M_DETAIL."`
		WHERE `state` = '1'
		ORDER BY `m_detail_id` ASC
	");
	
	//★
	while($a = tep_db_fetch_array($query)) {
		$operation = '<a href="'.$form_action_to.'?m_detail_id='.$a['id'].'">編集する</a>';
		$list_in.= '<tr><td>'.$a['name'].'</td><td>'.$operation.'</td></tr>';
		
		if($a['id'] == $rank_id){
			$m_detail_name = $a['name'];
		}
	}
}



/*-------- フォーム表示 --------*/
//▼自動入力要素
$input_auto = '<input type="hidden" name="act" value="process">';

//▼登録ボタン
$input_button = '<input type="submit" class="form_submit" name="act_send" value="この内容で登録する">';
$input_button.= '<a class="spc10_l" href="'.$form_action_to.'">クリア</a>';

//★入力項目
$i_m_detail_name = '<input type="text" class="input_text" name="m_detail_name" value="'.$m_detail_name.'" required>';



/*----- 表示フォーム -----*/
if($end == 'end'){
	
	$input_form = '<p>登録しました</p>';
	$input_form.= '<a href="'.$form_action_to.'">社内内訳の登録を続ける</a>';
	
}else{

	//★表示リスト
	$list_head = '<th>社内内訳名</th><th>操作</th>';

	$input_list = '<table class="input_list">'  ;
	$input_list.= '<tr>'.$list_head.'</tr>';
	$input_list.= $list_in;
	$input_list.= '</table>' ;


	//★登録フォーム
	$input_form = '<form action="'.$form_action_to.$cont_set.'" method="post">';
	$input_form.= $input_auto;
	$input_form.= '<table class="input_form">';
	$input_form.= '<tr><th>社内内訳名</th><td>'.$i_m_detail_name.'</td></tr>';
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
	<script src="../js/jquery-3.2.1.min.js" charset="UTF-8"></script>
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
					
					<h2>社内内訳登録</h2>
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
	
	<div id="footer">
		<?php require('inc_master_footer.php'); ?>
	</div>
</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
