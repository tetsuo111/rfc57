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
$link_to        = 'user_order_edit.php';


/*-------- リスト取得 --------*/
//▼通貨リスト
$cur_ar   = zCurrencyList();
$base_cur = $cur_ar[0];



/*-------- 検索条件 --------*/
//search_box
//▼検索条件
$s_name = ($_POST['s_name'])? $_POST['s_name'] : '';
$s_fsid = ($_POST['s_fsid'])? $_POST['s_fsid'] : '';
$s_mail = ($_POST['s_mail'])? $_POST['s_mail'] : '';
$d_from = $_POST['d_from'];
$d_to   = $_POST['d_to'];

//▼登録配列
$sales_ar[] = array('name'=>'注文金額');
$sales_ar[] = array('name'=>'証拠金');

if($_POST['act'] == 'process'){
	
	//▼名前条件
	if($s_name){
		$search_name = "AND((`m0`.`name1` LIKE '%".tep_db_input($s_name)."%')OR(`m0`.`name2` LIKE '%".tep_db_input($s_name)."%'))";
		
	}else{
		$search_name = '';
	}

	//▼FSID条件
	if($s_fsid){
		$search_fsid = "AND `o0`.`memberid` LIKE '%".$s_fsid."%'";
	}else{
		$search_fsid = "";
	}

	//▼メールアドレス条件
	if($s_fsid){
		$search_mail = "AND `m0`.`email` LIKE '%".$s_mail."%'";
	}else{
		$search_mail = "";
	}


	//▼期間
	if(($d_from)OR($d_to)){
		
		if(($d_from)AND($d_to)){
			$search_date = "AND DATE_FORMAT(`o0`.`calcdate`,'%Y-%m-%d') BETWEEN '".$d_from."' AND '".$d_to."'";
		}else if($d_from){
			$search_date = "AND DATE_FORMAT(`o0`.`calcdate`,'%Y-%m-%d') > '".date('Y-m-d',strtotime($d_from.' -1 day'))."'";
		}else if($d_to){
			$search_date = "AND DATE_FORMAT(`o0`.`calcdate`,'%Y-%m-%d') < '".date('Y-m-d',strtotime($d_to.' +1 day'))."'";
		}
	}
	
	//▼条件管理
	if(
		(!$s_name)
		AND(!$s_fsid)
		AND(!$s_fsid)
		AND(!$d_from)AND(!$d_to)
	){
		$err = true;
		$err_text = '<p class="alert">検索条件を入力してください</p>';
	}
	
	
	if($err == false){
		
		/*----- 注文情報取得 -----*/
		//▼注文データ
		$order_query = tep_db_query("
			SELECT
				IFNULL(`m0`.name1,`m0`.`name2`) AS `name`,
				`m0`.`login_id`,
				`o0`.`orderid`,
				`o0`.`memberid`,
				`o0`.`orderdate`,
				`o0`.`calcdate`,
				`o0`.`ordertype`,
				`o0`.`sumitem_intax`,
				`o0`.`adjust`,
				`o0`.`sumprice`,
				`o0`.`sumpoint`,
				`o0`.`recdate`,
				`o0`.`recmoney`
			FROM       `".TABLE_ODR00000."` `o0`
			LEFT JOIN  `".TABLE_MEM00000."` `m0` ON `m0`.`memberid` = `o0`.`memberid`
			WHERE `o0`.`candate` IS NULL
			".$search_name." 
			".$search_fsid."
			".$search_mail."
			".$search_date."
			ORDER BY `o0`.`orderdate` DESC 
		");


		//▼データ取得
		if (tep_db_num_rows($order_query) ) {
			
			while ($o = tep_db_fetch_array($order_query)) {

				$order_list_tr.='<tr>';
				$order_list_tr.='<td>'.$o['orderid'].'</td>';
				$order_list_tr.='<td>'.$o['memberid'].'</td>';
				$order_list_tr.='<td>'.$o['name'].'</td>';
				$order_list_tr.='<td>'.$o['loginid'].'</td>';
				$order_list_tr.='<td>'.$o_type.'</td>';
				$order_list_tr.='<td>'.$o['orderdate'].'</td>';
				$order_list_tr.='<td class="num_in">'.number_format($o['sumitem_intax']).' '.$base_cur.'</td>';
				$order_list_tr.='<td class="num_in">'.number_format($o['adjust']).' '.$base_cur.'</td>';
				$order_list_tr.='<td class="num_in">'.number_format($o['sumprice']).' '.$base_cur.'</td>';
				$order_list_tr.='<td class="num_in">'.number_format($o['recmoney']).' '.$base_cur.'</td>';
				$order_list_tr.='<td>'.$o['recdate'].'</td>';
				$order_list_tr.='<td>'.$o['sumpoint'].'</td>';
				$order_list_tr.='<td>'.$o['calcdate'].'</td>';
				$order_list_tr.='</tr>';
				
				//▼合計
				$order_total += $o['sumprice'];
				$reciev_total+= $o['recmoney'];
				$genshi_total+= $o['sumpoint'];
			}

		}else{
			$order_list_tr = '<tr><td colspan="6">注文の履歴がありません</td></tr>';
		}
	}
}


//▼合計金額
$total_in = '<td class="num_in">'.number_format($order_total).' '.$base_cur.'</td>';
$total_in.= '<td class="num_in">'.number_format($reciev_total).' '.$base_cur.'</td>';
$total_in.= '<td class="num_in">'.number_format($genshi_total).' '.$base_cur.'</td>';


$sales_head = '<th>注文合計</th>';
$sales_head.= '<th>入金合計</th>';
$sales_head.= '<th>原資合計</th>';

$sales_total.= '<table class="order_list spc20">';
$sales_total.= '<tr>'.$sales_head.'</tr>';
$sales_total.= '<tr>'.$total_in.'</tr>';
$sales_total.= '</table>';


//▼リスト見出し
$list_head ='<th>注文番号</th>';
$list_head.='<th>顧客番号</th>';
$list_head.='<th>顧客名</th>';
$list_head.='<th>会員ID</th>';
$list_head.='<th>種類</th>';
$list_head.='<th>注文日</th>';
$list_head.='<th>注文金額</th>';
$list_head.='<th>調整金</th>';
$list_head.='<th>合計金額</th>';
$list_head.='<th>入金金額</th>';
$list_head.='<th>入金日</th>';
$list_head.='<th>コミッション原資</th>';
$list_head.='<th>計算日</th>';


//▼表示リスト
$order_list = '<table class="order_list" style="font-size:11px; width:100%;">';
$order_list.= '<tr>'.$list_head.'</tr>';
$order_list.= $order_list_tr;
$order_list.= '</table>';


//▼検索フォーム
$from = '<input type="text" name="d_from" style="padding:5px;" size="6" value="'.$d_from.'" id="dFrom" required readonly>';
$to   = '<input type="text" name="d_to"   style="padding:5px;" size="6" value="'.$d_to.'"   id="dTo"   required readonly>';

$search_box = '<div style="margin:10px 0;">';
$search_box.= '<form name="search" action="'.$form_action_to.'" method="POST">';
$search_box.= '<input type="hidden" name="act" value="process">';
$search_box.= '期間：'.$from.'～'.$to;
$search_box.= '　お名前・カナ ';
$search_box.= '<input type="text" style="width:200px; padding:5px 5px;" name="s_name" value="'.$s_name.'"> ';
$search_box.= '　会員ID ';
$search_box.= '<input type="text" style="width:100px; padding:5px 5px;" name="s_fsid" value="'.$s_fsid.'"> ';
$search_box.= '<input type="submit" style="width:60px; padding:5px 0px;" value="検索"> ';
$search_box.= '<input type="button" style="width:60px; padding:5px 0px;" value="リセット" OnClick="window.location.href=\''.$form_action_to.'\'"> ';
$search_box.= '</form>';
$search_box.= '</div>';

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
	
	<script src="../js/jquery-3.2.1.min.js" charset="UTF-8"></script>
	<script src="../js/jquery-migrate-1.4.1.min.js" charset="UTF-8"></script>
	<script src="../js/jquery-ui/jquery-ui.min.js"  charset="UTF-8"></script>
	<script type="text/javascript">
		var mName    = ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'];
		var dName    = ['日', '月', '火', '水', '木', '金', '土'];
		var cOption  = { dateFormat: 'yy-mm-dd',monthNames:mName,monthNamesShort:mName,dayNames:dName,dayNamesMin:dName,changeMonth:true,showMonthAfterYear:true};
		$(function() {
			$('#dFrom').datepicker(cOption);
			$('#dTo').datepicker(cOption);
		});
	</script>
	<style>
		.order_list th{line-height:110%;}
		.ok{color:#00F; font-weight:800;}
		.name_err{background:#F00; color:#FFF; line-height:100%;padding:5px 10px;font-weight:800;}
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
					<?php echo $search_box;?>
					<?php echo $err_text;?>
					<div class="spc20">
						<h2>合計金額</h2>
						<div>
							<?php echo $sales_total;?>
						</div>
					</div>
					
					<div class="spc50">
						<h2>入金済注文一覧</h2>
						<div>
							<?php echo $order_list;?>
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
