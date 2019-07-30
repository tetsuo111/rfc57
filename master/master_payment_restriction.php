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

//▼読込ファイル
$filepath = '../util/inc_payment_restriction.php';


//-------- データ処理 --------//
if($_POST['act'] == 'process'){
	
	//▼データ取得
	$p_rest = $_POST['rest'];		//制限対象
	$p_mpay = $_POST['mpay'];		//支払方法
	
	//▼制限設定
	foreach($p_mpay AS $k => $v){
		if(!$p_rest[$k]){
			$file_in.= (($file_in)? ",'":"'").$k."'=>'".$k."'"."\n";
		}
	}
	
	//▼書き込み処理
	$string = "\$PayRestrictArray = array("."\n";
	$string.= $file_in;
	$string.= ");";
	
	//▼ファイル書き込み
	write_setting($filepath,$string,'w');
	
	//▼終了処理
	$end = 'end';
}

//----- 表示フォーム -----//
if($end == 'end'){
	
	$input_form = '<p>支払設定を追加しました</p>';
	$input_form.= '<a href="'.$form_action_to.'">登録を続ける</a>';
	
}else{
	
	//----- 表示リスト -----//
	$cur_ar  = zCurrencyList();			//通貨
	$rank_ar = zRankList('bcname');		//ランク
	$pay_ar  = zPaymentList();			//支払方法
	
	//▼規制リスト読込
	require($filepath);
	
	//$OrderType 			購入種類
	//$PayRestrictArray		支払制限
	
	//▼支払方法表示
	foreach($pay_ar AS $k => $v){
		$pay_head_in.= '<th>'.$v.'</th>';
	}
	
	//▼支払方法項目
	$pay_head = '<tr><th>ランク</th>'.$pay_head_in.'</tr>';
	
	
	//▼入力内容成形
	foreach($OrderType AS $ko => $vo){
		
		//▼初期化
		$rank_in = '';
		
		//▼登録リスト作成　ランク＞支払方法
		foreach($rank_ar AS $kr => $vr){
			
			//支払方法初期化
			$pay_in  = '';
			
			//支払方法を追加
			foreach($pay_ar AS $kp => $vp){
				
				//▼通貨ペア
				$cur_in = '';
				foreach($cur_ar AS $kc =>$vc){
					//制限ID 注文種類＞bctype＞支払方法＞支払通貨
					$rest_id = $ko.'_'.$kr.'_'.$kp.'_'.$kc;
					$checked = ($PayRestrictArray[$rest_id])? '':'checked';
					$cur_in.='<p><input type="checkbox" name="rest['.$rest_id.']" value="a" '.$checked.'> '.$vc.'</p>';
					$payset_ar.= '<input type="hidden"  name="mpay['.$rest_id.']" value="a">';
				}
				
				$pay_in.= '<td>'.$cur_in.'</td>';
			}
			
			$rank_in.= '<tr><th>'.$vr.'</th>'.$pay_in.'</tr>';
		}
		
		//▼登録リスト
		$input_form_in.= '<div class="spc20">';
		$input_form_in.= '<h4>'.$vo.'</h4>';
		$input_form_in.= '<table class="input_form">';
		$input_form_in.= $pay_head;
		$input_form_in.= $rank_in;
		$input_form_in.= '</table>';
		$input_form_in.= '</div>';
	}
	
	
	//----- フォーム表示 -----//
	//▼自動入力要素
	$input_auto = '<input type="hidden" name="act" value="process">';
	$input_auto.= $payset_ar;

	//▼登録ボタン
	$input_button = '<input type="submit" class="form_submit" name="act_send" value="この内容で登録する">';
	$input_button.= '<a class="spc10_l" href="'.$form_action_to.'">クリア</a>';
	
	
	//▼表示フォーム
	$input_form = '<form action="'.$form_action_to.'" method="post">';
	$input_form.= $input_auto;
	$input_form.= $input_form_in;
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
					
					<h2>支払方法設定</h2>
					<div class="m_area">
						<?php echo $err_text;?>
						<?php echo $input_form;?>
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
