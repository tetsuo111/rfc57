<?php
	
	//▼リスト見出し
	$list_head ='<th>注文<br>番号</th>';
	$list_head.='<th>顧客<br>番号</th>';
	$list_head.='<th>顧客名</th>';
	$list_head.='<th>会員ID</th>';
	$list_head.='<th>種類</th>';
	$list_head.='<th>注文日</th>';
	$list_head.='<th>注文金額<br>換算金額</th>';
	$list_head.='<th>入金<br>予定日</th>';
	$list_head.='<th>入金<br>金額</th>';
	$list_head.='<th>入金<br>完了日</th>';
	$list_head.='<th>計算日</th>';
	$list_head.='<th>出荷日</th>';
	$list_head.='<th>メモ</th>';
	$list_head.='<th>状況</th>';
	$list_head.='<th style="text-align:center;">操作</th>';


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
	$search_box.= '<input type="text"   style="width:100px; padding:5px 5px;" name="s_fsid" value="'.$s_fsid.'"> ';
	$search_box.= '<input type="submit" style="width:60px;  padding:5px 0px;" value="検索"> ';
	$search_box.= '<input type="button" style="width:60px;  padding:5px 0px;" value="リセット" OnClick="location.href=\''.$form_action_to.'\'"> ';
	$search_box.= '</form>';
	$search_box.= '</div>';


	//----- Pop -----//
	$disabled = 'disabled';

	//▼登録フォーム
	$edit_form = '<div class="form_outer">';
	$edit_form.= '<form name="j_edit_form" id="jEditForm">';
	$edit_form.= $input_auto;
	$edit_form.= '<input type="hidden" name="order_id" value="" id="oID">';
	$edit_form.= '<table class="input_form">';
	$edit_form.= '<tr><th>注文番号</th>  <td><span id="oNum"></span></td></tr>';
	$edit_form.= '<tr><th>顧客番号</th>  <td><span id="uID"></span></td></tr>';
	$edit_form.= '<tr><th>顧客名</th>    <td><span id="uName"></span></td></tr>';
	$edit_form.= '<tr><th>注文日</th>    <td><span id="oAppli"></span></td></tr>';
	$edit_form.= '<tr><th>入金完了日</th><td><span id="dReceive"></span></td></tr>';
	$edit_form.= '<tr><th>注文金額</th>  <td><span id="oAmount"></span></td></tr>';
	$edit_form.= '<tr><th>入金金額</th>  <td><span id="amtReceive"></span></td></tr>';
	$edit_form.= '<tr><th>返品日'.I_MUST.'</th>    <td><input type="text" name="date_back" value="" id="dBack" size="6"></td></tr>';
	$edit_form.= '<tr><th>計算日'.I_MUST.'</th>    <td><input type="text" name="date_culc" value="" id="dCulc" size="6"></td></tr>';
	$edit_form.= '<tr><th>メモ'.I_MUST.'</th>  　  <td><input type="text" name="memo" value="" id="jMemo"></td></tr>';
	$edit_form.= '<tr><th>購入商品</th>  <td><div id="oItem"></div></td></tr>';
	$edit_form.= '</table>';

	$edit_form.= '<div class="spc20">';
	$edit_form.= '<input type="button" value="返品の内容を登録する" id="ActSend"  '.$disabled.'>';
	$edit_form.= '</div>';

	$edit_form.= '</form>';
	$edit_form.= '</div>';


	//▼設定
	$p_obj = new mkPop;
	$p_obj->subject    = '入金確認';
	$p_obj->popcontens = $edit_form.$done_form;

	//▼登録ポップ
	$pop = $p_obj->getPop();

	//▼引継ぎ用
	$jsonOdr    = json_encode($j_order_ar);

?>