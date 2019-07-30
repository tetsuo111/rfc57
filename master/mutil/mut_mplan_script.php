<script src="../js/MyHelper.js"></script>
<script>
	var D    = JSON.parse('<?php echo $jsonItem;?>');
	var R    = JSON.parse('<?php echo $jsonRate;?>');
	var P    = JSON.parse('<?php echo $jsonPlan;?>');
	var sID  =' <?php echo $m_plan_id;?>';
	var chSh = {a:'<span class="ng">非表示</span>',b:'<span class="ok">表示</span>'};
	var chAr = {a:'b',b:'a'};
	
	function ljsetPlan(){
		f = $('.itmF');
		tt = 0;
		tc = 0;
		for(var i=0;i<f.length;i++){
			if(f[i].value > 0){
				id   = f[i].getAttribute('id-data');
				num  = f[i].value * 1;
				rid  = D[id]['cur_id'];
				rate = 1 / R[rid];
				tt+= D[id]['famt'] * rate * num;	//sum fix
				tc+= D[id]['reso'] * rate * num;	//sum resorce
			}
		}
		
		tt = (tt > 0)? tt:'';
		tc = (tc > 0)? tc:'';
		$('#sumFix').val(tt);
		$('#sumReso').val(tc);
	}
	
	$('.itmF').on('change',function(){
		ljsetPlan();
	});
	
	if(sID){ljsetPlan();}
	
	$('#pID').on('change',function(){
		aa = $('#pID').val();
		if(P[aa]){alert('登録済のIDです');}
	});
	
	$('.chAble').on('click',function(){
		a0 = $(this).attr('id-data');
		a1 = $('#cD'+a0).val();
		
		Cat = new jSendPostDataAj('xml_plan_able.php');
		sData = {id:a0,able:a1};
		
		Obj = Cat.sendPost(sData);
		
		if(Obj){
			
			Obj.done(function(res){
				
				if(res == 'ok'){
					$('#sH'+a0).html(chSh[a1]);
					$('#cD'+a0).val(chAr[a1]);
				}else{
					alert('データの設定に不備があります');
				}
				
			}).fail(function(){
				alert('ネットワークに接続できません');
			});
			
		}else{
			alert('変更できません');
		}
	});
</script>