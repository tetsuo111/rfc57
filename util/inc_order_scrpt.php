<script>
	var P    = JSON.parse('<?php echo $jsonPayment;?>');
	var R    = JSON.parse('<?php echo $jsonRate;?>');
	var C    = JSON.parse('<?php echo $jsonCurrency;?>');
	var Sum  = JSON.parse('<?php echo $jsonSum;?>');
	var CH   = JSON.parse('<?php echo $jsonCharge;?>');
	var CurN = <?php echo count($cur_ar);?>;
	var oSort = '<?php echo $js_sort;?>';
	
	//var oSum    = '<?php echo $jsonDetail;?>';
	var cSingle = JSON.parse('<?php echo $jsonCsingle;?>');
	var pwSel   = [cSingle];
	var baseCur = '<?php echo $base_cur;?>';
	var posre   = '';
	
	function ljDisSelect(ak){
		if(ak){
			for(var i=0;i < ak.length;i++){
				if(!ak[i].selected){ak[i].disabled=true;}
			}
		}
	}
	
	function ljDisRadio(ak){
		if(ak){
			for(var i=0;i < ak.length;i++){
				if(!ak[i].checked){ak[i].disabled=true;}
			}
		}
	}
	
	//total show
	function ljSetRate(Curid){
		rtotal = 0;
		for(var cur in Sum){rtotal+= Sum[cur].base * 1;}
		rated    = (rtotal * R[Curid].rate).toLocaleString()+' '+R[Curid].name;
		showRate = '1'+R[Curid].name+'='+(1 / (R[Curid].rate)*1)+baseCur;
		
		//total
		$('#pRate').html(showRate);
		$('#pTotal').html(rated);
	}
	
	//Devide into small amount
	function ljInitSmall(Curid){
		$('#pWay').html('');
		if(Curid == 'a'){
			$('#dPay').addClass('isShow');
			$('#dPay .sAmt,#dPay sCur').val('');
			$('#dPay').toggle(600);
			
			sSmall = document.getElementsByClassName('sSmall');
			PW2    = '';
			for(var j=0;j<sSmall.length;j++){
				sb = sSmall[j].getAttribute('id-cur');
				PW2+= ljPaymentSelect(sb);
			}
			$('#pWay').html(PW2);
			
		}else{
			ljSetRate(Curid);
			if($('#dPay').hasClass('isShow')){
				$('#dPay').removeClass('isShow');
				$('#dPay').toggle(600);
			}
		}
	}
	
	function ljErrSmall(){
		
		err = false;
		for(var bb in Sum){
			ma = $('input[name="mamt['+bb+']"]').val();
			mc = $('input[name="mcur['+bb+']"]').val();
				
			sa = $('input[name="samt['+bb+']"]').val();
			sc = $('select[name="scur['+bb+']"]').val();
			
			if(ma > 0 && mc == ''){err = true;}
			if(sa > 0 && sc == ''){err = true;}
		}
		
		return err;
	}
	
	//make payment select setting
	function ljPaymentSelect(ttlAr){
		
		ppww = '';
		chh  = [];
		if(CH){
			for(var c in CH){
				chh.push(CH[c].payid);
			}
		}
		
		okk = 0;
		for(var n in ttlAr){
			rw    = ttlAr[n];
			
			if(rw.amt){
				k     = rw.curid;
				selIn = '';
				tPar  = P[k];
				for(var v in tPar){
					ch_p = (chh.indexOf(v) > -1)? 'checked' : '';
					selIn+= '<p><input type="radio" class="i_radio" onchange="pWSelectCheck();" name="pay['+k+']" value="'+v+'" '+ch_p+'> '+tPar[v]+'</p>';
					if(ch_p){okk++;}
				}
				
				cl = ((n % 2) != 0)? 'class="cl2"':'';
				
				ppww+= '<tr '+cl+'>';
				ppww+= '<td>'+rw.amt.toLocaleString()+' '+rw.curname+'の支払</td>'
				ppww+= '<td>'+selIn+'</td>';
				ppww+= '</tr>';
			}
		}
		
		if(okk){
			$('#nxtPs4').prop('disabled',false);
		}else{
			$('#nxtPs4').prop('disabled',true);
		}
		
		return '<table class="notable">'+ppww+'</table>';
	}
	
	function ljCulcRatedAmt(cFrom,cTo,A){
		if(cFrom == cTo){
			t = A * 1;
		}else{
			
			if(cTo == '0'){
				//amt:cur
				//cur -> base
				t = A / R[cFrom].rate;
			}else{
				//amt:base
				//base -> cur
				t = A * R[cTo].rate;		//base to cur
			}
		}
		return t;
	}
	
	function lzChangePayment(){
		
		aa    = $('input[type="radio"][name="cur"]:checked').val();
		PW3   = '';
		pwSel = [];
		o_num = 1;
		
		if(aa == 'a'){

			tPW   = {};
			tPttl = {};
			
			
			for(var bb in Sum){
				//main
				ma = $('input[name="mamt['+bb+']"]').val() * o_num;
				mc = $('input[name="mcur['+bb+']"]').val();
				
				//sub
				sa = $('input[name="samt['+bb+']"]').val();
				sc = $('select[name="scur['+bb+']"]').val();
				
				if(mc != '' && !tPttl[mc]){tPttl[mc] = 0;}
				if(sc != '' && !tPttl[sc]){tPttl[sc] = 0;}
				
				if(ma > 0 && mc != ''){tPttl[mc]+= ljCulcRatedAmt(bb,mc,ma);}
				if(sa > 0 && sc != ''){tPttl[sc]+= ljCulcRatedAmt(bb,sc,sa);}
			}
			
			if(Object.keys(tPttl).length > 0){
				for(var k in tPttl){
					if(tPttl[k]){
						o_total = {curid:k,curname:R[k].name,amt:tPttl[k],rate:R[k].rate};
						pwSel.push(o_total);
					}
				}
			}
			
		}else{
			
			ljInitSmall(aa);
			ttotal  = 0;
			
			//aa > to currency
			for(var ba in Sum){
				td = Sum[ba];
				ttotal += ljCulcRatedAmt(ba,aa,td.sum) * o_num;
			}
			
			o_total = {curid:aa,curname:R[aa].name,amt:ttotal,rate:R[aa].rate};
			pwSel.push(o_total);
			
			ak = $('input[type="radio"][name="cur"]');
			ljDisRadio(ak);
		}
		
		PW3 = ljPaymentSelect(pwSel);
		$('#nxtPs3').prop('disabled',false);
		$('#pWay').html(PW3);
		return aa;
	}
	
	function pWSelectCheck(){
		i = 0;
		
		for(var k in pwSel){

			kc = $('input[type="radio"][name="pay['+pwSel[k].curid+']"]:checked').val();
			if(kc){
				pwSel[k]['payid'] = kc;
				i++;
			}
		}
		
		if(i == pwSel.length){
			dis = false;
		}else{
			dis = true;
		}
		
		$('#nxtPs4').prop('disabled',dis);
	}
	
	
	//Pyament cur
	$('input[type="radio"][name="cur"]').on('change',function(){
		
		aa  = $('input[type="radio"][name="cur"]:checked').val();
		
		//Payment small
		if(aa == 'a'){
			
			$('.sAmt').on('change',function(){
				
				//change amount
				bb  = $(this).attr('id-cur');		//curid
				cc  = $(this).val();				//amount
				dd  = as = Sum[bb].sum;				//main
				def = dd - cc;
				
				if(def > -1){
					$('input[name="mamt['+bb+']"]').val(def);
				}
			});
			
		}
		
		//Initialize payment small
		ljInitSmall(aa);
		$('#ps3').fadeOut(800);
		$('#ps4').fadeOut(800);
		$('#nxtPs3').prop('disabled',false);
	});
	
	//user actions
	$('select[name="order_num"]').on('change',function(){
		nn  = $(this).val();
		dis = (nn)? false:true;
		$('#nxtPs2').prop('disabled',dis);
	});
	
	$('#nxtPs2').on('click',function(){
		if(CurN > 1){
			$(this).prop('disabled',true);
			$('#ps2').toggle(800);
		}
		
		if(CurN == 1){
			$('#ps3').toggle(800);
		}
	});

	$('#nxtPs3').on('click',function(){
		
		
		if(lzChangePayment() == 'a'){
			if(err = ljErrSmall()){
				alert('支払通貨を選択してください');
			}else{
				$('.sAmt').prop('readonly',true);
				sc = $('select[name*="scur["]');
				ljDisSelect(sc);
			}
		}else{
			err = false;
		}
		
		if(!err){
			ak = $('input[name="cur"]');
			ljDisRadio(ak);
			$(this).prop('disabled',true);
			$('#ps3').toggle(800);
		}
	});
	
	
	$('#nxtPs5').on('click',function(){
		
		sc = $('input[type="radio"][name*="pay["]:checked');

		if(sc){
			
			$(this).prop('disabled',true);
			
			posre     = JSON.stringify(pwSel);
			var Cat2  = new jSendPostDataAj('xml_payment_fee.php');
			var sData = {top: 'pfee',payid: posre};
			var Obj2  = Cat2.sendPost(sData);
			
			
			Obj2.done(function(response){
				
				res = JSON.parse(response.trim());
				rPff = '';

				if(res.status == 'ok'){
					
					if(res.pfee){
						
						rPff = res.pfee;
						var pf_in  = '';
						for(var i=0; i<rPff.length; i++){
							
							var pf_sum = 0;
							rPff2 = rPff[i];
							for(var j=0; j<rPff2.length; j++){
								ttp = rPff2[j];
								pf_in+= '<p>'+ttp.name+' '+ttp.amt+' '+baseCur+'</p>';
								pf_sum+= ttp.amt;
							}
							
							pwSel[i]['amt']+= pf_sum;
						}
						
						$('#pFee').html(pf_in);
					}
					
					r_in = '';
					t_in = '';
					for(var n in pwSel){
						TT   = pwSel[n];
						tt_amt = (TT.amt)? TT.amt.toLocaleString(): 0;
						r_in+= '<p>1 '+TT.curname+' = '+(1 / TT.rate)+' '+baseCur+'</p>';
						t_in+= '<p>'+tt_amt+' '+TT.curname+'</p>';
					}
					
					ak   = $('input[name*="pay["]');
					ljDisRadio(ak);
					oFee = JSON.stringify(rPff);
					oPay = JSON.stringify(pwSel);

					$('#odrFee').val(oFee);
					$('#odrPayment').val(oPay);
					$('#pRate').html(r_in);
					$('#pTotal').html(t_in);
					$('#ps5').toggle(800);
					
					if(sc.attr('on-limit') == 1){
						$('#sdLimit').css('display','table-row');
					}else{
						$('#sdLimit').css('display','none');
					}
					
					$('#Act').prop('disabled',false);
					
				}else{
					alert('データの設定に不備があります');
				}
			})
			.fail(function(){alert('アクセスできません');});
		}else{
			alert('支払方法を選んでください');
		}
	});
	
	$('#dLimit').on('change',function(){
		aa = $(this).val();
		dis = (aa)? false:true;
		$('#Act').prop('disabled',dis);
	});
	
	$('#nxtPs4').on('click',function(){
		$(this).prop('disabled',true);
		$('#ps4').toggle(800);
	});
	
	//ship
	$('.sSip').on('change',function(){
		aa  = $('input[type="radio"][name="ssip"]:checked').val();
		dis = (aa)? false:true;
		$('#nxtPs5').prop('disabled',dis);
	});
	
	$('.addSip').on('click',function(){
		aa = $(this).attr('ship-data');
		$('#selSip').val(aa);
		
		if(aa == 'a'){
			$('#nxtPs5').prop('disabled',false);
			$('#regShip').slideUp(800,function(){lzResetSip();$('#regAddr').delay(200).slideDown(800);});
		}else{
			$('#nxtPs5').prop('disabled',true);
			$('#regAddr').slideUp(800);
			$('#regShip').slideDown(800);
		}
	});
	
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
						
						$('#Apref').val(Char[0]);
						$('#Acity').val(Char[1]);
						$('#Aarea').val(Char[2]);
						alert("自動入力しました");
					}
				}
				
				
				if(!$('#aFromZip').hasClass('addShow')){
					$('.add_in').slideToggle(800);
				}
				
				$('#aFromZip').addClass('addShow');
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
		$('#nxtPs5').prop('disabled',dis);
	}
	
	$('.iShip').on('change',function(){lzChecksSip();});
</script>