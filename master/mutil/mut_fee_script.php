<script>
var cFo  = '<?php echo $culc_base;?>';
var FeeA = document.getElementById('FeeA');

function jCheCulc(){
	A  = $('select[name*="culc["]');
	A2 = $('input[name*="culc["]');
	B  = 1;
	C  = [];
	
	for(var i=0;i<A.length;i++){
		AA  = A[i].value;
		AA2 = A2[i].value;
		
		if(C.indexOf(AA) > -1){
			alert('同じ手数料が設定されています');
			return;
		}else{
			B = B * ((AA && AA2)? 1:0);
			C.push(AA);
		}
	}
	
	Dis = (B)? false:true;
	$('#AddCulc').prop('disabled',Dis);
	
	if(i > 1){
		if(Dis){
			$('#RmvCulc').fadeIn(600);
		}else{
			$('#RmvCulc').fadeOut(600);
		}
	}
}

function jAddCulc(){
	A = $('select[name*="culc["]');
	bcFo = cFo.replace(/[A]/g,A.length);
	$('#FeeA').append(bcFo);
}

$(document).on('change','.culcs',function(){jCheCulc();});
$('#AddCulc').on('click',function(){jAddCulc();jCheCulc();});
$('#RmvCulc').on('click',function(){FeeA.removeChild(FeeA.lastChild);
	$('#RmvCulc').css('display','none');jCheCulc();
});
jCheCulc();
</script>