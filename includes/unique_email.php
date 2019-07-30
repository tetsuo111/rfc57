<?php
////Email
$EmailHead = "----------------------------------------------------------------------"."\n";
$EmailHead.= SITE_OWNER. "\n";
$EmailHead.= "----------------------------------------------------------------------"."\n\n";

$EmailFoot = "\n";
$EmailFoot.= "----------------------------------------------------------------------"."\n";
$EmailFoot.= SITE_OWNER. "\n";
$EmailFoot.= HTTP_SERVER."\n";
//$EmailFoot.= "株式会社グランディール"."\n";
//$EmailFoot.= "岡山県岡山市南区大福432番地22 クリオビル2階"."\n";
$EmailFoot.= "----------------------------------------------------------------------"."\n\n";

$EmailFootCstm = "\n";
$EmailFootCstm.= "----------------------------------------------------------------------"."\n";
$EmailFootCstm.= "最新情報やお客様の履歴確認は以下からできます。"."\n";
$EmailFootCstm.= SITE_OWNER."ホームページ"."\n";
$EmailFootCstm.= HTTP_SERVER."\n";
$EmailFootCstm.= "\n";
$EmailFootCstm.= "ご不明な点がございましたら以下まで問い合わせください。"."\n";
$EmailFootCstm.= SITE_CUSTOMER_EMAIL."\n";
$EmailFootCstm.= "----------------------------------------------------------------------"."\n\n";


//お問合せ
function Email_Contact($EmailHead,$EmailFoot,$c_email,$c_name,$c_title,$c_text){
	$EmailSubject = "【".SITE_OWNER."】お問合せを受付けました";
	$EmailText = "メールアドレス: ".$c_email."\n";
	$EmailText.= "※このメールは、ご入力のメールアドレスあてに自動的にお送りしています。". "\n";
	$EmailText.= ""."\n";
	$EmailText.= "お問合せを受付けました。"."\n";
	$EmailText.= "3営業日以内にご入力いただいたメールアドレスに回答いたします。"."\n";
	$EmailText.= ""."\n";

	$EmailText.= "■お名前"."\n";
	$EmailText.= $c_name."\n\n";
	$EmailText.= "■メールアドレス"."\n";
	$EmailText.= $c_email."\n\n";
	$EmailText.= "■お問合せタイトル"."\n";
	$EmailText.= $c_title."\n\n";
	$EmailText.= "■お問合せ内容"."\n";
	$EmailText.= $c_text."\n\n";
	
	$EmailTextAll = $EmailHead. $EmailText. $EmailFoot;
	
	//$array = array(SITE_OWNER_EMAIL, $c_email);
	//$array = array(SITE_OWNER_EMAIL2, $c_email);
	$array = array(SITE_LOG_EMAIL, $c_email,DEV_LOG_EMAIL);
	foreach ($array as $var) {
		tep_mail($c_email, $var, $EmailSubject, $EmailTextAll, SITE_OWNER, SITE_OWNER_EMAIL);
	}
}

//お問合せの回答
function Email_Contact_Answered($EmailHead,$EmailFoot,$c_email,$c_name,$c_title,$c_text,$inquiry_id){
    $EmailSubject = "【".SITE_OWNER."】".$c_title;
    $EmailText = $c_name.'様'."\n\n";
    $EmailText .= $c_text;
    $EmailText .= ""."\n";
    $EmailText .= ""."\n";
    $EmailText .= "以下のURLでもお問い合わせ内容と回答がご確認いただけます。"."\n";
    $EmailText .= "回答への返信、追加のお問い合わせはシステム内よりお願いいたします。"."\n";
    $EmailText.= HTTP_SERVER.'my/inquiry_answer.php?id='.$inquiry_id."\n";
    $EmailText.= ""."\n";

    $EmailTextAll = $EmailHead. $EmailText. $EmailFoot;

    //$array = array(SITE_OWNER_EMAIL, $c_email);
    //$array = array(SITE_OWNER_EMAIL2, $c_email);
    $array = array(SITE_LOG_EMAIL, $c_email,DEV_LOG_EMAIL);
    foreach ($array as $var) {
        tep_mail($c_email, $var, $EmailSubject, $EmailTextAll, SITE_OWNER, SITE_OWNER_EMAIL);
    }
}


//新規登録
function Email_Create($EmailHead,$EmailFoot,$invite_code,$email,$login_id,$login_pass,$login_url){

	$EmailSubject = "【".SITE_OWNER."】新規登録のおしらせ";
	
	$EmailText = "紹介者コード: ".$invite_code."\n";
	$EmailText.= "メールアドレス: ".$email."\n";
	$EmailText.= ""."\n";
	$EmailText.= "※このメールは、登録メールアドレスあてに自動的にお送りしています。". "\n";
	$EmailText.= "※This mail is automatically sent to the registered mail address.". "\n";
	$EmailText.= ""."\n";
	$EmailText.= SITE_OWNER."へのご参加ありがとうございます。"."\n";
	$EmailText.= "\n";
	$EmailText.= "お客様のログイン情報をお送りします。"."\n";
	$EmailText.= "次回以降".SITE_OWNER."にログインする際は"."\n";
	$EmailText.= "こちらの情報をお使いください。"."\n";
	$EmailText.= "\n";
	$EmailText.= "Your Login Data"."\n";
	$EmailText.= "============="."\n";
	$EmailText.= "[Login ID]"."\n";
	$EmailText.= $login_id."\n";
	$EmailText.= "\n";
	$EmailText.= "[Login Pass]"."\n";
	$EmailText.= $login_pass."\n";
	$EmailText.= "\n";
	$EmailText.= "[Login Url]"."\n";
	$EmailText.= HTTP_SERVER.$login_url.'?yl_id='.$login_id."\n";
	$EmailText.= "============="."\n";
	$EmailText.= "\n";
	$EmailText.= "※Login Passはマイページから変更できます。"."\n";
	$EmailText.= "※You can change Login Pass from My Page."."\n";
	
	$EmailTextAll = $EmailHead. $EmailText. $EmailFoot;
	
	$array = array(SITE_LOG_EMAIL, $email,DEV_LOG_EMAIL);
	foreach ($array as $var) {
		tep_mail($email, $var, $EmailSubject, $EmailTextAll, SITE_OWNER, SITE_OWNER_EMAIL);
	}
}


//招待メール
function Email_Invitation($EmailHead,$EmailFoot,$create_url,$from_name,$from_email,$to_name,$to_email,$to_text){
	
	$EmailSubject = "【".SITE_OWNER."】ご招待";

	$EmailText = $to_name." 様 (".$to_email.") \n";
	$EmailText.= ""."\n";
	
	$EmailText.= $from_name." 様からご招待がありましたのでメールを差し上げています。"."\n";
	$EmailText.= SITE_OWNER."と申します"."\n";
	$EmailText.= "\n";
	$EmailText.= $from_name.'様からのメッセージ'."\n";
	$EmailText.= "---------------------------"."\n";
	$EmailText.= $to_text."\n";
	$EmailText.= ""."\n";
	$EmailText.= "---------------------------"."\n";
	$EmailText.= "\n";
	$EmailText.= "下記URLをクリックすると".SITE_OWNER.'の新規登録画面に移動します。'."\n";
	$EmailText.= "こちらからご登録を進めてください。"."\n";
	$EmailText.= "URL: ".$create_url."\n";
	$EmailText.= "\n";

	$EmailTextAll = $EmailHead. $EmailText. $EmailFoot;
	
	//招待した人、された人のどちらにもメールを送る
	$array = array(SITE_LOG_EMAIL, $to_email,$from_email,DEV_LOG_EMAIL);
	foreach ($array as $var) {
		tep_mail($to_email, $var, $EmailSubject, $EmailTextAll, SITE_OWNER, SITE_OWNER_EMAIL);
	}
}


//パスワード再登録
function Email_Pass_Reset($EmailHead,$EmailFoot,$email,$resend_url){
	$EmailSubject = "【".SITE_OWNER."】パスワード再登録";
	
	$EmailText = "メールアドレス: ".$email."\n";
	$EmailText.= ""."\n";
	$EmailText.= "※このメールは、登録メールアドレスあてに自動的にお送りしています。". "\n";
	$EmailText.= ""."\n";
	$EmailText.= "メールアドレスの確認にご協力いただき、ありがとうござます。". "\n";
	$EmailText.= "下記URLをクリックして、新しいパスワードを入力してください。"."\n";
	$EmailText.= ""."\n";
	$EmailText.= "URL: ".$resend_url."\n";
	$EmailText.= ""."\n";
	
	$EmailTextAll = $EmailHead. $EmailText. $EmailFoot;
	
	
	$array = array(SITE_LOG_EMAIL, $email,DEV_LOG_EMAIL);
	foreach ($array as $var) {
		tep_mail($email, $var, $EmailSubject, $EmailTextAll, SITE_OWNER, SITE_OWNER_EMAIL);
	}
}


function Email_Re_Login($EmailHead,$EmailFoot,$email,$login_id,$login_pass,$login_url){
	
	$EmailSubject = "【".SITE_OWNER."】ログイン情報変更";
	
	$EmailText = "メールアドレス: ".$email."\n";
	$EmailText.= ""."\n";
	$EmailText.= "※このメールは、登録メールアドレスあてに自動的にお送りしています。". "\n";
	$EmailText.= "※This mail is automatically sent to the registered mail address.". "\n";
	$EmailText.= "\n";
	$EmailText.= "お客様のログイン情報を変更しました。"."\n";
	$EmailText.= SITE_OWNER."にログインする際は"."\n";
	$EmailText.= "こちらの情報をお使いください。"."\n";
	$EmailText.= "\n";
	$EmailText.= "Your Login Data"."\n";
	$EmailText.= "============="."\n";
	$EmailText.= "[Login ID]"."\n";
	$EmailText.= $login_id."\n";
	$EmailText.= "\n";
	$EmailText.= "[Login Pass]"."\n";
	$EmailText.= $login_pass."\n";
	$EmailText.= "\n";
	$EmailText.= "[Login Url]"."\n";
	$EmailText.= HTTP_SERVER.$login_url.'?yl_id='.$login_id."\n";
	$EmailText.= "============="."\n";
	$EmailText.= "\n";
	
	$EmailTextAll = $EmailHead. $EmailText. $EmailFoot;
	
	$array = array(SITE_LOG_EMAIL, $email,DEV_LOG_EMAIL);
	foreach ($array as $var) {
		tep_mail($email, $var, $EmailSubject, $EmailTextAll, SITE_OWNER, SITE_OWNER_EMAIL);
	}
}


/*============================
　注文
============================*/
//ご注文
function Email_Order_Create($EmailHead,$EmailFoot,$email,$fs_id,$user_name,$order_id,$order_amount,$order_limit,$cart_array){

	$EmailSubject = "【".SITE_OWNER."】ご注文の確認";
	
	//▼基準通貨
	$base_cur = zGetSysSetting('sys_base_currency_unit');
	
	//▼オーダーテキスト
	$EmailText = "※このメールは、登録メールアドレスあてに自動的にお送りしています。". "\n";
	$EmailText.= ""."\n";
	$EmailText.= $user_name."様"."\n";
	$EmailText.= ""."\n";
	$EmailText.= "下記の内容でご注文を受付いたしました。". "\n";
	$EmailText.= "記載金額のご入金を下記入金口座にお願いいたします。". "\n";
	$EmailText.= ""."\n";
	$EmailText.= ""."\n";
	$EmailText.= "入金の確認が出来次第手続きを進めさせて頂きます。". "\n";
	$EmailText.= ""."\n";
	
	$EmailText.= ""."\n";
	$EmailText.= "------------------------------"."\n";
	$EmailText.= "■　ご注文内容"."\n";
	$EmailText.= "------------------------------"."\n";
	$EmailText.= "会員番号　：".$fs_id."\n";
	$EmailText.= "注文番号　：".$order_id."\n";
	$EmailText.= "合計金額　：".$order_amount.$base_cur.'（税込）'."\n";
	$EmailText.= ""."\n";
	
	$EmailText.= "------------------------------"."\n";
	$EmailText.= "■　ご注文の商品"."\n";
	$EmailText.= "------------------------------"."\n";
	
	//▼注文内容を
	foreach($cart_array AS $k => $v){
		//消費税を含まない金額
		$EmailText.= "注文商品　：".$v['name']."\n";
		$EmailText.= "注文個数　：".$v['num']."\n";
		$EmailText.= "合計金額　：".$v['base_b'].$base_cur.'（税抜）'."\n";
		$EmailText.= ""."\n";
	}
	
	
	//▼請求情報作成
	$query =  tep_db_query("
		SELECT
			`charge_id`,
			`c_amount`,
			`c_currency_name`  AS `c_cur`,
			`c_payment_id`     AS `p_id`,
			`c_payment_name`   AS `p_name`,
			`payment_show`     AS `p_show`,
			`payment_info`     AS `p_info`,
			`payment_instruct` AS `p_instruct`
		FROM  `".VIEW_CHARGE."`
		WHERE `order_id` = '".tep_db_input($order_id)."'
		ORDER BY `c_currency_id`
	");
	
	while($pay = tep_db_fetch_array($query)){
		
		//▼請求情報
		$charge_id    = $pay['charge_id'];
		$pay_amount   = $pay['c_amount'].' '.$pay['c_cur'];		//請求金額
		$pay_name     = $pay['p_name'];							//支払方法
		$pay_show     = $pay['p_show'];							//表示情報
		$pay_info     = $pay['p_info'];							//その他情報
		$pay_instruct = $pay['p_instruct'];						//支払方法指示
		
		//▼支払方法
		if($pay_show == 'a'){
			
			//▼基準支払口座
			$bank_a   = zGetMasterBank();
			
			//▼支払情報
			$EmailPay = "銀行名　：".$bank_a["bank_name"]."\n";				//銀行名
			$EmailPay.= "支店名　：".$bank_a["bank_branch"]."\n";			//支店名
			$EmailPay.= "預金種類：".$bank_a["bank_type"]."\n";				//預金種類
			$EmailPay.= "口座番号：".$bank_a["bank_number"]."\n";			//口座番号
			$EmailPay.= "口座名義：".$bank_a["bank_account_name"]."\n";		//口座名義
			$EmailPay.= ""."\n";
			$EmailPay.= "■重要■". "\n";
			$EmailPay.= "※振込依頼人（入金されるお客さま）の名義は、". "\n";
			$EmailPay.= "「".$fs_id."＋氏名」をご入力下さい。". "\n";
			
		}else if($pay_show == 'b'){
			//ビットコインアドレス
			
		}
		
		
		$EmailText.= "------------------------------"."\n";
		$EmailText.= "■「".$pay_name."」でのお支払い"."\n";
		$EmailText.= "------------------------------"."\n";
		//$EmailText.= "請求番号：".$charge_id."\n";
		$EmailText.= "請求金額：".$pay_amount."\n";
		$EmailText.= "入金期限：".$order_limit."\n";
		$EmailText.= ""."\n";
		$EmailText.= $EmailPay;
		$EmailText.= ""."\n";
		$EmailText.= ""."\n";
	}
	
	
	$EmailTextAll = $EmailHead. $EmailText. $EmailFoot;
	
	$array = array(SITE_LOG_EMAIL, $email,DEV_LOG_EMAIL);
	foreach ($array as $var) {
		tep_mail($email, $var, $EmailSubject, $EmailTextAll, SITE_OWNER, SITE_OWNER_EMAIL);
	}
}


//入金確認
function Email_Payment_Confirm($EmailHead,$EmailFoot,$email,$user_name,$fs_id,$order_id,$charge_amount,$charge_currency,$paid_amount,$paid_date){
	
	$EmailSubject = "【".SITE_OWNER."】入金を確認しました";
	
	$EmailText.= "※このメールは、登録メールアドレスあてに自動的にお送りしています。". "\n";
	$EmailText.= ""."\n";
	$EmailText.= $user_name."様"."\n";
	$EmailText.= ""."\n";
	$EmailText.= "下記ご注文の入金を確認しました。". "\n";

	$EmailText.= ""."\n";
	$EmailText.= "------------------------------"."\n";
	$EmailText.= "■　ご注文内容"."\n";
	$EmailText.= "------------------------------"."\n";
	$EmailText.= "会員番号　：".$fs_id."\n";
	$EmailText.= "注文番号　：".$order_id."\n";
	
	
	$EmailText.= "請求金額　：".$charge_amount.$charge_currency."\n";
	$EmailText.= "入金金額　：".$paid_amount.$charge_currency."\n";
	$EmailText.= "確認日　　：".$paid_date."\n";
	
	$EmailText.= ""."\n";
	$EmailText.= ""."\n";
	
	$EmailTextAll = $EmailHead. $EmailText. $EmailFoot;
	
	$array = array(SITE_LOG_EMAIL, $email,DEV_LOG_EMAIL);
	foreach ($array as $var) {
		tep_mail($email, $var, $EmailSubject, $EmailTextAll, SITE_OWNER, SITE_OWNER_EMAIL);
	}
}


//▼注文削除
//function Email_Order_Del($EmailHead,$EmailFoot,$email,$fs_id,$user_name,$order_id,$order_plan_name,$order_num){
function Email_Order_Del($EmailHead,$EmailFoot,$email,$fs_id,$user_name,$order_id,$cart_ar){

	$EmailSubject = "【".SITE_OWNER."】ご注文取消しのご連絡";
	
	//▼オーダーテキスト
	$EmailText.= "※このメールは、登録メールアドレスあてに自動的にお送りしています。". "\n";
	$EmailText.= ""."\n";
	$EmailText.= $user_name."様"."\n";
	$EmailText.= ""."\n";
	$EmailText.= "下記ご注文を取消しました。". "\n";
	$EmailText.= ""."\n";
	
	$EmailText.= ""."\n";
	$EmailText.= "------------------------------"."\n";
	$EmailText.= "■　ご注文内容"."\n";
	$EmailText.= "------------------------------"."\n";
	$EmailText.= "会員番号　：".$fs_id."\n";
	$EmailText.= "注文番号　：".$order_id."\n";
	
	//▼注文詳細
	foreach($cart_ar AS $p){
		
		$EmailText.= ""."\n";
		$EmailText.= "商品番号　：".$p['plan_id']."\n";
		$EmailText.= "商品名称　：".$p['name']."\n";
		$EmailText.= "単体価格　：".$p['pricea'];
		$EmailText.= "注文個数　：".$p['num']."\n";
		$EmailText.= "合計価格　：".$p['priceb'];
	}
	
	$EmailText.= ""."\n";
	$EmailText.= ""."\n";
	
	$EmailTextAll = $EmailHead. $EmailText. $EmailFoot;
	
	$array = array(SITE_LOG_EMAIL, $email,DEV_LOG_EMAIL);
	foreach ($array as $var) {
		tep_mail($email, $var, $EmailSubject, $EmailTextAll, SITE_OWNER, SITE_OWNER_EMAIL);
	}
}



/*============================
　データ承認
============================*/
//代理店メール送信
function Email_Agent_to_User($EmailHead,$EmailFoot,$email,$subject,$content){
	
	$email_agent  = $_COOKIE['agent_email'];
	$EmailSubject = "【".SITE_OWNER."】".$subject;
	
	$EmailText = $content;

	$EmailTextAll = $EmailHead. $EmailText. $EmailFoot;
	
	//送信先メールアドレス
	//SITE_LOG_EMAI		＞サイトマスター
	//$email			＞ユーザー
	//$email_agent		＞当代理店責任者
	//DEV_LOG_EMAIL		＞開発者
	$array = array(SITE_LOG_EMAIL, $email,$email_agent,DEV_LOG_EMAIL);
	
	foreach ($array as $var) {
		tep_mail($email, $var, $EmailSubject, $EmailTextAll, SITE_OWNER, SITE_OWNER_EMAIL);
	}
}

//データ承認
function Email_User_Data_Approved($EmailHead,$EmailFoot,$user_name,$email,$data_name){
	
	$email_agent  = $_COOKIE['agent_email'];
	$EmailSubject = "【".SITE_OWNER."】データが承認されました";
	
	$EmailText = "※このメールは、登録メールアドレスあてに自動的にお送りしています。". "\n";
	$EmailText.= ""."\n";
	$EmailText.= $user_name."様"."\n";
	$EmailText.= ""."\n";
	$EmailText.= "ご登録いただいたデータが承認されました。". "\n";
	$EmailText.= ""."\n";
	$EmailText.= "承認されたデータ: ".$data_name."\n";
	$EmailText.= ""."\n";
	$EmailText.= "データのご登録ありがとうございました。". "\n";

	$EmailTextAll = $EmailHead. $EmailText. $EmailFoot;
	
	$array = array(SITE_LOG_EMAIL, $email,$email_agent,DEV_LOG_EMAIL);
	
	foreach ($array as $var) {
		tep_mail($email, $var, $EmailSubject, $EmailTextAll, SITE_OWNER, SITE_OWNER_EMAIL);
	}
}


//データ再提出
function Email_User_Data_Denied($EmailHead,$EmailFoot,$user_name,$email,$data_name,$reason){
	
	$email_agent  = $_COOKIE['agent_email'];
	$EmailSubject = "【".SITE_OWNER."】データを再提出してください";
	
	$EmailText = "※このメールは、登録メールアドレスあてに自動的にお送りしています。". "\n";
	$EmailText.= ""."\n";
	$EmailText.= $user_name."様"."\n";
	$EmailText.= ""."\n";
	$EmailText.= "下記データの再提出をお願いします。". "\n";
	$EmailText.= ""."\n";
	$EmailText.= "再提出が必要なデータ: ".$data_name."\n";
	$EmailText.= ""."\n";
	$EmailText.= "【再提出の理由】"."\n";
	$EmailText.= $reason."\n";
	
	$EmailTextAll = $EmailHead. $EmailText. $EmailFoot;
	
	
	//送信先メールアドレス
	//SITE_LOG_EMAI		＞サイトマスター
	//$email			＞ユーザー
	//$email_agent		＞担当代理店責任者
	//DEV_LOG_EMAIL		＞開発者
	$array = array(SITE_LOG_EMAIL, $email,$email_agent,DEV_LOG_EMAIL);
	
	foreach ($array as $var) {
		tep_mail($email, $var, $EmailSubject, $EmailTextAll, SITE_OWNER, SITE_OWNER_EMAIL);
	}
}
?>