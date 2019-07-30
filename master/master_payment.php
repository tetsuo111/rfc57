<?php 
require('includes/application_top.php');

if(($_COOKIE['master_id']) && ($_COOKIE['master_permission'])){
	$master_id = $_COOKIE['master_id'];
	$head_master_name = $_COOKIE['master_name'].'様';
}else{
	//$head_master_name = 'ゲスト様';
	tep_redirect('logout.php', '', 'SSL');
}


//-------- 全体設定 --------//
//▼とび先設定
$form_action_to = basename($_SERVER['PHP_SELF']);

//▼リスト取得
$cur_ar   = zCurrencyList();			//通貨
$show_ar  = array('a' => '銀行口座');	//表示情報


//★
$payment_id = $_GET['m_payment_id'];
$cont_set   = '?m_payment_id='.$payment_id;


//▼変数設定
$p_code     = $_POST['m_payment_code'];
$p_name     = $_POST['m_payment_name'];
$p_show     = $_POST['m_payment_show'];
$p_info     = $_POST['m_payment_info'];
$p_instruct = $_POST['m_payment_instruct'];
$p_culcid   = $_POST['m_payment_culc_id'];
$p_base     = $_POST['m_payment_base_date'];

$p_cur  = $_POST['cur'];	//checkbox配列
$culc   = $_POST['culc'];	//手数料配列



//-------- データ処理 --------//
if($_POST['act'] == 'process'){
	
	//----- エラーチェック -----//
	$err = false;
	
	//★表示名
	if(!$p_code)    {$err = true; $err_text.= '<span class="alert">識別文字列を入力してください</span>';}
	if(!$p_name)    {$err = true; $err_text.= '<span class="alert">支払方法を入力してください</span>';}
	if(!$p_cur)     {$err = true; $err_text.= '<span class="alert">対応通貨を指定してください</span>';}
	if(!$p_instruct){$err = true; $err_text.= '<span class="alert">支払メールを入力してください</span>';}
	
	//▼表示設定
	if(($err == false)AND(!$_POST['act_cancel'])){    //エラーなし
		
		//----- 初期設定 -----//
		//★
		$db_table = TABLE_M_PAYMENT;		//登録DB設定
		$t_ai_id  = 'm_payment_ai_id';		//自動登録ID
		$t_id     = 'm_payment_id';			//テーブルID
		
		//▼登録チェック
		$query_check = tep_db_query("
			SELECT 
				`".$t_id."`
			FROM  `".$db_table."`
			WHERE `state` = '1'
			AND   `".$t_id."` = '".tep_db_input($payment_id)."'
		");
		
		
		$p_target_in = '-'.implode('-',$p_cur).'-';

		
		//----- 情報登録 -----//
		//★登録情報
		$sql_data_array = array(
			'm_payment_code'      => $p_code,
			'm_payment_name'      => $p_name,
			'm_payment_target'    => $p_target_in,
			'm_payment_show'      => zSetNull($p_show),
			'm_payment_info'      => zSetNull($p_info),
			'm_payment_instruct'  => $p_instruct,
			'm_payment_culc_id'   => $p_culcid,
			'm_payment_base_date' => $p_base,
			'date_create'         => 'now()',
			'state'               => '1'
		);
		
		//▼検索設定
		$w_set = "`".$t_id."`='".$payment_id."' AND `state`='1'";
		
		
		//----- DB登録 -----//
		if($_POST['act_del']){
			
			$del_array = array('date_update' => 'now()','state' => 'z');
			tep_db_perform($db_table,$del_array,'update',$w_set);
			
			//▼終了テキスト
			$end_text = '削除しました';
		
		}else{
			
			if ($payment_id){
				//更新登録
				tep_db_perform($db_table,$sql_data_array,'update',$w_set);

			}else{
				//新規登録
				$payment_id = zDBNewUniqueID($db_table,$sql_data_array,$t_ai_id,$t_id);
			}
			
			
			//▼手数料登録
			if($culc){
				
				$db_fee   = TABLE_M_PAYMENT_FEE;
				$wfee_set = "`state` = '1' AND `payment_id` = '".tep_db_input($payment_id)."'";
				
				//▼過去の設定を無効化
				$query_check = tep_db_query("
					SELECT 
						`m_payment_fee_ai_id`
					FROM  `".$db_fee."`
					WHERE ".$wfee_set);
				
				if(tep_db_num_rows($query_check)){
					$old_ar = array(
						'date_update' => 'now()',
						'state'       => (($_POST['act_del'])? 'z' : 'y')
					);
					tep_db_perform($db_fee,$old_ar,'update',$wfee_set);
				}
				
				//▼新規登録
				foreach($culc AS $cdt){
					
					$data_ar = array(
						'payment_id'         => $payment_id,
						'culc_id'            => $cdt['id'],
						'm_payment_fee_name' => $cdt['name'],
						'date_create'        => 'now()',
						'state'              => '1'
					);
					
					tep_db_perform($db_fee,$data_ar);
				}
			}
			
			//▼終了テキスト
			$end_text = '登録しました';
		} 
		
		//▼終了処理
		$end = 'end';
	}
	
}else{
	$err_text = '<p class="alert">利用する支払方法をここに登録</p>';
}


//----- 表示フォーム -----//
if($end == 'end'){
	
	$input_form = '<p>'.$end_text.'</p>';
	$input_form.= '<a href="'.$form_action_to.'">登録を続ける</a>';
	
}else{

	//----- 表示リスト -----//
	//▼支払方法
	$query =  tep_db_query("
		SELECT
			`m_payment_id`        AS `id`,
			`m_payment_code`      AS `code`,
			`m_payment_name`      AS `name`,
			`m_payment_target`    AS `target`,
			`m_payment_show`      AS `show`,
			`m_payment_info`      AS `info`,
			`m_payment_instruct`  AS `instruct`,
			`m_payment_culc_id`   AS `culc_id`,
			`m_payment_base_date` AS `base_date`,
			`m_payment_condition` AS `condition`
		FROM  `".TABLE_M_PAYMENT."`
		WHERE `state` = '1'
		ORDER BY `m_payment_code` ASC
	");

	while($a = tep_db_fetch_array($query)) {
		$operation = '<a href="'.$form_action_to.'?m_payment_id='.$a['id'].'"><button type="button">編集する</button></a>';

		$list_in.= '<tr>';
		$list_in.= '<td>'.$a['id'].'</td>';
		$list_in.= '<td>'.$a['code'].'</td>';
		$list_in.= '<td>'.$a['name'].'</td>';
		$list_in.= '<td>'.$operation.'</td>';
		$list_in.= '</tr>';
		
		if($a['id'] == $payment_id){
			//▼値の取得
			$p_code     = $a['code'];
			$p_name     = $a['name'];
			$p_show     = $a['show'];
			$p_info     = $a['info'];
			$p_target   = $a['target'];
			$p_instruct = $a['instruct'];
			$p_culcid   = $a['culc_id'];
			$p_base     = $a['base_date'];
		}
	}
	
	
	$list_head = '<th>番号</th>';
	$list_head.= '<th>識別文字列</th>';
	$list_head.= '<th>支払方法</th>';
	$list_head.= '<th>操作</th>';
	
	$input_list = '<table class="input_list">'  ;
	$input_list.= '<tr>'.$list_head.'</tr>';
	$input_list.= $list_in;
	$input_list.= '</table>' ;
	
	
	//----- フォーム表示 -----//
	//▼自動入力要素
	$input_auto = '<input type="hidden" name="act" value="process">';
	$button_del = '<input type="submit" class="form_submit" name="act_del" value="削除">';

	//▼登録ボタン
	$input_button = '<input type="submit" class="form_submit" name="act_send" value="この内容で登録する">';
	$input_button.= ($_GET['m_payment_id'])? $button_del:'';
	$input_button.= '<a class="spc10_l" href="'.$form_action_to.'">クリア</a>';
	
	//▼計算式リスト
	$culc_ar  = zCulcList();
	
	//★入力項目
	$st  = 'style="width:70px;"';
	$req = 'required';
	$im_payment_code      = '<input type="text" class="input_text" name="m_payment_code" value="'.$p_code.'" '.$req.' '.$st.' pattern="^([a-zA-Z0-9]{1,3})$">';
	$im_payment_name      = '<input type="text" class="input_text" name="m_payment_name" value="'.$p_name.'" '.$req.'>';
	//$im_payment_show      = '<input type="text" class="input_text" name="m_payment_show" value="'.$p_show.'">';
	$im_payment_show      = zSelectListSet($show_ar,$p_show,'m_payment_show','▼メールの中に表示する内容','','','','');
	$im_payment_info      = '<input type="text" class="input_text" name="m_payment_info" value="'.$p_info.'">';
	$im_payment_instruct  = '<textarea name="m_payment_instruct" class="remarks">'.$p_instruct.'</textarea>';
	$im_payment_base_date = '<input type="number" class="input_text" name="m_payment_base_date" value="'.$p_base.'" '.$st.' min="1">';
	
	
	//▼対応通貨配列
	if($p_target){
		$p_target_ar = explode('-',trim($p_target,'-'));
	}else{
		$p_target_ar = $p_cur;
	}
	
	//▼対応通貨選択
	foreach($cur_ar AS $k => $v){
		$che_r  = (in_array($k,$p_target_ar))? 'checked':'';
		$cl_r   = 'class="i_radio"';
		$checkcur.= (($checkcur)? '<br>':'').'<input type="checkbox" name="cur['.$k.']" value="'.$k.'" '.$cl_r.' '.$che_r.' '.$option.'> '.$v;
	}
	
	$im_payment_target = $checkcur;
	
	
	//▼支払方法手数料
	$query =  tep_db_query("
		SELECT
			`culc_id`            AS `cid`,
			`m_payment_fee_name` AS `name`
		FROM  `".TABLE_M_PAYMENT_FEE."`
		WHERE `state` = '1'
		AND   `payment_id` = '".tep_db_input($payment_id)."'
		ORDER BY `m_payment_fee_ai_id` ASC
	");
	
	
	//▼登録設定
	$stc    = 'style="margin-right:10px;"';
	$cl_clc = 'class="culcs"';				//変化検出用
	
	if(tep_db_num_rows($query)){
		$j = 0;
		
		while($b = tep_db_fetch_array($query)){
			$cld   = ($im_payment_culcid)? 'class="spc10"':'';
			$im_payment_culcid.= '<div '.$cld.'>';
			$im_payment_culcid.= '名前：<input type="text" size="6" name="culc['.$j.'][name]" value="'.$b['name'].'" '.$cl_clc.' '.$stc.'>';
			$im_payment_culcid.= zSelectListSet($culc_ar,$b['cid'],'culc['.$j.'][id]','▼手数料','','','',$cl_clc);
			$im_payment_culcid.= '</div>';
			
			$j++;
		}
	}else{
		
		$im_payment_culcid = '名前：<input type="text" size="6" name="culc[0][name]" value="" '.$stc.' '.$cl_clc.'>';
		$im_payment_culcid.= zSelectListSet($culc_ar,$p_culcid,'culc[0][id]','▼手数料','','','',$cl_clc);
	}
	
	//▼手数料追加
	$add_culc = '<div>';
	$add_culc.= '<button type="button" id="AddCulc" disabled>手数料を追加</button>';
	$add_culc.= '<button type="button" id="RmvCulc" class="spc10_l">削除</button>';
	$add_culc.= '</div>';
	
	
	//▼必須登録
	$must = I_MUST;
	$alm1 = '<span class="alert spc10_l">半角英数3文字以内</span>';
	$alm2 = '<p class="alert spc10_l">顧客が注文をした時に確認メールが送られます。<br>その確認メールの中に表示されます。</p>';
	
	//▼表示フォーム
	$input_form = '<form action="'.$form_action_to.$cont_set.'" method="post">';
	$input_form.= $input_auto;
	$input_form.= '<table class="input_form">';
	$input_form.= '<tr><th>識別文字列'.$must.'</th><td>'.$im_payment_code.$alm1.'</td></tr>';
	$input_form.= '<tr><th>支払方法'.$must.'</th><td>'.$im_payment_name.'</td></tr>';
	$input_form.= '<tr><th>対応通貨'.$must.'</th><td>'.$im_payment_target.'</td></tr>';
	$input_form.= '<tr><th>支払メール'.$must.'</th><td>'.$im_payment_instruct.$alm2.'</td></tr>';
	$input_form.= '<tr><th>表示情報</th><td>'.$im_payment_show.'</td></tr>';
	$input_form.= '<tr><th>支払情報</th><td>'.$im_payment_info.'</td></tr>';
	$input_form.= '<tr><th>手数料</th><td>'.$add_culc.'<div id="FeeA" class="spc10">'.$im_payment_culcid.'</div></td></tr>';
	$input_form.= '<tr><th>毎月の支払日</th><td>毎月　'.$im_payment_base_date.'　日に支払い</td></tr>';
	$input_form.= '</table>';
	$input_form.= '<div class="spc20">';
	$input_form.= $input_button;
	$input_form.= '</div>';
	$input_form.= '</form>';
}

//javascript引継ぎ用
$culc_base = '<div class="spc10">';
$culc_base.= '名前：<input type="text" size="6" name="culc[A][name]" value="" '.$stc.' '.$cl_clc.'>';
$culc_base.= zSelectListSet($culc_ar,'','culc[A][id]','▼手数料','','','',$cl_clc);
$culc_base.= '</div>';

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
		.input_form{width:100%;}
		.remarks{width:100%; height:200px; resize:none;padding:5px;}
		#RmvCulc{display:none;}
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
					
					<h2>追加支払方法</h2>
					<div class="m_area">
						<div class="m_list_area_f">
							<?php echo $input_list;?>
						</div>
						<div class="m_input_area_f">
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
<script>
var cFo  = '<?php echo $culc_base;?>';
var FeeA = document.getElementById('FeeA');

function jCheCulc(){
	A  = $('select[name*="culc["]');
	A2 = $('input[name*="culc["]');
	B  = 1;
	C  = [];
	
	for(var i=0;i<A.length;i++){
		AA  = A[i].value;
		AA2 = A2[i].value;
		
		if(C.indexOf(AA) > -1){
			alert('同じ手数料が設定されています');
			return;
		}else{
			B = B * ((AA && AA2)? 1:0);
			C.push(AA);
		}
	}
	
	Dis = (B)? false:true;
	$('#AddCulc').prop('disabled',Dis);
	
	if(i > 1){
		if(Dis){
			$('#RmvCulc').fadeIn(600);
		}else{
			$('#RmvCulc').fadeOut(600);
		}
	}
}

function jAddCulc(){
	A = $('select[name*="culc["]');
	bcFo = cFo.replace(/[A]/g,A.length);
	$('#FeeA').append(bcFo);
}

$(document).on('change','.culcs',function(){jCheCulc();});
$('#AddCulc').on('click',function(){jAddCulc();jCheCulc();});
$('#RmvCulc').on('click',function(){FeeA.removeChild(FeeA.lastChild);
	$('#RmvCulc').css('display','none');jCheCulc();
});
jCheCulc();
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
