//値があるかを確認する
function jIsValue(A){

	var R = ($('#'+A).val() != '')? 1 : 0;
	return R;

};


//ラジオボタンの値があるかを確認する
function jIsValueRadio(B){

	var R = ($('input[name="'+B+'"]:checked').val())? 1 : 0;
	return R;

};


//メニューの表示
$('#onMobMenu').on('click',function(){
	$('#left1').toggleClass('isOpen');
});

$('.u_menu_a').on('click',function(){
	$('#left1 .isOpen').css('display','none');
});

$('#left1').on('click',function(){
	$('#left1').toggleClass('isOpen');
});


//フォームのポスト送信クラス
function jSendPostFormAj(Surl,formID){
	this.setInit(Surl,formID);
};

jSendPostFormAj.prototype = {
	setInit: function(Surl,formID){
		this._url = Surl;
		this._formid = formID;
	},
	
	sendPost: function(){
		
		var Sform = new FormData($('#'+this._formid).get(0));
		
		if((this._url != '') && (Sform)){

			//▼POSTでアップロード
			return $.ajax({
				url  : this._url,
				type : "POST",
				contentType : false,
				processData : false,
				dataType: "text",
				data : Sform
			});
			
		}else{
			
			return false;
		}
	}
};


//データのポスト送信クラス
function jSendPostDataAj(Surl){
	this.setInit(Surl);
};

jSendPostDataAj.prototype = {
	
	//▼初期設定
	setInit: function(Surl){
		this._url = Surl;
	},
	
	//▼データ送信
	sendPost: function(SData){
		
		//データを判定して送信
		if(Object.keys(SData).length > 0){
			
			return $.ajax({
				url: this._url,
				type:"POST",
				dataType:"text",
				data:SData
			});
			
		}else{
			return false;
		}
	}
};
