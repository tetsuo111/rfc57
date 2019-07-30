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
$cont_set       = ($_GET['notice_id'])? '?notice_id='.$_GET['notice_id']:'';


//▼値取得
$notice_id     = $_GET['notice_id'];

$NowDay1 = date( "Y-m-d", time() );
$NowDay2 = date( "Y/m/d", time() );

//▼データ取得
$p_target  = $_POST['target'];
$p_subject = $_POST['subject'];
$p_text    = $_POST['text'];
$p_send    = $_POST['send'];


/*-------- データ処理 --------*/
if($_POST['act'] == 'process'){
	
	/*----- エラーチェック -----*/
	$err = false;
	
	if(!$p_target)  {$err = true; $err_text.= '<p class="alert">対象を入力してください</p>';}
	if(!$p_subject) {$err = true; $err_text.= '<p class="alert">件名を入力してください</p>';}
	if(!$p_text)    {$err = true; $err_text.= '<p class="alert">本文を入力してください</p>';}
	if(!$p_send)    {$err = true; $err_text.= '<p class="alert">配信日を入力してください</p>';}
	
	
	//▼表示設定
	if($err == false){
		
		/*----- 初期設定 -----*/
		$db_table = TABLE_A_NOTICE;		//登録DB設定
		$t_ai_id  = 'a_notice_ai_id';	//自動登録ID
		$t_id     = 'a_notice_id';		//テーブルID
		
		
		//▼登録チェック
		$query_check = tep_db_query("
			SELECT 
				`".$t_id."`
			FROM  `".$db_table."`
			WHERE `state` = '1'
			AND   `".$t_id."` = '".tep_db_input($notice_id)."'
		");
		
		
		//▼登録情報
		$sql_data_array = array(
			'a_notice_target'    => $p_target,
			'a_notice_subject'   => $p_subject,
			'a_notice_text'      => $p_text,
			'a_notice_date_send' => $p_send,
			'a_notice_condition' => 'a',
			'date_create'        => 'now()',
			'state'              => '1'
		);
		
		
		/*----- DB登録 -----*/
		if($_POST['act_del']){
			
			$del_array = array(
				'date_update' => 'now()',
				'state'       => 'z'
			);
			
			$w_set = "`".$t_id."`='".$notice_id."' AND `state`='1'";
			tep_db_perform($db_table,$del_array,'update',$w_set);
			
			//▼終了テキスト
			$end_text = '削除しました';
		
		}else{
			
			if($b = tep_db_fetch_array($query_check)){
				//更新登録
				zDBUpdate($db_table,$sql_data_array,$b[$t_id]);

			}else{
				//新規登録
				zDBNewUniqueID($db_table,$sql_data_array,$t_ai_id,$t_id);
			}
			
			//▼終了テキスト
			$end_text = '登録しました';
		} 
		
		//▼終了処理
		$end = 'end';
	}
	
}else{

	//▼初期設定
	$query =  tep_db_query("
		SELECT 
			`a_notice_id`      AS `id`,
			`a_notice_target`  AS `target`,
			`a_notice_subject` AS `subject`,
			`a_notice_text`    AS `text`,
			DATE_FORMAT(`a_notice_date_send`,'%Y-%m-%d') AS `send`
		FROM `".TABLE_A_NOTICE."`
		WHERE `state`     = '1'
		ORDER BY `a_notice_id` ASC
	");
	
	
	//▼既存のイベントを取得
	while($a = tep_db_fetch_array($query)) {
		
		//操作
		$operation = '<a href="'.$form_action_to.'?notice_id='.$a['id'].'">編集する</a>';

		$list_in.= '<tr>';
		$list_in.= '<td>'.$a['send'].'</td>';
		$list_in.= '<td>'.$NoticeTargetArray[$a['target']].'</td>';
		$list_in.= '<td>'.$a['subject'].'</td>';
		$list_in.= '<td>'.$operation.'</td>';
		$list_in.= '</tr>';
		
		if($a['id'] == $notice_id){
			$p_target  = $a['target'];
			$p_subject = $a['subject'];
			$p_text    = $a['text'];
			$p_send    = $a['send'];
		}
	}
}



/*----- 表示フォーム -----*/
if($end == 'end'){
	
	$input_form = '<p>'.$end_text.'</p>';
	$input_form.= '<a href="'.$form_action_to.'">お知らせの登録を続ける</a>';
	
}else{

	/*-------- フォーム表示 --------*/
	//▼入力必須
	$required     = 'required';
	
	//▼入力項目 
	$e_target  = zSelectListSet($NoticeTargetArray,$p_target ,'target' ,'▼選択','','','',$required);
	$e_subject = '<input type="text" name="subject" value="'.$p_subject.'" '.$required.'>';
	$e_text    = '<textarea          name="text"    class="remarks"        '.$required.'>'.$p_text.'</textarea>';
	$e_send    = '<input type="text" name="send"    value="'.$p_send.'"    '.$required.' id="sSend" size="7">';
	
	
	/*-------- フォーム表示 --------*/
	//▼表示リスト
	$list_head = '<tr><th>配信日</th><th>対象</th><th>件名</th><th>操作</th></tr>';
	
	$input_list = '<table class="input_list">'  ;
	$input_list.= '<tr>'.$list_head.'</tr>';
	$input_list.= $list_in;
	$input_list.= '</table>' ;
	
	
	//▼自動入力要素
	$input_auto = '<input type="hidden" name="act" value="process">';

	//▼削除ボタン
	$button_del = '<input type="submit" class="form_submit" name="act_del" value="削除">';

	//▼登録ボタン
	$input_button = '<input type="submit" class="form_submit" name="act_send" value="この内容で登録する">';
	$input_button.= ($_GET['notice_id'])? $button_del:'';
	$input_button.= '<a class="spc10_l" href="'.$form_action_to.'">クリア</a>';
	
	
	//▼登録フォーム
	$input_form = '<form action="'.$form_action_to.$cont_set.'" method="POST">';
	$input_form.= $input_auto;
	$input_form.= '<table class="input_form">';
	$input_form.= '<tr><th>※対象：</th><td>'.$e_target.'</td></tr>';
	$input_form.= '<tr><th>※件名：</th><td>'.$e_subject.'</td></tr>';
	$input_form.= '<tr><th>※本文：</th><td>'.$e_text.'</td></tr>';
	$input_form.= '<tr><th>※配信日：</th><td>'.$e_send.'</td></tr>';
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
			$('#sSend').datepicker({ dateFormat: 'yy-mm-dd', });
		});
	</script>
	
	<style>
		.in_date{width:80px;}
		.input_form input[type="text"],
		.input_form textarea{padding:3px;}
		
		.m_area .m_list_area .input_list{width:480px;}
		
		.m_area .m_input_area {width:570px;}
		.m_area .m_input_area .m_inner{width:530px;}
		
		.remarks{width:400px; height:200px; resize:none; overflow:auto;}
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
					
					<h2>お知らせ登録</h2>
					<div class="m_area">
						<div class="m_list_area">
							<?php echo $input_list;?>
						</div>
						<div class="m_input_area">
							<div class="m_inner">
								<?php echo $input_form;?>
								<?php echo $err_text;?>
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
