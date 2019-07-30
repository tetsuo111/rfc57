<script>
$('#Act').on('click',function(){
	/*
	var Cat = new jSendPostDataAj('xml_order_check.php');
	lM = $('#dLimit').val();
	oN = $('#oNum').val();
	
	var sData = {
			top   : 'ordrr',
			planid:'<?php echo $m_plan_id;?>',
			oNum  :oN,
			oLimit:lM,
			oPay  :posre
		};
	*/
	
	var Cat = new jSendPostFormAj('xml_order_check.php','OdrForm');
	var Obj = Cat.sendPost();
	
	Obj.done(function(response){
		
		res = JSON.parse(response.trim());
		
		if(res.status == 'ok'){
			
			if(confirm('この内容で注文しますか')){
				document.odr_form.submit();
			}
			
		}else{
			alert('データの設定に不備があります');
		}
	})
	.fail(function(){alert('データの確認ができません');});
});

function lzOdrDel(A){
	if(confirm('この注文を削除しますか')){
		var B = 'dlF'+A;
		document.forms[B].submit();
	}
}
</script>