<script>
//ship
$('.adZip').on('keyup',function(){
	fza = $('#Za').val();
	fzb = $('#Zb').val();
	dis = (fza.length==3 && fzb.length==4)? false:true;
	$('#aFromZip').prop('disabled',dis);
});

$('#aFromZip').on('click',function(){
	
	var Za = $('#Za').val();
	var Zb = $('#Zb').val();
	if(Za && Zb){
		$.ajax({
			url : 'zip_post.php',
			type:'POST',
			dataType: 'json',
			data: {
				top:"ziproma",
				zip:Za+Zb
			}
		})
		.done(function (response) {
			
			var res = response.result;
			if(res != 'ok'){
				alert("該当する住所がありません");
				
			}else{
				
				var Char = response.ZipData.Char;
				
				if(Char[0] == ""){
					alert("該当する住所がありません");
					
				}else{
					
					alert("自動入力しました");
					$('#Apref').val(Char[0]);
					$('#Acity').val(Char[1]);
					$('#Aarea').val(Char[2]);
				}
			}
			
			lzChecksSip();
			
		}).fail(function (data, textStatus, errorThrown) {
			alert("データの取得に失敗しました");
		});
	
	}else{
		alert("郵便番号を入力してください");
	}
});


function lzResetSip(){
	$('#Za').val('');
	$('#Zb').val('');
	$('#Apref').val('');
	$('#Acity').val('');
	$('#Aarea').val('');
	$('#Astrt').val('');
	$('#Aname').val('');
	$('#Aphone').val('');
}

function lzChecksSip(){
	A = jIsValue('Za');
	B = jIsValue('Zb');
	C = jIsValue('Apref');
	D = jIsValue('Acity');
	E = jIsValue('Aarea');
	F = jIsValue('Aname');
	G = jIsValue('Aphone');
	
	if(G){
		aa = $('#Aphone').val();
		G  = (aa.match(/[^0-9-]+/))? 0:1;
	}
	
	AA = A * B * C * D * E * F *G;
	dis = (AA)? false:true;
	$('#Act').prop('disabled',dis);
}

$('.iShip').on('change',function(){lzChecksSip();});
</script>