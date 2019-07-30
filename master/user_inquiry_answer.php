<?php
require('includes/application_top.php');

if(($_COOKIE['master_id']) && ($_COOKIE['master_permission'])){
    $master_id = $_COOKIE['master_id'];
    $head_master_name = $_COOKIE['master_name'].'様';
}else{
    //$head_master_name = 'ゲスト様';
    tep_redirect('logout.php', '', 'SSL');
}



if($_POST['act']=='confirm'){
    $answer = $_POST['answer'];
    $iq_id = $_POST['inquiry_id'];

}elseif($_POST['act']=='send') {
    $answer = $_POST['answer'];
    $iq_id = $_POST['inquiry_id'];


    $db_table = 'a_inquiry';
    $t_ai_id = 'a_inquiry_ai_id';
    $t_id = 'a_inquiry_detail_id';


    $sql_data_array = array(
        //			'a_notice_target'    => $p_target,
        'a_inquiry_id' => $iq_id,
        'a_inquiry_title' => '',
        'a_inquiry_content' => $answer,
        'a_inquiry_memberid' => 1000,
        'a_inquiry_side' => 1,
        'date_create' => 'now()',
        'date_update' => 'now()',
        'state' => '1'
    );


    $notice_id = zDBNewUniqueID($db_table, $sql_data_array, $t_ai_id, $t_id);

    $input_form = '送信しました。';


    //問い合わせ者のmemberid特定
    $query =  tep_db_query("SELECT `a_inqiury_memberid` AS memberid FROM `a_inquiry` WHERE `a_inquiry_id` ='".$iq_id."' AND `a_inquiry_detail_id` = 0 AND `a_inquiry_side` = 0");
    $inquiry = tep_db_fetch_array($query);

    //▼会員ID
    $query = tep_db_query("SELECT `login_id`, `name1` FROM `".TABLE_MEM00000."` WHERE `memberid` = '".tep_db_input($inquiry['memberid'])."'");
    $mem0  = tep_db_fetch_array($query);

    //----- メール送信 -----//
    //▼送信設定
    $email            = zGetUserEmail($inquiry['memberid']);	//送信メールアドレス
    $Efs_id           = $mem0['login_id'];			//会員ID　＞cisログインID
    $Euser_name       = $mem0['name1'];		//ユーザー名

    //回答メール
    Email_Contact_Answered(
        $EmailHead,
        $EmailFoot,
        $email,
        $Euser_name,
        'お問い合わせありがとうございます。',
        $answer,
        $iq_id
    );


}else{
    $inquriy_id = $_GET['id'];

}

if(isset($iq_id)){
    $inquriy_id = $iq_id;
}

/*----- リスト表示 -----*/
//▼初期設定
$query = tep_db_query("
	SELECT
	 `a_inquiry_memberid` AS `memberid`, 
	 `a_inquiry_id` AS `inquiry_id`, 
	 `a_inquiry_title` AS `title`, 
	 `a_inquiry_content` AS `content`, 
	 `a_inquiry_detail_id` AS `detail_id`, 
	 `a_inquiry_side` AS `side`, 
      `date_create`,`date_update`,
      `mem00000`.`name1` AS `name`
	FROM `a_inquiry` INNER JOIN `mem00000` ON `mem00000`.`memberid` = `a_inquiry`.`a_inquiry_memberid` 
	WHERE `state` = 1 AND `a_inquiry_id` = ".$inquriy_id." 
	ORDER BY `date_update` ASC 
");


//▼表示設定
$iq_list_tr = '';
while($a = tep_db_fetch_array($query)) {
    if($a['side']==0){
        $iq_list_tr .= '<tr class="bg-success">';
    }else{
        $iq_list_tr .= '<tr>';
    }

    if($a['side']==0){
        $iq_list_tr .= '<td>'.$a['name'].' 様</td>';
    }else{
        $iq_list_tr .= '<td>'.$a['name'].'</td>';
    }
    $iq_list_tr .= '<td>'.$a['content'].'</td>';
    $iq_list_tr .= '<td>'.$a['date_update'].'</td>';
    $iq_list_tr .= '</tr>';
}

//$list_head = '<tr><th>件名</th><th colspan="2">'.$a['title'].'</th></tr>';
$list_head .= '<tr><th>送信者</th><th>内容</th><th>送信日時</th></tr>';

$input_list = '<table class="table table-bordered" style="font-size:11px;">';
$input_list.= '<tr>'.$list_head.'</tr>';
$input_list.= $iq_list_tr;
$input_list.= '</table><br><br>';

if($_POST['act']=='confirm') {
    $input_form = '下記の内容で送信しますか？<form action="user_inquiry_answer.php" method="post">';
    $input_form .= '<input type="hidden" name="inquiry_id" value="' . $inquriy_id . '">';
    $input_form .= '<input type="hidden" name="act" value="send">';
    $input_form .= '<input type="hidden" name="answer" value="' . $answer . '">';
    $input_form .= '<textarea class="form-control" name="answer" disabled rows="10" cols="300">' . $answer . '</textarea><br><br>';
    $input_form .= '<div class="text-center"><input class="btn btn-default" type="submit" value="送信"></div>';
    $input_form .= '</form>';
}elseif($_POST['act']=='send'){

}else{
    $input_form = '<form action="user_inquiry_answer.php" method="post">';
    $input_form .= '<input type="hidden" name="inquiry_id" value="'.$inquriy_id.'">';
    $input_form .= '<input type="hidden" name="act" value="confirm">';
    $input_form .= '<textarea class="form-control" name="answer" rows="10" cols="100"></textarea><br><br>';
    $input_form .= '<div class="text-center"><input class="btn btn-default" type="submit" value="送信内容の確認"></div>';
    $input_form .= '</form>';


}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
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
    <link rel="stylesheet" type="text/css" href="../js/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="../js/bootstrap/css/font-awesome.min.css" />

    <script src="../js/jquery-3.2.1.min.js" charset="UTF-8"></script>
    <script src="../js/jquery-migrate-1.4.1.min.js"   charset="UTF-8"></script>
    <script src="../js/jquery-ui/jquery-ui.min.js"    charset="UTF-8"></script>
    <script src="../js/bootstrap/js/bootstrap.min.js" charset="UTF-8"></script>

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
<body>
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
            </div>
            <div id="left2" class="col-xs-12 col-md-12 col-md-8 col-lg-10">
                <div class="inner">
                    <div class="part">

                        <div class="area1">
                            <h2>対応履歴</h2>
                            <?php echo $input_list;?>
                        </div>
                        <div class="input_area">
                            <?php echo $input_form;?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/MyHelper.js" charset="UTF-8"></script>

<!--    <div id="footer">-->
<!--        --><?php //require('inc_master_footer.php');?>
<!--    </div>-->
</div>
<script>
    $('#qrcode').qrcode({width: 196, height: 196, text:'<?php echo $linkurl;?>'});
    $('.onQR').on('click',function(){
        $('#Pop').toggleClass('isOpen');
    });
</script>
<script>
    $(function () {
        var clipboard = new Clipboard('.onClip');

        clipboard.on('success',function(e){
            e.clearSelection();
            alert('コピーしました');
        });
        clipboard.on('error', function(e) {
            alert('コピーに失敗しました');
        });
    });
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
