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
$cur_ar = zCurrencyList();
$base_currency = $cur_ar[0];


/*-------- 検索条件 --------*/
//search_box
//▼検索条件
$s_name = ($_POST['s_name'])? $_POST['s_name'] : '';
$s_fsid = ($_POST['s_fsid'])? $_POST['s_fsid'] : '';
$s_mail = ($_POST['s_mail'])? $_POST['s_mail'] : '';


//▼名前条件
if($s_name){
	$search_name = "AND(`vo`.`u_name` LIKE '%".tep_db_input($s_name)."%')";
	
}else{
	$search_name = '';
}

//▼FSID条件
if($s_fsid){
	$search_fsid = "AND `vo`.`fs_id` LIKE '%".$s_fsid."%'";
}else{
	$search_fsid = "";
}

//▼メールアドレス条件
if($s_fsid){
	$search_mail = "AND `vo`.`u_mail` LIKE '%".$s_mail."%'";
}else{
	$search_mail = "";
}

//▼注文データ
$order_query = tep_db_query("
	SELECT 
		`o`.`user_order_id`        AS `order_id`,
		`o`.`user_order_sort`      AS `o_sort`,
		`o`.`plan_id`              AS `o_plan_id`,
		`o`.`user_order_num`       AS `o_num`,
		`o`.`rank_id`              AS `o_rank_id`,
		`mr`.`m_rank_name`         AS `o_rank_name`,
		`o`.`user_order_amount`    AS `o_amount`,
		`o`.`user_order_condition` AS `o_condition`,
		`o`.`user_order_remarks`   AS `o_remarks`,
		DATE_FORMAT(`o`.`user_order_date_application`,'%Y-%m-%d') AS `d_appli`,
		DATE_FORMAT(`o`.`user_order_date_limit`,'%Y-%m-%d')       AS `d_limit`,
		DATE_FORMAT(`o`.`user_order_date_done`,'%Y-%m-%d')        AS `d_done`,
		DATE_FORMAT(`o`.`user_order_date_mail_send`,'%Y-%m-%d')   AS `d_smail`,
		DATE_FORMAT(`o`.`user_order_date_figure`,'%Y-%m-%d')      AS `d_figure`,
		`o`.`position_id` AS `position_id`,
		`u`.`memberid`    AS `user_id`,
		`u`.`login_id`,
		`u`.`email`,
		`o0`.`recmoney`,
		(CASE WHEN (`u`.`name1` is not null) THEN `u`.`name1` WHEN (`u`.`name2` is not null) THEN `u`.`name2` ELSE NULL end) AS `name`,
		`p`.`position_condition` AS `p_cond`
	FROM `".TABLE_USER_ORDER."` `o` 
	LEFT JOIN `".TABLE_MEM00000."`  `u` ON  `u`.`memberid`  = `o`.`user_id`
	LEFT JOIN `".TABLE_ODR00000."` `o0` ON `o0`.`orderid`   = `o`.`user_order_id`
	LEFT JOIN `".TABLE_M_RANK."`   `mr` ON `mr`.`m_rank_id` = `o`.`rank_id`
	LEFT JOIN `".TABLE_POSITION."`  `p` ON  `p`.`memberid`  = `o`.`user_id`
	WHERE (`o`.`state` = '1')
	AND ((`mr`.`state` = '1') OR (`mr`.`state` IS NULL))
	AND `p`.`state`  = '1'
	".$search_name." 
	".$search_fsid."
	".$search_mail."
	ORDER BY `o`.`user_order_id` DESC 
");


//▼データ取得
if (tep_db_num_rows($order_query)){
	
	//▼基準通貨
	$base_cur = $cur_ar[0];
	
	while ($o = tep_db_fetch_array($order_query)) {
		$order_ar[$o['order_id']]['order'] = $o;
		
		//▼取得用
		$for_get_id.= (($for_get_id)? ",":"")."'".$o['order_id']."'";
		
		//▼引継ぎ用
		$j_order_ar[$o['order_id']] = array(
			'oid'    => $o['order_id'],
			'fsid'   => $o['login_id'],
			'uid'    => $o['user_id'],
			'uname'  => $o['name'],
			'pname'  => $o['o_plan_name'],
			'dappli' => $o['d_appli']
		);
	}
	
	//▼請求取得
	$charge_query = tep_db_query("
		SELECT
			`charge_id`,
			`order_id`,
			`c_currency_name` AS `cur_name`,
			`c_payment_id`    AS `pay_id`,
			`c_amount`,
			`r_amount`,
			`condition`,
			`c_remarks`,
			`c_payment_name` AS `c_name`,
			DATE_FORMAT(`r_date`,'%Y-%m-%d') AS `r_date`
		FROM `".VIEW_CHARGE."`
		WHERE `order_id` IN (".$for_get_id.")
		ORDER BY `order_id`,`c_currency_id`
	");
	
	
	while ($ch = tep_db_fetch_array($charge_query)) {
		$order_ar[$ch['order_id']]['charge'][] = $ch;
	}
	
	
	//----- 表示成形 -----//
	foreach($order_ar AS $o => $o_data){
		
		//▼表示用データ
		$a      = $o_data['order'];
		$charge = $o_data['charge'];
		
		//▼注文状況
		$cl_done = '';
		if($a['o_condition'] == 'a'){
			$condition = '<span class="ok">確認済</span>';
			$cl_done   = 'class="done"';
			
		}else if($a['o_condition'] == 'c'){
			$condition = '<span class="alert">キャンセル</span>';
			
		}else{
			$condition = '入金待';
		}
		
		
		//▼顧客名
		$o_uname = ($a['name'])? $a['name']:'<span class="alert">未登録</span>';
		
		
		//▼入金操作
		//o_sort a：初回　b：追加　c：定期
		$operation = '';
		if(($a['o_sort'] == 'b')AND($a['p_cond'] != 'a')){
			//▼非Activeの追加注文
			$operation = '<div class="name_err">追加購入はActive後に入金確認できます</div>';
			
		}else if(!$a['name']){
			//▼名前の処理
			$operation = '<div class="name_err">個人情報の登録がないため<br>確認できません</div>';
			
		}else{
			
			//▼入金処理
			$rcv_ng     = false;
			$o_paid_amt = '';		//入金済金額
			$c_remarks  = '';
			
			//▼入金金額表示
			if($a['o_condition'] == 'a'){
				
				//▼入金完了
				$o_paid_amt = $a['recmoney'];
				
			}else{
				
				foreach($charge AS $b){
					
					$ch_id = $b['charge_id'];		//請求番号
					
					//▼通貨別入金確認
					if($a['o_condition'] == 'a'){
						
						$operation = '<p class="ok">確認済</p>';
						$o_paid_amt.= '<p>'.$b['r_amount'].' '.$b['cur_name'].'</p>';
						
					}else{
						
						if($b['condition'] == 'a'){
							$cop_txt = '<span class="ok">確認済</span>';
							$o_paid_amt.= '<p>'.$b['r_amount'].' '.$b['cur_name'].'</p>';
						}else{
							$cop_txt = '未確認';
							$rcv_ng  = true;
						}
						
						$operation.= '<p><button type="button" data-id="'.$ch_id.'" class="spc10_l fl_r onShow btn_rcv" onClick="ShowPop();">';
						$operation.= $b['c_amount'].' '.$b['cur_name'].' '.$cop_txt;
						$operation.= '<br>'.$b['c_name'];
						$operation.= '</button></p>';
					}
					
					//▼入金メモ
					$c_remarks.= '<p>'.$b['c_remarks'].'</p>';

					//▼引継ぎ用
					$j_charge_ar[$ch_id] = array(
						'chid'     => $ch_id,
						'oid'      => $a['order_id'],
						'uid'      => $a['user_id'],
						'uname'    => $o_uname,
						'pname'    => $a['o_plan_name'],
						'curname'  => $b['c_name'],
						'camt'     => $b['c_amount'],
						'ramt'     => $b['r_amount'],
						'dappli'   => $a['d_appli'],
						'dreceive' => $b['r_date'],
						'memo'     => $b['c_remarks']
					);
				}
			}
			
			
			//▼全入金確認用
			if($rcv_ng){
				$done = '<span class="alert">未確認有</span>';
			}else{
				
				if($a['o_condition'] == 'a'){
					$done = '<span class="ok">入金<br>完了</span>';
				}else{
					$done = '<button type="button" class="btn_rcval rcvAll" data-id="'.$o.'" onclick="ShowPop();">入金完了処理</button>';
				}
			}
		}
		
		//▼注文種類
		$o_type     = ($a['o_sort'] == 'a')? '<span class="alert">'.$OrderType[$a['o_sort']].'</span>': $OrderType[$a['o_sort']];

		//▼注文金額
		$o_amt = number_format($a['o_amount']);
		
		//▼ビットコイン対策
		/*
			$o_amt      = zCheckNum($a['o_amt']);
			$o_rate_amt = zCheckNum($a['o_rate_amt']);
			$o_paid_amt = zCheckNum($a['o_paid_amt']);
		*/
		
		$order_list_tr.='<tr '.$cl_done.'>';
		$order_list_tr.='<td>'.$a['order_id'].'</td>';
		$order_list_tr.='<td>'.$a['user_id'].'</td>';
		$order_list_tr.='<td>'.$o_uname.'</td>';
		$order_list_tr.='<td>'.$a['login_id'].'</td>';
		$order_list_tr.='<td>'.$o_type.'</td>';
		$order_list_tr.='<td>'.$a['d_appli'].'</td>';
		$order_list_tr.='<td class="num_in">'.$o_amt.' '.$base_currency.'</td>';
		$order_list_tr.='<td>'.$a['d_limit'].'</td>';
		$order_list_tr.='<td class="num_in">'.(($o_paid_amt)? $o_paid_amt:'-').'</td>';
		$order_list_tr.='<td>'.(($a['d_done'])? $a['d_done']:'-').'</td>';
		$order_list_tr.='<td>'.(($a['d_figure'])? $a['d_figure']:'-').'</td>';
		$order_list_tr.='<td>'.$c_remarks.'</td>';
		$order_list_tr.='<td>'.$condition.'</td>';
		$order_list_tr.='<td>'.$operation.'</td>';
		$order_list_tr.='<td>'.$done.'</td>';
		$order_list_tr.='</tr>';
	}
	
}else{
	$order_list_tr = '<tr><td colspan="6">注文の履歴がありません</td></tr>';
}



//▼リスト見出し
$list_head ='<th>注文<br>番号</th>';
$list_head.='<th>顧客<br>番号</th>';
$list_head.='<th>顧客名</th>';
$list_head.='<th>会員ID</th>';
//$list_head.='<th>メールアドレス</th>';
$list_head.='<th>種類</th>';
$list_head.='<th>注文日</th>';
$list_head.='<th>注文金額<br>換算金額</th>';
$list_head.='<th>入金<br>予定日</th>';
$list_head.='<th>入金<br>金額</th>';
$list_head.='<th>入金<br>完了日</th>';
$list_head.='<th>計算日</th>';
$list_head.='<th>メモ</th>';
$list_head.='<th>状況</th>';
$list_head.='<th style="text-align:center;">操作</th>';
$list_head.='<th>完了処理</th>';

//▼表示リスト
$order_list = '<table class="order_list" style="font-size:11px;">';
$order_list.= '<tr>'.$list_head.'</tr>';
$order_list.= $order_list_tr;
$order_list.= '</table>';


//▼検索フォーム
$search_box = '<div style="margin:10px 0;">';
$search_box.= '<form name="search" action="'.$form_action_to.'" method="POST">';
$search_box.= 'お名前・カナ ';
$search_box.= '<input type="text" style="width:200px; padding:5px 5px;" name="s_name" value="'.$s_name.'"> ';
$search_box.= '　会員ID ';
$search_box.= '<input type="text" style="width:100px; padding:5px 5px;" name="s_fsid" value="'.$s_fsid.'"> ';
$search_box.= '<input type="submit" style="width:60px; padding:5px 0px;" value="検索"> ';
$search_box.= '<input type="button" style="width:60px; padding:5px 0px;" value="リセット" OnClick="location.href=\''.$form_action_to.'\'"> ';
$search_box.= '</form>';
$search_box.= '</div>';


//----- Pop -----//
$disabled = 'disabled';

$button_camt = '<button type="button" onclick="ljInCargeAmt();" style="font-size:10px; padding:2px 5px;">請求金額を入力</button>';

//▼登録フォーム
$edit_form = '<div style="margin:0 auto;">';
$edit_form.= '<form name="j_edit_form" id="jEditForm">';
$edit_form.= '<table class="input_form">';
$edit_form.= '<tr><th>注文番号</th>  <td><span id="oNum"  ></span></td></tr>';
$edit_form.= '<tr><th>顧客番号</th>  <td><span id="uID"  ></span></td></tr>';
$edit_form.= '<tr><th>顧客名</th>    <td><span id="uName"  ></span></td></tr>';
$edit_form.= '<tr><th>商品名</th>    <td><span id="pName"  ></span></td></tr>';
$edit_form.= '<tr><th>注文日</th>    <td><span id="oAppli" ></span></td></tr>';
$edit_form.= '<tr><th>請求金額</th>  <td><span id="cAmount"></span></td></tr>';
$edit_form.= '<tr><th>入金金額</th>  <td><input type="text" name="received_amount" size="10" id="amtReceive">'.$button_camt.'</td></tr>';
$edit_form.= '<tr><th>入金完了日</th><td><input type="text" name="d_received"      size="10" id="dReceive"></td></tr>';
$edit_form.= '<tr><th>メモ</th>      <td><input type="text" name="memo" value="" id="jMemo"></td></tr>';
$edit_form.= '</table>';

$edit_form.= '<div class="spc20">';
$edit_form.= '<input type="button" value="編集する"       id="ActSend"   '.$disabled.'>';
//$edit_form.= '<input type="button" value="申込キャンセル" id="ActCancel">';
$edit_form.= '</div>';

$edit_form.= '</form>';
$edit_form.= '</div>';

//--- 完了 ---//
$figure_info = '<p class="alert">入金確認日に関係なく、報酬は「計算日」を元に計算されます</p>';

$done_form = '<div id="jDoneForm">';
$done_form.= '<table class="input_form">';
$done_form.= '<tr><th>注文番号</th>  <td><span id="doNum"></span></td></tr>';
$done_form.= '<tr><th>顧客番号</th>  <td><span id="duID"></span></td></tr>';
$done_form.= '<tr><th>顧客名</th>    <td><span id="duName"></span></td></tr>';
$done_form.= '<tr><th>商品名</th>    <td><span id="dpName"></span></td></tr>';
$done_form.= '<tr><th>注文日</th>    <td><span id="doAppli"></span></td></tr>';
$done_form.= '<tr><th>計算日</th>    <td><input type="text" id="dFigure" value="" size="10"></span>'.$figure_info.'</td></tr>';
$done_form.= '</table>';
$done_form.= '<div class="spc20">';
$done_form.= '<input type="button" value="全入金完了にする" id="ActDone"   '.$disabled.'>';
$done_form.= '</div>';
$done_form.= '</div>';

//▼設定
$p_obj = new mkPop;
$p_obj->subject    = '入金確認';
$p_obj->popcontens = $edit_form.$done_form;

//▼登録ポップ
$pop = $p_obj->getPop();


//▼引継ぎ用
$jsonOdr    = json_encode($j_order_ar);
$jsonCharge = json_encode($j_charge_ar);

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
			var Pkeep = '<?php echo $keep;?>';
			if(!Pkeep){	$('#dReceive').datepicker(dopt);}
			$('#dFigure').datepicker(dopt);
		});
		
		function jSetPayRateAmt(Amt){$('#paidAmt').val(Amt);}
	</script>
	<style>
		.order_list th{line-height:110%;}
		.ok{color:#00F; font-weight:800; text-align:center;}
		.done{background:#E0E0E0;}
		.name_err{background:#F00; color:#FFF; line-height:100%;padding:5px 10px;font-weight:800;}
		
		.btn_rcv  {padding:2px 0; font-size:11px;width:180px;line-height:110%;}
		.btn_rcval{padding:2px 0; font-size:11px;width:70px;color:#00F;font-weight:800;min-height:34px;}
		
		#jDoneForm      {display:none;}
		#jDoneForm.dShow{display:block;}
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
					
					<h2>注文一覧</h2>
					<div>
						<?php echo $search_box;?>
						<?php echo $order_list;?>
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
<script src="../js/popup.js"></script>
<script>
	var DD = JSON.parse('<?php echo $jsonOdr;?>');
	var D  = JSON.parse('<?php echo $jsonCharge;?>');
	var camt = 0;
	var sID  = '';
	var dID  = '';
	var Cat  = new jSendPostDataAj('xml_user_charge_edit.php');
	var Cat1 = new jSendPostDataAj('xml_user_order_recieveall.php');
	
	function  ljInCargeAmt(){$('#amtReceive').val(camt);}
	function jSendEditData(Data){
		var Obj = Cat.sendPost(Data);
		Obj.done(function (jtext) {
			var res = jtext.replace(/\s|　/g,"");
			if(res == 'ok'){alert('登録しました');location.reload();}else{alert('有効なデータがありません');}
		})
		.fail(function (data, textStatus, errorThrown) {alert('登録できません');});
	}
	
	function jResetForm(){
		$('#jDoneForm').removeClass('dShow');
		$('#jEditForm').css('display','block');
		$('#dReceive').val('');
		$('#amtReceive').val('');
		$('#jMemo').val('');
		$('#ActSend').prop('disabled',true);
	}
	
	
	function jCheckForm(){
		var A = $('#dReceive').val();
		var B = $('#amtReceive').val();
		var F = ((A)? 1:0) * ((B)? 1:0);
		return (F > 0)? false : true;
	}
	
	function jCheckDone(){
		A = $('#dFigure').val();
		return (A)? false:true;
	}
	
	$('#jEditForm').on('change',function(){$('#ActSend').prop('disabled',jCheckForm());});
	$('#ActSend').on('click',function(){
		if(confirm('この内容で登録しますか？')){
			dR = $('#dReceive').val();
			aR = $('#amtReceive').val();
			jM = $('#jMemo').val();
			var aData = {top:"send",sendid:sID,damt:aR,drec:dR,memo:((jM)? jM:'')};
			jSendEditData(aData);
		}
	});
	
	$('.onShow').on('click',function(){
		jResetForm();
		sID = $(this).attr('data-id');
		rw = D[sID];
		if(rw){
			camt  = rw.camt * 1;
			lcamt = (rw.camt * 1).toLocaleString();
			$('#Subject').html('【'+sID+'】'+rw.uname);
			$('#pName').html(rw.pname);
			$('#oNum').html(rw.oid);
			$('#uID').html(rw.uid);
			$('#uName').html(rw.uname);
			$('#oAppli').html(rw.dappli);
			$('#cAmount').html(lcamt+rw.curname);
			$('#amtReceive').val(rw.ramt);
			$('#dReceive').val(rw.dreceive);
			$('#jMemo').val(rw.memo);
		}
	});
	
	$('.rcvAll').on('click',function(){
		$('#jEditForm').css('display','none');
		dID = $(this).attr('data-id');
		rd = DD[dID];

		$('#Subject').html('【'+dID+'】'+rd.uname);
		$('#doNum').html(rd.oid);
		$('#duID').html(rd.uid);
		$('#duName').html(rd.uname);
		$('#dpName').html(rd.pname);
		$('#doAppli').html(rd.dappli);
		$('#jDoneForm').addClass('dShow');
		$('#dFigure').val('');
		$('#ActDone').prop('disabled',true);
	});
	
	
	$('#dFigure').on('change',function(){
		$('#ActDone').prop('disabled',jCheckDone());
	});
	
	$('#ActDone').on('click',function(){
		if(confirm('全入金完了にしますか')){
			dFg = $('#dFigure').val();
			aData = {top:'done',sendid:dID,dfigure:dFg};
			Obj1 = Cat1.sendPost(aData);
			Obj1.done(function (jtext) {
				var res = jtext.trim();
				if(res == 'ok'){alert('全入金完了しました');location.reload();}else{alert('有効なデータがありません');}
			})
			.fail(function (data, textStatus, errorThrown) {alert('登録できません');});
		}
	});
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
