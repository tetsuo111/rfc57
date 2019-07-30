<?php 
require('includes/application_top.php');

if(($_COOKIE['master_id']) && ($_COOKIE['master_permission'])){
	$master_id = $_COOKIE['master_id'];
	$head_master_name = $_COOKIE['master_name'].'様';
}else{
	//$head_master_name = 'ゲスト様';
	tep_redirect('logout.php', '', 'SSL');
}


//▼とび先設定
$form_action_to = basename($_SERVER['PHP_SELF']);
$g_number = $_GET['number'];
$cont_set = '?number='.$_GET['number'];


//-------- データ処理 --------//
//▼データ取得
$p_culc  = $_POST['culcdate'];
$p_memo  = $_POST['memo'];
$p_money = ($_POST['money'] == '')? 0:$_POST['money'];


//▼DB登録
if($_POST['act'] == 'process'){
	
	//▼エラーチェック
	$err = false;
	
	if(!$p_culc){$err=true; $err_text='<p class="alert">調整日を登録してください</p>';}
	if(!$p_memo){$err=true; $err_text='<p class="alert">理由などを登録してください</p>';}
	
	if($err == false){
		
		//▼登録設定
		$data_array = array(
			'calcdate' => $p_culc,
			'memo'     => $p_memo,
			'money'    => $p_money
		);
		
		//▼検索設定
		$w_set = "`number`='".tep_db_input($g_number)."'";
		tep_db_perform(TABLE_MEM02002,$data_array,'update',$w_set);
		
		//▼終了処理
		$end = 'end';
	}
}


//-------- 検索設定 --------//
//▼検索条件
$s_name  = ($_POST['s_name'])?  $_POST['s_name']  : '';
$s_memid = ($_POST['s_memid'])? $_POST['s_memid'] : '';
$s_login = ($_POST['s_login'])? $_POST['s_login'] : '';

//▼検索設定
if($_POST['act_search'] == 'search'){
	
	//▼名前条件
	if($s_name){
		$search_name = "AND `name1` LIKE '%".tep_db_input($s_name)."%'";
		
	}else{
		$search_name = '';
	}

	//▼memid条件
	if($s_memid){
		$search_memid = "AND `memberid` LIKE '%".$s_memid."%'";
	}else{
		$search_memid = "";
	}

	//▼login条件
	if($s_login){
		$search_login = "AND `login_id` LIKE '%".$s_login."%'";
	}else{
		$search_login = "";
	}
}


//-------- 表示処理------//
if($end == 'end'){
	
	$input_form = '<p>登録しました</p>';
	$input_form.= '<a href="'.$form_action_to.'">調整金の編集続ける</a>';
	
	$input_list = '';
	
}else{
	
	//▼調整金
	$query = tep_db_query("
		SELECT 
			`number`,
			`memberid`,
			`login_id`,
			`name1`,
			DATE_FORMAT(`calcdate`,'%Y-%m-%d') AS `calcdate`,
			`memo`,
			`money`
		FROM `".TABLE_MEM02002."`
		WHERE `memberid` IS NOT NULL
		".$search_name." 
		".$search_memid."
		".$search_login."
		ORDER BY `calcdate`
	");
	
	//▼データ取得
	if(tep_db_num_rows($query)){
		
		//▼基準通貨
		$cur_ar   = zCurrencyList();
		$base_cur = $cur_ar[0];
		
		while ($a = tep_db_fetch_array($query)) {
			
			//▼操作
			$operation = '<a href="'.$form_action_to.'?number='.$a['number'].'"><button type="button">この調整金を編集する</button></a>';
			
			//▼表示リスト
			$list_in.= '<tr>';
			$list_in.= '<td>'.$a['number'].'</td>';
			$list_in.= '<td>'.$a['memberid'].'</td>';
			$list_in.= '<td>'.$a['login_id'].'</td>';
			$list_in.= '<td>'.$a['name1'].'</td>';
			$list_in.= '<td>'.$a['calcdate'].'</td>';
			$list_in.= '<td>'.$a['memo'].'</td>';
			$list_in.= '<td>'.number_format($a['money']).' '.$base_cur.'</td>';
			$list_in.= '<td>'.$operation.'</td>';
			$list_in.= '</tr>';
			
			//▼修正データ
			if($a['number'] == $g_number){
				$adj = $a;
				
				$p_culc  = $a['calcdate'];
				$p_memo  = $a['memo'];
				$p_money = $a['money'];
			}
		}
		
	}else{
		$list_in = '<tr><td colspan="6">登録された調整金がありません</td></tr>';
	}
	
	
	//▼項目見出し
	$list_head ='<th>処理番号</th>';
	$list_head.='<th>会員番号</th>';
	$list_head.='<th>ログインID</th>';
	$list_head.='<th>氏名</th>';
	$list_head.='<th>調整日（返金処理日）</th>';
	$list_head.='<th>備考（理由など）</th>';
	$list_head.='<th>調整金額</th>';
	$list_head.='<th>操作</th>';
	
	
	//----- 登録フォームリスト -----//
	//▼入力項目
	$i_culc  = '<input type="text"   class="input_text short" name="culcdate" value="'.$p_culc.'" id="cDate" readonly>';
	$i_memo  = '<input type="text"   class="input_text"       name="memo"     value="'.$p_memo.'">';
	$i_money = '<input type="number" class="input_text short" name="money"    value="'.$p_money.'">';
	
	//▼登録ボタン
	$dis = ($g_number)? '':'disabled';
	$input_button = '<input type="submit" name="act_send" value="この内容で登録する" '.$dis.'>';
	$input_button.= '<br><a href="'.$form_action_to.'">クリア</a>';
	
	$input_auto = '<input type="hidden" name="act" value="process">';
	
	
	$fbody_in = '<td>'.$adj['number'].'</td>';
	$fbody_in.= '<td>'.$adj['memberid'].'</td>';
	$fbody_in.= '<td>'.$adj['login_id'].'</td>';
	$fbody_in.= '<td>'.$adj['name1'].'</td>';
	$fbody_in.= '<td>'.$i_culc.'</td>';
	$fbody_in.= '<td>'.$i_memo.'</td>';
	$fbody_in.= '<td>'.$i_money.'</td>';
	$fbody_in.= '<td>'.$input_button.'</td>';
	
	
	//▼登録フォーム
	$input_form = '<form action="'.$form_action_to.$cont_set.'" method="POST">';
	$input_form.= $input_auto;
	$input_form.= '<table class="input_form">';
	$input_form.= '<tr>'.$list_head.'</tr>';
	$input_form.= '<tr>'.$fbody_in.'</tr>';
	$input_form.= '</table>';
	$input_form.= '</form>';
	$input_form.= $err_text;
	
	
	//----- 表示リスト -----//
	//▼調整金一覧
	$adj_list = '<table class="input_list" style="font-size:11px;">';
	$adj_list.= '<tr>'.$list_head.'</tr>';
	$adj_list.= $list_in;
	$adj_list.= '</table>';
	
	
	//▼検索フォーム

	$search_box = '<div class="spc10">';
	$search_box.= '<form name="search" action="'.$form_action_to.'" method="POST">';
	$search_box.= '<input type="hidden" name="act_search" value="search">';
	$search_box.= 'お名前・カナ ';
	$search_box.= '<input type="text" style="width:200px; padding:5px 5px;" name="s_name"  value="'.$s_name.'"> ';
	$search_box.= '　会員ID ';
	$search_box.= '<input type="text" style="width:100px; padding:5px 5px;" name="s_memid" value="'.$s_memid.'"> ';
	$search_box.= '　ログインID ';
	$search_box.= '<input type="text" style="width:100px; padding:5px 5px;" name="s_login" value="'.$s_login.'"> ';
	$search_box.= '<input type="submit" style="width:60px; padding:5px 0px;" value="検索"> ';
	$search_box.= '<input type="button" style="width:60px; padding:5px 0px;" value="リセット" OnClick="location.href=\''.$form_action_to.'\'"> ';
	$search_box.= '</form>';
	$search_box.= '</div>';
	
	
	//----- 表示一覧 -----//
	$input_list = '<h2>調整金一覧</h2>';
	$input_list.= '<div class="spc20">';
	$input_list.= $search_box;
	$input_list.= '<div class="spc20">';
	$input_list.= $adj_list;
	$input_list.= '</div>';
	$input_list.= '</div>';
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
	<meta name="format-detection" content="elogin=no">
	<link rel="stylesheet" type="text/css" href="../css/cssreset.css" media="all">
	<link rel="stylesheet" type="text/css" href="../css/common.css"   media="all">
	<link rel="stylesheet" type="text/css" href="../js/jquery-ui/jquery-ui.min.css">
	<link rel="stylesheet" type="text/css" href="../css/master.css"   media="all">
	<script src="../js/jquery-3.2.1.min.js" charset="UTF-8"></script>
	<script src="../js/jquery-migrate-1.4.1.min.js"   charset="UTF-8"></script>
	<script src="../js/jquery-ui/jquery-ui.min.js"    charset="UTF-8"></script>
	<script type="text/javascript">
			var opmonth = ["1","2","3","4","5","6","7","8","9","10","11","12"];
			var opday   = ["日","月","火","水","木","金","土"];
			var dopt ={
				dateFormat :'yy-mm-dd',
				changeMonth:true,
				monthNames:opmonth,monthNamesShort:opmonth,
				dayNames:opday,dayNamesMin:opday,dayNamesShort:opday,
				showMonthAfterYear:true
			}
			
			$(function() {
				var Pkeep = '';
				if(!Pkeep){	$('#dReceive').datepicker(dopt);}
				$('#cDate').datepicker(dopt);
			});
	</script>
	<style>
		.input_form {width:100%;}
		.input_list {width:100%;}
		.input_list th{text-align:center;}
		.input_text.short{width:70px;}
	</style>
</head>
<body id="body">
<div id="wrapper">
	<?php echo $pop;?>
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
					
					<h2>調整金編集</h2>
					<div>
						<?php echo $input_form;?>
					</div>
					<div class="spc50">
						<?php echo $input_list;?>
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
