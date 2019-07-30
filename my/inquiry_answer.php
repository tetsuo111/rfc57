<?php
require('includes/application_top.php');

if($_COOKIE['user_id']){
    $user_id = $_COOKIE['user_id'];
    $head_user_name = $_COOKIE['user_name'].'様';
}else{
    //$head_user_name = 'ゲスト様';
    tep_redirect('../logout.php', '', 'SSL');
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
        'a_inquiry_memberid' => $user_id,
        'a_inquiry_side' => 0,
        'date_create' => 'now()',
        'date_update' => 'now()',
        'state' => '1'
    );


    $notice_id = zDBNewUniqueID($db_table, $sql_data_array, $t_ai_id, $t_id);

    $input_form = '送信しました。';


    //▼会員ID
    $query = tep_db_query("SELECT `login_id`, `name1` FROM `".TABLE_MEM00000."` WHERE `memberid` = '".tep_db_input($user_id)."'");
    $mem0  = tep_db_fetch_array($query);

    //----- メール送信 -----//
    //▼送信設定
    $email            = zGetUserEmail($user_id);	//送信メールアドレス
    $Efs_id           = $mem0['login_id'];			//会員ID　＞cisログインID
    $Euser_name       = $mem0['name1'];		//ユーザー名

    Email_Contact(
        $EmailHead,
        $EmailFoot,
        $email,
        $Euser_name,
        '',
        $answer
    );

    //todo:問い合わせ通知をどこかへ送る
//    Email_Contact(
//        $EmailHead,
//        $EmailFoot,
//        $email,
//        $Euser_name,
//        '',
//        $answer
//    );

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
        $iq_list_tr .= '<tr>';
    }else{
        $iq_list_tr .= '<tr class="bg-info">';
    }

    if($a['side']==0){
        $iq_list_tr .= '<td nowrap>'.$a['name'].' 様</td>';
    }else{
        $iq_list_tr .= '<td>'.$a['name'].'</td>';
    }
    $iq_list_tr .= '<td>'.$a['content'].'</td>';
    $iq_list_tr .= '<td>'.$a['date_update'].'</td>';
    $iq_list_tr .= '</tr>';
}

//$list_head = '<tr><th>件名</th><th colspan="2">'.$a['title'].'</th></tr>';
$list_head .= '<tr><th>送信者</th><th width="70%">内容</th><th>送信日時</th></tr>';

$input_list = '<table class="table table-bordered" style="font-size:11px;">';
$input_list.= '<tr>'.$list_head.'</tr>';
$input_list.= $iq_list_tr;
$input_list.= '</table>';

if($_POST['act']=='confirm') {
    $input_form = '下記の内容で送信しますか？<form action="inquiry_answer.php" method="post">';
    $input_form .= '<input type="hidden" name="inquiry_id" value="' . $inquriy_id . '">';
    $input_form .= '<input type="hidden" name="act" value="send">';
    $input_form .= '<input type="hidden" name="answer" value="' . $answer . '">';
    $input_form .= '<textarea class="form-control" name="answer" disabled rows="10">' . $answer . '</textarea><br><br>';
    $input_form .= '<div class="text-center"><input class="btn btn-default" type="submit" value="送信"></div>';
    $input_form .= '</form>';
}elseif($_POST['act']=='send'){

}else{
    $input_form = '<form action="inquiry_answer.php" method="post">';
    $input_form .= '<input type="hidden" name="inquiry_id" value="'.$inquriy_id.'">';
    $input_form .= '<input type="hidden" name="act" value="confirm">';
    $input_form .= '<textarea class="form-control" name="answer" rows="10"></textarea><br><br>';
    $input_form .= '<div class="text-center"><input class="btn btn-default" type="submit" value="送信内容の確認"></div>';
    $input_form .= '</form>';


}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type"         content="text/html; charset=<?php echo CHARSET; ?>">
    <meta http-equiv="Content-Style-Type"  content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <meta http-equiv="X-UA-Compatible"      content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php echo $favicon."\n"; ?>
    <title><?php echo $title;?></title>
    <meta name="description"       content="">
    <meta name="keywords"          content="">
    <meta name="robots"            content="noindex,nofollow,noarchive">
    <meta name="format-detection" content="telephone=no">
    <meta name="format-detection" content="email=no">
    <link rel="stylesheet" type="text/css" href="../css/cssreset.css" media="all">
    <link rel="stylesheet" type="text/css" href="../css/common.css"   media="all">
    <link rel="stylesheet" type="text/css" href="../js/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="../js/bootstrap/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="../css/my.css"       media="all">

    <script src="../js/jquery-3.2.1.min.js"            charset="UTF-8"></script>
    <script src="../js/bootstrap/js/bootstrap.min.js" charset="UTF-8"></script>

</head>
<body>
<div id="wrapper">

    <div id="header">
        <?php require('inc_user_header.php');?>
    </div>

    <div class="container-fluid">
        <div id="content" class="row">

            <div id="left1" class="col-md-4 col-lg-2">
                <div class="inner">
                    <div class="u_menu_area">
                        <?php require('inc_user_left.php'); ?>
                    </div>
                </div>
            </div>

            <div id="left2">
                <div id="left2" class="col-xs-12 col-md-12 col-md-8 col-lg-10">
                    <div class="inner">
                        <div class="part">

                            <div class="area1">
                                <h2>お問い合わせ回答</h2>
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

        <div id="footer">
            <?php require('inc_user_footer.php');?>
        </div>
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
