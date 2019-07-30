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

//▼基準通貨
$base_currency = zGetSysSetting('sys_base_currency');


/*-------- リスト取得 --------*/
$cur_ar  = zCurrencyList();		//通貨リスト
$rank_ar = zRankList();			//ランクリスト


/*-------- DB登録 --------*/
if($_POST['act'] == 'process'){
	
	$err = false;
	
	//▼エラーチェック
	if(!$_POST['p_id'])  {$err = true; $err_text.= '<p class="alert">アクティブにするポジションが選択されていません</p>';}
	if(!$_POST['p_rank']){$err = true; $err_text.= '<p class="alert">開始ランクが選択されていません</p>';}
	
	if($err == false){
		$post_data = array(
			'top'     => 'union',
			'sendid'  => $_POST['p_id'],
			'rankid'  => $_POST['p_rank']
		);
		
		//▼別ファイルにポストする
		$bb = nPostData('xml_uni_on_force.php',$post_data);
		
		if($bb == 'ok'){
			//▼終了表示
			$end = 'end';
		}else{
			$err      = true;
			$err_text = '<p class="alert">データの登録に失敗しました</p>';
		}
	}
}


/*-------- 検索条件 --------*/
//search_box
//▼検索条件
$s_name = ($_POST['s_name'])? $_POST['s_name'] : '';
$s_会員ID = ($_POST['s_会員ID'])? $_POST['s_会員ID'] : '';
$s_mail = ($_POST['s_mail'])? $_POST['s_mail'] : '';


//▼名前条件
if($s_name){
	$search_name = "AND((`ui`.`user_name`      LIKE '%".tep_db_input($s_name)."%')OR(`ui`.`user_name2`      LIKE '%".tep_db_input($s_name)."%')";
	$search_name.=   "OR(`ui`.`user_name_kana` LIKE '%".tep_db_input($s_name)."%')OR(`ui`.`user_name_kana2` LIKE '%".tep_db_input($s_name)."%'))";
	
}else{
	$search_name = '';
}

//▼会員ID条件
if($s_会員ID){
	$search_会員ID = "AND `u`.`fs_id` LIKE '%".$s_会員ID."%'";
}else{
	$search_会員ID = "";
}

//▼メールアドレス条件
if($s_会員ID){
	$search_mail = "AND `u`.`user_email` LIKE '%".$s_mail."%'";
}else{
	$search_mail = "";
}


/*----- 顧客リスト -----*/

if($end == 'end'){
	
	$input_active = '<p class="alert">アクティブにしました</p>';
	$input_active.= '<a href="">強制アクティブの登録を続ける</a>';
	
}else{

	//▼情報取得
	$user_query =  tep_db_query("
		SELECT 
			`u`.`user_id`,
			`u`.`fs_id`,
			`u`.`user_email`, 
			CONCAT(`ui`.`user_name`,'　',`ui`.`user_name2`) AS `name`,
			`p`.`position_id`                               AS `position_id`,
			`p`.`position_condition`                        AS `p_condition`,
			`pus`.`p_uni_status_rank_id`                    AS `rank_id`
		FROM      `".TABLE_USER."`           AS `u`
		LEFT JOIN `".TABLE_USER_INFO."`      AS `ui`  ON  `ui`.`user_id` = `u`.`user_id`
		LEFT JOIN `".TABLE_POSITION."`       AS `p`   ON   `p`.`user_id` = `u`.`user_id`
		LEFT JOIN `".TABLE_P_UNI_STATUS."`   AS `pus` ON `pus`.`user_id` = `u`.`user_id`
		WHERE `u`.`state` = '1'
		AND   `u`.`user_permission` = 'u'
		AND   ((`ui`.`state` = '1')OR(`ui`.`state` IS NULL))
		AND   ((`pus`.`state` = '1')OR(`pus`.`state` IS NULL))
		AND   `p`.`state` = '1'
		".$search_会員ID."
		".$search_name."
		".$search_mail."
		ORDER BY `u`.`user_id` DESC
	");

	$st_ar = array('info','addr','ident','ad_certif','certir','buy');

	while($a = tep_db_fetch_array($user_query)){
		
		//▼登録設定
		$p_condition = ($a['p_condition'])? '<span class="ac">Active</span>': '<span class="inac">Inactive</span>';
		$operation   = (!$a['p_condition'])? '<button type="button" class="uniOn" data-pid="'.$a['position_id'].'">アクティブにする</button>':'-';
		
		//▼表示フォーム
		$list_in.= '<tr>';
		$list_in.= '<td>'.$a['user_id'].'</td>';
		$list_in.= '<td>'.$a['name'].'</td>';
		$list_in.= '<td>'.$a['fs_id'].'</td>';
		$list_in.= '<td>'.$a['position_id'].'</td>';
		$list_in.= '<td>'.$p_condition.'</td>';
		$list_in.= '<td>'.(($a['rank_id'])? $rank_ar[$a['rank_id']]:'-').'</td>';
		$list_in.= '<td>'.$a['user_email'].'</td>';
		$list_in.= '<td>'.$operation.'</td>';
		$list_in.= '</tr>';
		
		//▼引継ぎ用
		$j_user_ar[$a['position_id']] = $a;
	}
	
	//▼引継ぎ用
	$jsonUser = json_encode($j_user_ar);
	
	
	//▼表示リスト
	$list_head = '<th>顧客番号</th>';
	$list_head.= '<th>顧客名</th>';
	$list_head.= '<th>会員ID</th>';
	$list_head.= '<th>ポジション番号</th>';
	$list_head.= '<th>状況</th>';
	$list_head.= '<th>ランク</th>';
	$list_head.= '<th>メールアドレス</th>';
	$list_head.= '<th>操作</th>';

	$input_list = '<table class="input_list">'  ;
	$input_list.= '<tr>'.$list_head.'</tr>';
	$input_list.= $list_in;
	$input_list.= '</table>' ;


	/*----- アクティブ登録 -----*/
	//▼表示項目
	$active_head = '<th>顧客番号</th>';
	$active_head.= '<th>顧客名</th>';
	$active_head.= '<th>会員ID</th>';
	$active_head.= '<th>状況</th>';
	$active_head.= '<th>開始ランク</th>';
	$active_head.= '<th>登録</th>';

	$input_rank  = zSelectListSet($rank_ar,'','p_rank','▼ランク','Prank','','required');

	//▼ランク登録ボタン
	$input_auto = '<input type="hidden" name="act"  value="process">';
	$input_auto.= '<input type="hidden" name="p_id" value="" id="Pid">';
	
	$button_rank = '<input type="submit" value="この内容でアクティブにする" id="Act" disabled>';
	$button_rank.= '<a class="spc10_l" href="">クリア</a>'; 

	$active_in = '<td><p id="sUid"  ></p></td>';
	$active_in.= '<td><p id="Uname"></p></td>';
	$active_in.= '<td><p id="会員ID"></p></td>';
	$active_in.= '<td><p id="Pcond"></p></td>';
	$active_in.= '<td>'.$input_rank.'</td>';
	$active_in.= '<td>'.$button_rank.'</td>';


	//▼表示フォーム
	$input_active = '<form action="'.$form_action_to.'" method="POST" id="ActForm">';
	$input_active.= $input_auto;
	$input_active.= '<table class="input_list">';
	$input_active.= '<tr>'.$active_head.'</tr>';
	$input_active.= '<tr>'.$active_in.'</tr>';
	$input_active.= '</table>';
	$input_active.= '</form>';


	/*----- 検索フォーム -----*/
	$search_box = '<div style="margin:10px 0;">';
	$search_box.= '<form name="search" action="'.$form_action_to.'" method="POST">';
	$search_box.= 'お名前・カナ ';
	$search_box.= '<input type="text" style="width:200px; padding:5px 5px;" name="s_name" value="'.$s_name.'"> ';
	$search_box.= '　会員ID ';
	$search_box.= '<input type="text" style="width:100px; padding:5px 5px;" name="s_会員ID" value="'.$s_会員ID.'"> ';
	$search_box.= '<input type="submit" style="width:60px; padding:5px 0px;" value="検索"> ';
	$search_box.= '<input type="button" style="width:60px; padding:5px 0px;" value="リセット" OnClick="window.location.href=\''.$form_action_to.'\'"> ';
	$search_box.= '</form>';
	$search_box.= '</div>';
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
	<style>
		.ac  {color:#00F;font-weight:800;}
		.inac{color:#F00;font-weight:800;}
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
					
					<div>
						<h2>アクティブ登録</h2>
						<div class="active_area">
							<?php echo $err_text;?>
							<?php echo $input_active;?>
						</div>
					</div>
					
					<div class="spc50">
						<h2>顧客一覧</h2>
						<div>
							<?php echo $search_box;?>
							<?php echo $input_list;?>
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

<script src="../js/MyHelper.js"></script>
<script>
	var D = JSON.parse('<?php echo $jsonUser;?>');
	
	$('.uniOn').on('click',function(){
		var aa   = $(this).attr('data-pid');
		var uD   = D[aa];
		var cond = (uD['p_condition'] == 'a')? '<span class="ac">Active</span>':'<span class="inac">Inactive</span>';
		
		//setting
		$('#sUid').html(uD['user_id']);
		$('#Uname').html(uD['name']);
		$('#会員ID').html(uD['fs_id']);
		$('#Pcond').html(cond);
		
		$('#Pid').val(aa);
	});
	
	$('#ActForm').on('change',function(){
		var A = jIsValue('Uid');
		var B = jIsValue('Prank');
		var C = A * B;
		
		var Flag = (C > 0)? false:true;
		
		$('#Act').prop('disabled',Flag);
	});
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
