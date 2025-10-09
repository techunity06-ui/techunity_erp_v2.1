<?php
	session_start();
	include("../../config/config.php");
	include("../../config/session.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php
		$TITLE = "Printing ...";
		//include("../common/head.php");
	?>
	<title>Printing ...</title>
	<!-- Bootstrap core CSS -->
	<link href="<?php echo DOMAIN_CHEQUE; ?>js/bootstrap/dist/css/bootstrap.css" rel="stylesheet" media="all"> 
	<link href="<?php echo DOMAIN_CHEQUE; ?>css/style.css" rel="stylesheet" media="all" />
	
	
	<link href='css/jquery-ui.min.css' rel='stylesheet' type='text/css' media="all">
	<style type="text/css">
		@media print
		{
			@page
			{
				margin:0;
			}
			
			body {
				display: block;
				padding-top: 0px;
				height: 8.05in;
				width: 3.85in;
				margin:0px auto;
				/*border: 2px dashed;*/
			}
			
			.hidden {
					display:none;
			}
			#cheque {
				display: inline-block;
				/*margin-top: 20px;*/
				padding:0px;
				font-family: 'Roboto', sans-serif;
				width:8.05in;
				height:3.85in;
				vertical-align: middle
			}
			.cheque-item {
				position: absolute;
				display:block;
				margin:0px;
				padding:0px;
				/*background:rgba(0,0,0,0.2);
				cursor:pointer;
				color:#fff;*/
				background:rgba(0,0,0,0);
				color:#000;
				font-size: 12px;
				line-height: 12px;
			}
			
			#date {
				font-size: 13px;
				line-height: 12px;
			}
			
			#amount-number {
				font-size:16px;
				line-height: 16px;
			}
			
			.vertical {
				
				/* FF Chrome Opera etc top:6.5cm; */
				-webkit-transform: rotate(90deg); 
				-moz-transform: rotate(90deg);
				-o-transform: rotate(90deg);
				/* IE */
				filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
			}
			
			#img_nn {
				position: absolute;
				top : 15px;
				left : 300px;
                width:100px;
			}
			
			#img_ac_f {
				position: absolute;
				top : 15px;
				left : 300px;
				width:100px;
			}
			
			#img_ac_e {
				position: absolute;
				top : 0px;
				left : 15px;
			}
			
			#not_more_than {
				position: absolute;
				font-size:12px;
			}
			
			.ui-widget-content {
				border: none;
			}
			
			.cmark {
				border-top:solid 1px #000;border-bottom:solid 1px #000;
				font-size:12px;
				line-height: 13px;
				text-align:center;
			}
			b:{
			font-weight:600;
			}
		}

		
		body {
			display: block;
			padding-top: 0px;
			height: 8.05in;
			width: 3.85in;
			margin:0px auto;
			/*border: 2px dashed;*/
		}
		
		.hidden {
				display:none;
		}
		#cheque {
			display: inline-block;
				/*margin-top: 20px;*/
			padding:0px;
			font-family: 'Roboto', sans-serif;
			width:8.05in;
			height:3.85in;
			vertical-align: middle
		}
		.cheque-item {
			position: absolute;
			display:block;
			margin:0px;
			padding:0px;
			/*background:rgba(0,0,0,0.2);
			cursor:pointer;
			color:#fff;*/
			background:rgba(0,0,0,0);
			color:#000;
			font-size: 12px;
			line-height: 12px;
		}
		
		#date {
			font-size: 13px;
			line-height: 12px;
		}
		
		#amount-number {
			font-size:16px;
			line-height: 16px;
		}
		
		.vertical {
			
			/* FF Chrome Opera etc top:5.5cm; */
			-webkit-transform: rotate(90deg); 
			-moz-transform: rotate(90deg);
			-o-transform: rotate(90deg);
			/* IE */
			filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
		}
		
		#img_nn {
			position: absolute;
			top : 15px;
			left : 300px;
		}
		
		#img_ac_f {
			position: absolute;
			top : 15px;
			left : 300px;
			width:100px;
		}
		
		#img_ac_e {
			position: absolute;
			top : 0px;
			left : 15px;
		}
		
		#not_more_than {
			position: absolute;
			font-size:12px;
		}
		
		.ui-widget-content {
			border: none;
		}
		
		.cmark {
			border-top:solid 1px #000;border-bottom:solid 1px #000;
			font-size:12px;
			line-height: 13px;
			text-align:center;
		}
		b:{
			font-weight:600;
			}

	</style>
</head>
<body onLoad="">
	<?php
		 
		 $query = $dbcon -> query("SELECT cheque_id, company_name, acc_name, bank_name,paytype, cheque_number, cheque_acc, acc_number, cheque_date, cheque_payee, ROUND(cheque_amount) as cheque_amount, cheque_mode, cheque_morethen, cheque_iscancel, cheque_tmst, cheque_of FROM coro_cheques left JOIN `account_mst` as acc ON `acc_id` = `cheque_acc` left JOIN `bank_mst` as bank  ON bank.`bankid` = acc.`bankid` left JOIN `tbl_customer` ON `cust_id` = `cheque_payee` WHERE `cheque_id` = '$_GET[id]'");
		$r = $query->fetch_assoc();
		if($r['paytype']=="Acc"){
			 $qary = $dbcon -> query("SELECT * from account_mst where acc_id=".$r['cheque_payee']);
		$ro = $qary->fetch_assoc();
		if($r['cheque_payee']=="1"){
			$vendor_name="SELF";
		}else{
		 $vendor_name=$ro['acc_name'];
		}
		}else{
			$vendor_name=$r['company_name'];
		}
		$query_acc="SELECT acc_type FROM account_mst as acc where acc_id=".$r['venderacc_id'];
		$rs_acc=$dbcon->query($query_acc);
		 $rel_acc=mysqli_fetch_assoc($rs_acc);
		if($rel_acc['acc_type']==1) 
		{
			   $vendor_name='SELF';
		}
	?>
	<div id="output" class="vertical">
		<div id="cheque">
			<div id="payee" class="ui-widget-content cheque-item"><b><?php echo $vendor_name; ?></b></div>
			<div id="date" class="ui-widget-content cheque-item"><?php echo str_replace("-","",date("d-m-Y",strtotime($r['cheque_date']))); ?></div>
			<div id="amount-number" class="ui-widget-content cheque-item"><?php echo '**'.indian_number($r['cheque_amount'],0).'/-**'; ?></div>
			<div id="amount-text" class="ui-widget-content cheque-item"></div>
			<?php
				switch($r['cheque_mode']) {
					case 1:
						//nothing to do ;)
						break;
					case 2:
						echo '<div id="bearer" class="ui-widget-content cheque-item">*************</div>';
						echo '<img src="'.DOMAIN_CHEQUE.'cheque_marks/ac_payee_e.png" id="img_ac_e" />';
						break;
					case 3:
						echo '<div id="bearer" class="ui-widget-content cheque-item">*************</div>';
						echo '<div id="img_ac_f" class="cmark mark">A/C PAYEE ONLY</div>';
						break;
					case 4:
						echo '<div id="bearer" class="ui-widget-content cheque-item">*************</div>';
						echo '<div id="img_nn" class="cmark mark">NOT NEGOTIABLE<br>A/C PAYEE ONLY</div>';
						break;
				}
								
				if($r['cheque_morethen']) {
					echo '<div id="not_more_than" class="ui-widget-content cheque-item"><b>NOT OVER THAN '.indian_number($r['cheque_amount'],0).'/-</b></div>';
				}
			?>
			
		</div>
	</div>
	<script type="text/javascript" src="js/jquery.js"></script>

	
	<script type="text/javascript">
	
	function frac(f) {
		return f % 1;
	}
	
	function convert_number(number)
	{
		if ((number < 0) || (number > 999999999)) 
		{ 
			return "NUMBER OUT OF RANGE!";
		}
		var Gn = Math.floor(number / 10000000);  /* Crore */ 
		number -= Gn * 10000000; 
		var kn = Math.floor(number / 100000);     /* lakhs */ 
		number -= kn * 100000; 
		var Hn = Math.floor(number / 1000);      /* thousand */ 
		number -= Hn * 1000; 
		var Dn = Math.floor(number / 100);       /* Tens (deca) */ 
		number = number % 100;               /* Ones */ 
		var tn= Math.floor(number / 10); 
		var one=Math.floor(number % 10); 
		var res = ""; 

		if (Gn>0) 
		{ 
			res += (convert_number(Gn) + " CRORE"); 
		} 
		if (kn>0) 
		{ 
				res += (((res=="") ? "" : " ") + 
				convert_number(kn) + " LAKH"); 
		} 
		if (Hn>0) 
		{ 
			res += (((res=="") ? "" : " ") +
				convert_number(Hn) + " THOUSAND"); 
		} 

		if (Dn) 
		{ 
			res += (((res=="") ? "" : " ") + 
				convert_number(Dn) + " HUNDRED"); 
		} 


		var ones = Array("", "ONE", "TWO", "THREE", "FOUR", "FIVE", "SIX","SEVEN", "EIGHT", "NINE", "TEN", "ELEVEN", "TWELVE", "THIRTEEN","FOURTEEN", "FIFTEEN", "SIXTEEN", "SEVENTEEN", "EIGHTEEN","NINETEEN"); 
	var tens = Array("", "", "TWENTY", "THIRTY", "FOURTY", "FIFTY", "SIXTY","SEVENTY", "EIGHTY", "NINETY"); 
		
		if (tn>0 || one>0) 
		{ 
			if (!(res=="")) 
			{ 
				res += " AND "; 
			} 
			if (tn < 2) 
			{ 
				res += ones[tn * 10 + one]; 
			} 
			else 
			{ 

				res += tens[tn];
				if (one>0) 
				{ 
					res += (" " + ones[one]); 
				} 
			} 
		}
		
		if (res=="")
		{ 
			res = "ZERO"; 
		} 
		return res;
	}
	
	function UpdateAmount(amount) {
		var value = amount;
		
		var fraction = Math.round(frac(value)*100);
		var f_text  = "";
		
		if(fraction > 0) {
			console.log(fraction);
			f_text = "AND "+convert_number(fraction)+" PAISE";
		}
		
		$("#amount-text").html(convert_number(value)+" "+f_text+" ONLY");
		$("#not_more_than").html("NOT OVER THAN "+value+"/-");
	}
	
	var root_DOMAIN_CHEQUE = '<?php echo DOMAIN_CHEQUE; ?>';
	var reply;
	var get_id = '<?php echo $_GET['id']; ?>';
		$(document).ready(function() {
			
			var form_data = {
					type: "print",
					id : get_id
			};
			$.ajax({
				type: "POST",
				cache:false,
				url: root_DOMAIN_CHEQUE+'app/cheque/new',
				data: form_data,
				success: function(response)
				{
					console.log(response);
					reply = jQuery.parseJSON(response);
					if(response == 0) {
					
					}
					else {
						$("#print_preview").removeClass("hidden");
						$("#no_preview").hide();
						$("#payee").attr("style","position: absolute;width:"+reply.design_payeeWidth+"px;top:"+reply.design_payee.top+"px;left:"+reply.design_payee.left+"px;");
						$("#date").attr("style","position: absolute;width:"+reply.design_dateWidth+"px;top:"+reply.design_date.top+"px;left:"+reply.design_date.left+"px;letter-spacing:"+reply.design_dateLspace+"px;");
						$("#amount-text").attr("style","position: absolute;width:"+reply.design_amount_textWidth+"px;top:"+reply.design_amount_text.top+"px;left:"+reply.design_amount_text.left+"px;text-indent:"+reply.design_amount_textIndent+"px;line-height:"+reply.design_amount_textLHeight+"px;");
						UpdateAmount(<?php echo $r['cheque_amount']; ?>);
						console.log("<?php echo $r['cheque_amount']; ?>");
						$("#amount-number").attr("style","position: absolute;width:"+reply.design_amount_numberWidth+"px;top:"+reply.design_amount_number.top+"px;left:"+reply.design_amount_number.left+"px;");
						$("#bearer").attr("style","position: absolute;width:"+reply.design_bearerWidth+"px;top:"+reply.design_bearer.top+"px;left:"+reply.design_bearer.left+"px;");
						$("#not_more_than").removeClass("hidden").attr("style","width:"+reply.design_notmoreWidth+"px;top:"+reply.design_notmore.top+"px;left:"+reply.design_notmore.left+"px;");
						$(".mark").attr("style","position:absolute;top:"+reply.design_mark.top+"px;left:"+reply.design_mark.left+"px;");
						$('#cheque').css("background", "rgba(255,255,255,0.5) url(upload/check/"+reply.design_preview+") no-repeat");
						$('#cheque').css("background-size", "100% 100%");
						$("#no_preview").hide();
						window.print();
					}
				}					
			});
		});
	</script>
</body>
</html>