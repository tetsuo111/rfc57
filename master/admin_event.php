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
$cont_set       = ($_GET['event_id'])? '?event_id='.$_GET['event_id']:'';


//▼値取得
$event_id     = $_GET['event_id'];

$NowDay1 = date( "Y-m-d", time() );
$NowDay2 = date( "Y/m/d", time() );

//▼データ取得
$p_name    = $_POST['name'];
$p_owner   = $_POST['owner'];
$p_date    = $_POST['date'];
$p_open    = $_POST['open'];
$p_start   = $_POST['start'];
$p_end     = $_POST['end'];
$p_area    = $_POST['area'];
$p_number  = $_POST['number'];
$p_place   = $_POST['place'];
$p_explain = $_POST['explain'];
$p_url     = $_POST['eurl'];
$p_target = $_POST['target'];
$p_send    = $_POST['send'];
$p_close    = $_POST['close'];


/*-------- データ処理 --------*/
if($_POST['act'] == 'process'){
	
	/*----- エラーチェック -----*/
	$err = false;
	
	if(!$p_name)  {$err = true; $err_text.= '<p class="alert">タイトルを入力してください</p>';}
	if(!$p_owner) {$err = true; $err_text.= '<p class="alert">主催者を入力してください</p>';}
	if(!$p_date)  {$err = true; $err_text.= '<p class="alert">日時を入力してください</p>';}
	if(!$p_open)  {$err = true; $err_text.= '<p class="alert">受付開始を入力してください</p>';}
	if(!$p_start) {$err = true; $err_text.= '<p class="alert">開始時間を入力してください</p>';}
	if(!$p_end)   {$err = true; $err_text.= '<p class="alert">終了時間を入力してください</p>';}
	if(!$p_area)  {$err = true; $err_text.= '<p class="alert">地域を入力してください</p>';}
	if(!$p_place) {$err = true; $err_text.= '<p class="alert">会場を入力してください</p>';}
	
	
	//▼表示設定
	if($err == false){
		
		/*----- 初期設定 -----*/
		$db_table = TABLE_A_EVENT;		//登録DB設定
		$t_ai_id  = 'a_event_ai_id';	//自動登録ID
		$t_id     = 'a_event_id';		//テーブルID
		
		
		//▼登録チェック
		$query_check = tep_db_query("
			SELECT 
				`".$t_id."`
			FROM  `".$db_table."`
			WHERE `state` = '1'
			AND   `".$t_id."` = '".tep_db_input($event_id)."'
		");
		
		
		//▼登録情報
		$sql_data_array = array(
			'a_event_name'       => $p_name,
			'a_event_owner'      => $p_owner,
			'a_event_area'       => $p_area,
			'a_event_place'      => $p_place,
			'a_event_number'     => zSetNull($p_number),
			'a_event_explain'    => zSetNull($p_explain),
			'a_event_url'        => zSetNull($p_url),
			'a_event_date'       => $p_date,
			'a_event_time_open'  => $p_open,
			'a_event_time_start' => $p_start,
			'a_event_time_end'   => $p_end,
			'a_event_date_send'   => $p_send,
			'a_event_date_close'   => $p_close,
			'date_create'        => 'now()',
			'state'              => '1'
		);
		
		
		/*----- DB登録 -----*/
		if($_POST['act_del']){
			
			$del_array = array(
				'date_update' => 'now()',
				'state'       => 'z'
			);
			
			$w_set = "`".$t_id."`='".$event_id."' AND `state`='1'";
			tep_db_perform($db_table,$del_array,'update',$w_set);

			//紐付けレコードの削除
			tep_db_query("DELETE FROM tag00000 WHERE article_type=3 AND article_id='$event_id'");

			//▼終了テキスト
			$end_text = '削除しました';
		
		}else{
			
			if($b = tep_db_fetch_array($query_check)){
				//更新登録
				zDBUpdate($db_table,$sql_data_array,$b[$t_id]);

			}else{
				//新規登録
				$event_id = zDBNewUniqueID($db_table,$sql_data_array,$t_ai_id,$t_id);
			}
			
			tep_db_tagging('3',$event_id,$p_target);
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
			`a_event_id`      AS `id`,
			`a_event_name`    AS `name`,
			`a_event_owner`   AS `owner`,
			`a_event_area`    AS `area`,
			`a_event_place`   AS `place`,
			`a_event_number`  AS `number`,
			`a_event_url`     AS `url`,
			`a_event_explain` AS `explain`,
			WEEKDAY(`a_event_date`) AS event_week,
			DATE_FORMAT(`a_event_date`,       '%Y-%m-%d') AS event_date,
			DATE_FORMAT(`a_event_time_open`,  '%H:%i')    AS event_open,
			DATE_FORMAT(`a_event_time_start`, '%H:%i')    AS event_start,
			DATE_FORMAT(`a_event_time_end`,   '%H:%i')    AS event_end,
			DATE_FORMAT(`a_event_date_send`,'%Y-%m-%d') AS `send`,
			DATE_FORMAT(`a_event_date_close`,'%Y-%m-%d') AS `close`
		FROM `".TABLE_A_EVENT."` 
		WHERE `state` = '1' 
		AND   `a_event_date` >= '".tep_db_input($NowDay)."' 
		ORDER BY `a_event_date` ASC , `a_event_id` ASC 
	");
	
	
	//▼既存のイベントを取得
	while($a = tep_db_fetch_array($query)) {

		//対象
		$query2 = tep_db_query("SELECT * FROM tag00000 INNER JOIN typ01004 ON tag00000.bctype = typ01004.id WHERE article_type=3 AND article_id='".$a['id']."' ORDER BY bctype ASC");

		$event_target_array = [];
		$event_target_array[$a['id']] = '';
		while($b = tep_db_fetch_array($query2)) {

			$event_target_array[$a['id']] .= $event_target_array[$a['id']]==''?$b['tag']:','.$b['tag'];


			if($a['id'] == $event_id){
				$p_target[] = $b['bctype'];
			}
		}


		//操作
		$operation = '<a href="'.$form_action_to.'?event_id='.$a['id'].'">編集する</a>';
		
		$list_in.= '<tr>';
		$list_in.= '<td>'.$a['name'].'</td>';
		$list_in.= '<td>'.$a['event_date'].'</td>';
		$list_in.= '<td>'.$a['event_start'].'～'.$a['event_end'].'</td>';
		$list_in.= '<td>'.$a['place'].'</td>';
		$list_in.= '<td>'.$event_target_array[$a['id']].'</td>';
		$list_in.= '<td>'.$operation.'</td>';
		$list_in.= '</tr>';
		
		if($a['id'] == $event_id){
			$p_name    = $a['name'];
			$p_owner   = $a['owner'];
			$p_number  = $a['number'];
			$p_date    = $a['event_date'];
			$p_open    = $a['event_open'];
			$p_start   = $a['event_start'];
			$p_end     = $a['event_end'];
			$p_area    = $a['area'];
			$p_place   = $a['place'];
			$p_explain = $a['explain'];
			$p_send = $a['send'];
			$p_close = $a['close'];
			$p_url     = $a['url'];
		}
	}
}



/*----- 表示フォーム -----*/
if($end == 'end'){
	
	$input_form = '<p>'.$end_text.'</p>';
	$input_form.= '<a href="'.$form_action_to.'">イベントの登録を続ける</a>';
	
}else{

	/*-------- フォーム表示 --------*/
	//▼入力必須
	$required     = 'required';
	
	//▼入力項目
	$sel_date  = '<input  name="date" type="text" style="width:100px;" value="'.$p_date.'" id="sDate" '.$required.'>';
	$sel_open  = zSelectListSet($TimeArray,$p_open ,'open' ,'▼選択','','','',$required);
	$sel_start = zSelectListSet($TimeArray,$p_start,'start','▼選択','','','',$required);
	$sel_end   = zSelectListSet($TimeArray,$p_end  ,'end'  ,'▼選択','','','',$required);
	$sel_area  = zSelectListSet($AreaArray,$p_area ,'area' ,'▼選択','','','',$required);

	$e_name    = '<input name="name"       type="text" value="'.$p_name.'"  '.$required.'>';					//タイトル
	$e_owner   = '<input name="owner"      type="text" value="'.$p_owner.'" '.$required.'>';					//主催者
	$e_number  = '<input name="number"     type="text" value="'.$p_number.'">';								//定員
	$e_date    = $sel_date.'　受付開始：'.$sel_open.'　'.$sel_start.' ～ '.$sel_end;							//日付
	$e_area    = $sel_area;																					//地域
	$e_place   = '<input name="place"      type="text" value="'.$p_place.'" '.$required.'>';					//会場
	$e_explain = '<textarea name="explain" type="text" class="remarks">'.$p_explain.'</textarea>';			//説明
	$e_url     = '<input name="eurl"       type="text" value="'.$p_url.'"> ※http から入力してください';		//URL
	$e_send    = '<input type="text" name="send"    value="'.$p_send.'"    '.$required.' id="sSend" size="10">';
	$e_close    = '<input type="text" name="close"    value="'.$p_close.'"    '.$required.' id="sClose" size="10">';

	
	/*-------- フォーム表示 --------*/
	//▼表示リスト
	$list_head = '<th>タイトル</th>';
	$list_head.= '<th>日付</th>';
	$list_head.= '<th>時間</th>';
	$list_head.= '<th>会場</th>';
	$list_head.= '<th>対象</th>';
	$list_head.= '<th>操作</th>';
	
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
	$input_button.= ($_GET['event_id'])? $button_del:'';
	$input_button.= '<a class="spc10_l" href="'.$form_action_to.'">クリア</a>';


	//対象区分
	$query =  tep_db_query("SELECT * FROM typ01004 ORDER BY id asc");

	$target_array = [];
	while($a = tep_db_fetch_array($query)) {

		$target_array[$a['id']] = $a['tag'];

	}

	//▼入力項目(チェックボックスに変更）
	$e_target  = zCheckboxSet2($target_array,$p_target ,'target[]');


	//▼登録フォーム
	$input_form = '<form action="'.$form_action_to.$cont_set.'" method="POST">';
	$input_form.= $input_auto;
	$input_form.= '<table class="input_form">';
	$input_form.= '<tr><th>※タイトル</th><td>'.$e_name.'</td></tr>';
	$input_form.= '<tr><th>※主催者</th><td>'.$e_owner.'</td></tr>';
	$input_form.= '<tr><th>　定　員</th><td>'.$e_number.'</td></tr>';
	$input_form.= '<tr><th>※日　時</th><td>'.$e_date.'</td></tr>';
	$input_form.= '<tr><th>※地　域</th><td>'.$e_area.'</td></tr>';
	$input_form.= '<tr><th>※会　場</th><td>'.$e_place.'</td></tr>';
	$input_form.= '<tr><th>　説　明</th><td>'.$e_explain.'</td></tr>';
	$input_form.= '<tr><th>　URL</th><td>'.$e_url.'</td></tr>';
	$input_form.= '<tr><th>※対象</th><td>'.$e_target.'</td></tr>';
	$input_form.= '<tr><th>※配信日：</th><td>'.$e_send.'</td></tr>';
	$input_form.= '<tr><th>※終了日：</th><td>'.$e_close.'</td></tr>';

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
			$('#sDate').datepicker({ dateFormat: 'yy-mm-dd', });
			$('#sSend').datepicker({ dateFormat: 'yy-mm-dd', });
			$('#sClose').datepicker({ dateFormat: 'yy-mm-dd', });
		});

	</script>
	
	<style>
		.in_date{width:80px;}
		.input_form input[type="text"],
		.input_form textarea{padding:3px;}
		
		.m_area .m_list_area .input_list{width:480px;}
		
		.m_area .m_input_area {width:570px;}
		.m_area .m_input_area .m_inner{width:530px;}
		#wrapper{
			min-height:140%;
		}
		
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
					
					<h2>イベント登録</h2>
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
	
<!--	<div id="footer">-->
<!--		--><?php //require('inc_master_footer.php'); ?>
<!--	</div>-->
</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
