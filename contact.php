<?php 
require('includes/application_top.php');

if($_GET['info'] == 'sign'){
	$info_box  = '<div class="red_box">';
	$info_box .= '当サービスは、完全紹介制になっています。<br>';
	$info_box .= '詳細・説明会などについてのご質問はお問合せください。';
	$info_box .= '</div>';
}else{
	$info_box  = '<div class="blue_box">';
	$info_box .= '商品に関する事や、当店に対するご意見ご感想、お問合わせなど、<br>';
	$info_box .= 'こちらのフォームよりお気軽にお尋ねください。';
	$info_box .= '</div>';
}

if($_POST['act'] == 'send'){
	if($_COOKIE['send_flag']){
		tep_redirect('contact.php', '', 'SSL');
	}
	
	//リロード対策
	if($_POST['ticket'] === $_COOKIE['ticket']){
		tep_cookie_del('ticket');
	}else{
		tep_cookie_del('ticket');
		tep_redirect('sample1.php?iou='.$iou, '', 'SSL');
	}
	
	$c_name  = $_POST['c_name'];
	$c_email = $_POST['c_email'];
	$c_title = $_POST['c_title'];
	$c_text  = $_POST['c_text'];
	
	////メール送信
	Email_Contact($EmailHead,$EmailFoot,$c_email,$c_name,$c_title,$c_text);

	$ToHome = '<button type="button" class="send_button" OnClick="ToHome();" style="margin-top:5px; padding:2px 10px;">Home</button>';
	$announce_flag = true;
	tep_cookie_set('send_flag', 'done');    //$_COOKIE['send_flag']
}else{
	/*
	if($_GET['event_id']){
		$event_query = tep_db_query("
		SELECT 
			`event_name`,
			DATE_FORMAT(`event_date`,'%m/%d') AS event_date
		FROM `".TABLE_EVENT."` 
		WHERE `event_id` = ".tep_db_input($_GET['event_id'])."
		");
		$event = tep_db_fetch_array($event_query);
		$set_title = $event['event_name'].'('.$event['event_date'].') に関して';
	}
	*/
	
	////リロード対策
	$ticket = md5(uniqid().mt_rand());//リロード対策
	tep_cookie_set('ticket',$ticket );//リロード対策
	
	$form  = '<form name="contact" action="contact.php" method="POST">';
	$form .= '<input type="hidden" name="ticket" value="'.$ticket.'">';//リロード対策
	$form .= '<input type="hidden" name="act" value="send">';
	$form_end = '</form>';
	$input1 = '<input id="i1" class="send_input" type="text" name="c_name" value="">';
	$input2 = '<input id="i2" class="send_input" type="text" name="c_email" value="">';
	$input3 = '<input id="i3" class="send_input" type="text" name="c_title" value="'.$set_title.'">';
	$input4 = '<textarea  id="i4" class="send_text" name="c_text" style="width:400px; height:200px;"></textarea>';
	//$submit = '<button type="button" class="send_button" OnClick="ContactSend();" style="margin-top:5px; padding:10px 20px;">お問合せをする</button>';
	$submit = '<button type="button" class="btn" OnClick="ContactSend();" style="margin-top:5px; padding:10px 20px;">お問合せをする</button>';
	$announce_flag = false;
	tep_cookie_del('send_flag'); //$_COOKIE['send_flag']
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
	<link rel="stylesheet" type="text/css" href="css/cssreset.css" media="all">
	<!--<link rel="stylesheet" type="text/css" href="css/plain.css" media="all">-->
	<link rel="stylesheet" type="text/css" href="css/top.css" media="all">
	<script>
	function ContactSend(){
		i1 = document.getElementById('i1').value;
		i2 = document.getElementById('i2').value;
		i3 = document.getElementById('i3').value;
		i4 = document.getElementById('i4').value;
		if((i1) && (i2) && (i3) && (i4)){
			if(window.confirm('ご入力の内容で送信します。\nよろしいですか？')){
				document.contact.submit();
			}
		}else{
			window.alert('未入力の項目があります。');
		}
	}
	function ToHome(){location.href = "/";}
	</script>
</head>
<div>

<?php require('inc_header1.php'); ?>

<div class="cont1" style="">
	<div class="outer" style="">
				<h2 style="margin-top:30px;">■お問合せ</h2>
				<div class="gbFFF">
					<div class="inner">

	<?php if($announce_flag == true){ ?>
		<div class="blue_box">
				お問合せを受付けました。<br>
				3営業日以内にご入力いただいたメールアドレスに回答いたします。<br>
		</div>
		<center><?php echo $ToHome; ?></center>

	<?php }else{ ?>

		<?php echo $info_box; ?>
		
		<?php echo $form; ?>
		<table class="about">
			<tr>
				<th>お名前 *</th>
				<td><?php echo $input1;?></td>
			</tr>
			<tr>
				<th>メールアドレス *</th>
				<td><?php echo $input2;?></td>
			</tr>
			<tr>
				<th>お問合せタイトル *</th>
				<td><?php echo $input3;?></td>
			</tr>
			<tr>
				<th>お問合せ内容 *</th>
				<td><?php echo $input4;?></td>
			</tr>
		</table>
		<?php echo $form_end; ?>
		<center><?php echo $submit; ?></center>
	<?php } ?>
				</div>
			</div>
</div>
<div style="height:100px;"></div>

<?php require('inc_footer1.php'); ?>

</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>