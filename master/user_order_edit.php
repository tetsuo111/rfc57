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
$link_return    = 'user_order_list.php';
$link_to        = 'user_new_uni_list.php';

//▼オーダーID
$order_id = $_GET['u_order_id'];
$set_cont = '?u_order_id='.$order_id;


/*-------- リスト取得 --------*/
//▼マスターリスト
$cur_ar      = zCurrencyList();		//通貨
$m_rank_ar   = zRankList();			//ランク
$m_point_ar  = zPointList();		//ポイント
$m_detail_ar = zDetailListSys();	//会社内訳
$m_item_ar   = zItemList();			//費用内訳
$m_plan_ar   = zPlanListAr();		//商品　id,name,rank_id

$r_point_ar = zPointRewardList();	//ポイント報酬対象


/*-------- 注文データ復元 --------*/
//▼注文データ
$order_query = tep_db_query("

");


//▼データ取得
if($a = tep_db_fetch_array($order_query)) {
	
}


/*-------- 入力内容 --------*/
$date_payment   = ($_POST['user_order_date_payment'])?   $_POST['user_order_date_payment']  :$a['d_paid'];
$peyment_amount = ($_POST['user_order_peyment_amount'])? $_POST['user_order_peyment_amount']:$a['o_paid_amt'];
$date_figure    = ($_POST['user_order_date_figure'])?    $_POST['user_order_date_figure']   :$a['d_figure'];
$remarks        = ($_POST['user_order_remarks'])?        $_POST['user_order_remarks']       :$a['remarks'];


/*-------- DB登録 --------*/
if(($_POST['act'] == 'process')AND($_POST['act_send'])){
		
		/*----- DB登録 -----*/
		//▼登録データベース
		$db_table = TABLE_USER_ORDER;
		
		//▼登録データ
		$data_array = array(
			'user_order_condition'      => 'a',
			'user_order_date_payment'   => $date_payment,
			'user_order_peyment_amount' => $peyment_amount,
			'user_order_date_figure'    => $date_figure,
			'user_order_remarks'        => (($remarks)? $remarks:'null')
		);
		
		
		/*----- 入金確認メール送信 -----*/
		if(!$a['d_smail']){
			
			/*--- メール送信 ---*/
			//▼送信設定
			$Eemail                 = $user_mail;
			$Eorder_id              = $order_id;
			$Eorder_amount          = $o_amt; 
			$Eorder_limit           = $o_limit;
			$Euser_name             = $user_name;
			$Efs_id                 = $fs_id;
			$Eorder_currency        = $cur;
			$Eorder_currency_rate   = $cur_rate;
			$Eorder_pay_rate_amount = $o_rate_amt;
			$Epaid_amount           = $peyment_amount;
			$Epaid_date             = $date_payment;
			
			
			Email_Payment_Confirm($EmailHead,$EmailFoot,$Eemail,$Eorder_id,$Eorder_amount,$Eorder_limit,$Euser_name,$Efs_id,$Eorder_currency,$Eorder_currency_rate,$Eorder_pay_rate_amount,$Epaid_amount,$Epaid_date);
			
			//▼DB追加用
			$data_array['user_order_date_mail_send'] = 'now()';
			
			
			/*--- ステータス変更 ---*/
			zUserWCStatusUpdate('user_wc_status_buy','a',$user_id);
		}
		
		//▼DB登録
		$w_set = "`user_order_id`='".tep_db_input($order_id)."' AND `state`='1'";
		tep_db_perform($db_table,$data_array,'update',$w_set);
		
		
		/*----- 追加注文 -----*/
		//▼ポイント報酬追加
		if($o_sort == 'b'){
			
			//$r_point_ar	報酬対象ポイント
			//▼現在のポイントを取得　＞指定された計算日で考える
			$query_b = tep_db_query("
				SELECT
					*
				FROM `".TABLE_P_UNI_STATUS."`   AS `ps`
				WHERE `ps`.`state` = '1'
				AND   `ps`.`p_uni_status_ai_id` = (
					SELECT
						`p_uni_status_ai_id`
					FROM  `".TABLE_P_UNI_STATUS."`
					WHERE `state` = '1'
					AND   `position_id` = `ps`.`position_id`
					AND   DATE_FORMAT(`p_uni_status_date_reckon`,'%Y-%m-%d') < '".date('Y-m-d',strtotime($o_figure.' +1 day'))."'
					ORDER BY `p_uni_status_date_reckon` DESC
					LIMIT 1
				)
				AND `ps`.`position_id` = '".tep_db_input($pos_id)."'
			");
			
			if($b = tep_db_fetch_array($query_b)){
				//▼引継ぎ用に余分なデータを削除
				unset($b['p_uni_status_ai_id']);
				unset($b['p_uni_status_id']);
				unset($b['date_create']);
				unset($b['date_update']);
				unset($b['state']);
				
				if($b['p_uni_status_point']){$b_point = zJSToArry($b['p_uni_status_point']);}
			}
			
			
			//▼ポイント毎に追加
			foreach($r_point_ar AS $kpoid => $vn){
				if($o_point[$kpoid]){
					$tmp[$kpoid] = $b_point[$kpoid] + $o_point[$kpoid];
				}
			}
			
			//▼データ登録
			if($tmp){
				
				//▼登録用ポイント
				$jpoint = zToJSText($tmp);
				
				//▼登録データ
				$data_array = array(
					'position_id'              => $pos_id,
					'user_id'                  => $user_id,
					'p_uni_status_point'       => $jpoint,
					'p_uni_status_date_reckon' => $o_figure,
					'date_create'              => 'now()',
					'state'                    => '1'
				);
				
				//▼同じ起算日のデータがある場合には更新
				$query_check = tep_db_query("
					SELECT
						`p_uni_status_id`
					FROM  `".TABLE_P_UNI_STATUS."`
					WHERE `state` = '1'
					AND   `position_id` = '".tep_db_input($pos_id)."'
					AND   DATE_FORMAT(`p_uni_status_date_reckon`,'%Y-%m-%d') = '".date('Y-m-d',strtotime($o_figure))."'
				");
				
				//▼登録設定
				$db_table = TABLE_P_UNI_STATUS;
				$tb_id    = 'p_uni_status_id';
				
				if($c = tep_db_fetch_array($query_check)){
					//▼更新登録
					zDBUpdate($db_table,$data_array,$c[$tb_id]);
					
				}else{
					//▼データ追加
					foreach($data_array AS $k => $v){$b[$k] = ($v)? $v:'null';}			//ないデータを補充
					$sql_data_array = $b;													//登録データ
					
					//▼新規登録
					zDBNewUniqueID($db_table,$sql_data_array,'p_uni_status_ai_id',$tb_id);
				}
			}
		
			//▼終了テキスト
			$end_text = '登録しました';
		}
		
		
		//▼終了処理
		$end = 'end';
	
}else if($_POST['act'] == 'process'){
	
	/*----- エラーチェック -----*/
	$err = ($_POST['act_cancel'])? true:false;
	
	if(!$date_payment)  {$err = true; $err_text.= '<span class="input_alert">入金確認日を入力してください</span>';}
	if(!$peyment_amount){$err = true; $err_text.= '<span class="input_alert">入金金額を入力してください</span>';}
	
	if($err == false){
		$keep = 'readonly';
		$form = 'send';
	}
}


if($end == 'end'){
	
	$list_form = '<p>入金内容を'.$end_text.'</p>';
	$list_form.= '<a href="'.$link_return.'">注文一覧に戻る</a><br>';

}else{
	
	/*-------- フォーム表示 --------*/
	$input_auto = '<input type="hidden" name="act" value="process">';
	
	//▼自動入力要素
	if($form == 'send'){
		//▼登録ボタン
		$input_button = '<input type="submit" class="form_submit"         name="act_send"   value="この内容で登録する">';
		$input_button.= '<input type="submit" class="form_cancel spc10_l" name="act_cancel" value="キャンセル">';
		
		$amt_set_btn  = '';
	}else{
		//▼登録ボタン
		$input_button = '<input type="submit" class="form_submit" value="入力確認">';
		$input_button.= '<a class="spc10_l" href="'.$form_action_to.$set_cont.'">クリア</a>';
		
		//▼値のコピー
		$amt_set_btn  = '<button type="button" class="spc10_l" onClick="jSetPayRateAmt(\''.$o_amt.'\')">支払金額を入力</button>';
	}
	
	//▼入力内容
	$d_paid   = '<input type="text" name="user_order_date_payment"   value="'.$date_payment.'"   id="dPayment" size="9" required '.$keep.'>';
	$paid_amt = '<input type="text" name="user_order_peyment_amount" value="'.$peyment_amount.'" id="paidAmt"  size="9" required '.$keep.' pattern="^[0-9\.]+?">';
	$d_figure = '<input type="text" name="user_order_date_figure"    value="'.$date_figure.'"    id="dFigure"  size="9" required '.$keep.'>'; 
	$remarcs  = '<input type="text" name="user_order_remarks"        value="'.$remarks.'"        style="width:95%;" '.$keep.'>';
	
	//▼入力フォーム
	$list_form = $input_auto;
	$list_form.= '<table class="input_list">';
	$list_form.= $order_list_tr;
	$list_form.= '<tr><th>入金確認日</th><td>'.$d_paid.'</td></tr>';
	$list_form.= '<tr><th>入金金額</th><td>'.$paid_amt.$amt_set_btn.'</td></tr>';
	$list_form.= '<tr><th>計算日</th><td>'.$d_figure.'</td></tr>';
	$list_form.= '<tr><th>メモ</th><td>'.$remarcs.'</td></tr>';
	$list_form.= '</table>';
	$list_form.= '<div class="submit_area">';
	$list_form.= $input_button;
	$list_form.= '</div>';
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
	<link rel="stylesheet" type="text/css" href="../js/jquery-ui/jquery-ui.min.css">
	<link rel="stylesheet" type="text/css" href="../css/master.css"   media="all">
	
	<script src="../js/jquery-3.2.1.min.js"            charset="UTF-8"></script>
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
			if(!Pkeep){
				$('#dPayment').datepicker(dopt);
				$('#dFigure').datepicker(dopt);
			}
		});
		
		function jSetPayRateAmt(Amt){$('#paidAmt').val(Amt);}
	</script>
	<style>
		.m_area .m_list_area_f {}
		
		
		.submit_area{margin-top:20px; text-align:center;}
		.m_area .m_input_area_f {margin-left:20px; padding:0;}
		.m_area .m_input_area_f .m_inner{background:#FAFAFA; margin:0;width:600px;}
		
		.input_list{width:100%;}
		.input_list input{padding:2px 5px;}
		.input_list td{background:#FFF;}
		
		.input_form {width:100%;}
		.input_form td{background:#FFF;}
		.input_form input[type="text"]{width:80%; max-width:100px; text-align:right; padding:1px 5px;}
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
					
					<h2>注文情報</h2>
					<div class="m_area">
						<div class="m_list_area">
							<h3>注文内容</h3>
							<form action="<?php echo $form_action_to.$set_cont;?>" method="POST">
								<?php echo $err_text;?>
								<?php echo $list_form;?>
							</form>
						</div>
						<div class="float_clear"></div>
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
