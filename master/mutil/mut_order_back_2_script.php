<script src="../js/MyHelper.js"></script>
<script src="../js/popup.js"></script>
<script>
	var D    = JSON.parse('<?php echo $jsonOdr;?>');
	var sID  = '';
	var dID  = '';
	var Cat  = new jSendPostFormAj('xml_user_order_back.php','jEditForm');
	var bCur = '<?php echo $base_currency;?>';

	function jSendEditData(){
		var Obj = Cat.sendPost();
		Obj.done(function (jtext) {
			var res = jtext.replace(/\s|　/g,"");
			if(res == 'ok'){alert('登録しました');location.reload();}else{alert('有効なデータがありません');}
		})
		.fail(function (data, textStatus, errorThrown) {alert('登録できません');});
	}
	
	function jResetForm(){
		$('#oID').val('');
		$('#dBack').val('');
		$('#dCulc').val('');
		$('#jMemo').val('');
		$('#ActSend').prop('disabled',true);
	}
	
	
	function jCheckForm(){
		var A = jIsValue('dBack');
		var B = jIsValue('jMemo');
		var C = jIsValue('dCulc');
		
		var D0 = $('.o_num');
		var D  = 0;
		
		for(var i=0;i<D0.length;i++){
			if(D0[i].value > 0){D++;}
		}

		var F = A * B *C *D;
		return (F > 0)? false : true;
	}
	

	$('#jEditForm').on('change',function(){$('#ActSend').prop('disabled',jCheckForm());});
	$('#ActSend').on('click',function(){
		if(confirm('この内容で登録しますか？')){
			jSendEditData();
		}
	});
	
	$('.oprBack').on('click',function(){
		jResetForm();
		sID = $(this).attr('data-id');
		rw = D[sID];
		
		if(rw){
			loamt = (rw.oamt * 1).toLocaleString();
			lramt = (rw.ramt * 1).toLocaleString();
			
			pl_in = '';
			
			for(var oid in rw.plan){
				pdt = rw.plan[oid];
				
				pl_in+= '<tr>';
				pl_in+= '<td>'+pdt.plan_id+'</td>';
				pl_in+= '<td>'+pdt.name+'</td>';
				pl_in+= '<td>'+pdt.num+' 個'+'</td>';
				pl_in+= '<td>'+pdt.base_b+bCur+'</td>';
				pl_in+= '<td><input  type="number" name="pbnum['+pdt.plan_id+']" min="0" max="'+pdt.num+'" value="" class="o_num" style="width:40px;"> 個返品</td>';
				pl_in+= '</tr>';
			}
			
			pl_tb = '<table class="notable">'+pl_in+'</table>'
			
			$('#oID').val(sID);
			$('#Subject').html('【'+sID+'】'+rw.uname);
			$('#pName').html(rw.pname);
			$('#oNum').html(rw.oid);
			$('#uID').html(rw.uid);
			$('#uName').html(rw.uname);
			$('#oAppli').html(rw.dappli);
			$('#oAmount').html(loamt+' '+bCur);
			$('#amtReceive').html(lramt+' '+bCur);
			$('#dReceive').html(rw.dreceive);
			$('#oItem').html(pl_tb);
		}
	});
</script>