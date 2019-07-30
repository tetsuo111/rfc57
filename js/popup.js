//▼閉じる処理
function ClosePop(){
	$("#Subject").html();
	$("#ToList").html();
	$("#ReadList").html();
	$("#YetList").html();
	$("#popup").css("display","none");
	$("#popcontain").css("display","none");
}

//▼結果の表示
function ShowPop(){
	//▼ポップアップ
	$("#popup").css("display","inline");
	$("#popcontain").css("display","inline");
}

//▼閉じる処理
$("#popcontain").on('click',function(){ClosePop();});
$("#PopClose").on('click',function(){ClosePop();});