<?php
	session_start();
	include("../../config/config.php");
	include("../../config/session.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php
		$TITLE = "Preview ...";
		//include("../common/head.php");
	?>
	<title>Printing ...</title>
	<!-- Bootstrap core CSS -->
	<link href="<?php echo DOMAIN_CHEQUE; ?>js/bootstrap/dist/css/bootstrap.css" rel="stylesheet" media="all"> 
	<link href="<?php echo DOMAIN_CHEQUE; ?>css/style.css" rel="stylesheet" media="all" />
	
	
	<link href='<?php echo DOMAIN_CHEQUE; ?>css/jquery-ui.min.css' rel='stylesheet' type='text/css' media="all">
	<style type="text/css">
		#cheque_design {
			display: none;
		}
		
		#cheque {
			margin:0px;
			padding:0px;
			font-family: 'Roboto', sans-serif;
			/*background:rgba(255,255,255,0.5) url(upload/check/hdfc_bank.png) no-repeat;
			width:767.24px;
			height:351.49px;
			max-width:767.24px;
			max-height:351.49px;*/
			max-width:8.05in;
			height:3.85in;
		}
		.cheque-item {
			display: inline-block;
			position:absolute;
			margin:0px;
			padding:0px;
			background:rgba(0,0,0,0.2);
			color:#fff;
			cursor:pointer;
			font-size:13px;
			line-height: 13px;
		}	
		
		#amount-number {
			font-size:16px;
			line-height: 16px;
		}
		
		#date {
			font-size:13px;
			line-height: 13px;
		}
		
		#payee {
			font-size:13px;
			line-height: 13px;
		}
		
		#amount-text {
			font-size:13px;
			line-height: 13px;
		}
		
		#not-more {
			font-size: 12px;
			line-height: 12px;
		}
		.ui-icon, .ui-widget-content .ui-icon {
			background:none;
		}
	</style>
</head>
<body>
	<div class="col-sm-8" style="margin: 0px !important; padding: 0px !important;">
		<div id="output" class="">
			<div id="cheque">
				<div id="payee" class="ui-widget-content cheque-item"><?php echo COMPANY; ?></div>
				<div id="date" class="ui-widget-content cheque-item"><?php echo str_replace("-","",date("d-m-Y")); ?></div>
				<div id="amount-number" class="ui-widget-content cheque-item">99,99,999.00/-</div>
				<div id="amount-text" class="ui-widget-content cheque-item">NINETY-NINE LAKH NINETY-NINE THOUSAND NINE HUNDRED NINETY NINE RUPEE ONLY</div>
				<div id="bearer" class="ui-widget-content cheque-item">*************</div>
				<div id="marking" class="ui-widget-content cheque-item"><img src="<?php echo DOMAIN_CHEQUE; ?>cheque_marks/ac_nn_f.png" id="img_nn" class="mark" /></div>
				<div id="not-more" class="ui-widget-content cheque-item"><b>NOT OVER THAN 99,99,999.00/-</b></div>
			</div>
		</div>
	</div>
	<div class="col-sm-4">
			<form method="post" id="FromGenerateDesign" role="form" parsley-validate novalidate> 
			<div class="form-group">
				<label>DATE LETTER SPACE</label>
				<div class="input-group input-group-sm">
					<input type="text" id="date-letter-space" onKeyUp="updateUI()" placeholder="space between date" class="form-control" parsley-trigger="change" required>
					<span class="input-group-addon">px</span>
				</div>
			</div>
			<div class="form-group">
				<label>AMOUNT TEXT INDENT</label>
				<div class="input-group input-group-sm">
					<input type="text" id="amount-indent" onKeyUp="updateUI()" placeholder="space between date" class="form-control" parsley-trigger="change" required>
					<span class="input-group-addon">px</span>
				</div>
			</div>
			<div class="form-group">
				<label>AMOUNT LINE HEIGHT</label>
				<div class="input-group input-group-sm">
					<input type="text" id="amount-line-height" onKeyUp="updateUI()" placeholder="space between date" class="form-control" parsley-trigger="change" required>
					<span class="input-group-addon">px</span>
				</div>
			</div>
			<div class="form-group">
				<input type="hidden" id="design_id" name="design_id" value="" />
				<button type="submit" id="generate_new_design" name="generate_new_design" class="btn btn-defailt btn-flat">Update</button>
			</div>
		</form>
	</div>
	
	<?php include("../common/js.php"); ?>

	
	<script type="text/javascript">
	var root_domain = '<?php echo DOMAIN_CHEQUE; ?>';
	var reply;
	var get_id = '<?php echo $_GET['id']; ?>';
		$(document).ready(function() {
			
			var form_data = {
					type: "edit_preview",
					id : get_id
			};
			$.ajax({
				type: "POST",
				cache:false,
				url: root_domain+'app/cheque/new',
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
						$("#amount-number").attr("style","position: absolute;width:"+reply.design_amount_numberWidth+"px;top:"+reply.design_amount_number.top+"px;left:"+reply.design_amount_number.left+"px;");
						$("#bearer").attr("style","position: absolute;width:"+reply.design_bearerWidth+"px;top:"+reply.design_bearer.top+"px;left:"+reply.design_bearer.left+"px;");
						$("#not-more").removeClass("hidden").attr("style","width:"+reply.design_notmoreWidth+"px;top:"+reply.design_notmore.top+"px;left:"+reply.design_notmore.left+"px;");
						$("#marking").attr("style","position:absolute;top:"+reply.design_mark.top+"px;left:"+reply.design_mark.left+"px;");
						$('#cheque').css("background", "rgba(255,255,255,0.5) url("+root_domain+"upload/check/"+reply.design_preview+") no-repeat");
						$('#cheque').css("background-size", "100% 100%");
						
						//input
						$("#amount-line-height").val(reply.design_amount_textLHeight);
						$("#date-letter-space").val(reply.design_dateLspace);
						$("#amount-indent").val(reply.design_amount_textIndent);
						$("#design_id").val(get_id);
						$("#no_preview").hide();
					}
					console.log(reply);
				}					
			});
		});
	$(function() {
		$( ".cheque-item" ).draggable({ containment: "parent" });
		$( ".cheque-item" ).resizable({ containment: "parent" });
	});
	
	var ptop = $("#cheque").position().top;
	var pleft = $("#cheque").position().left;
	
	function updateUI() {
		//setdate
		updateDate();
		updateAmountTextIndent();
		updateAmountLineHeight();
		
	}
	
	function updateDate() {
		var dlspace = $("#date-letter-space").val();
		var attr = $("#date").attr("style");
		var top = $("#date").position().top;
		var left = $("#date").position().left;
		$("#date").attr("style","position: absolute;"+"top:"+top+"px;left:"+left+"px;"+"letter-spacing:"+dlspace+"px;");
		console.log("DATE : ");
		console.log($("#date").position());
	}
	
	function updateAmountTextIndent() {
		var dlspace = $("#amount-indent").val();
		var attr = $("#amount-text").attr("style");
		$("#amount-text").attr("style",attr+"text-indent:"+dlspace+"px;");
		console.log("AMOUNT TEXT : ");
		console.log($("#amount-text").position());
	}
	
	function updateAmountLineHeight() {
		var dlspace = $("#amount-line-height").val();
		var attr = $("#amount-text").attr("style");
		$("#amount-text").attr("style",attr+"line-height:"+dlspace+"px;");
	}
	
	$(document).ready(function(){
		//generate
		$('#FromGenerateDesign').submit(function(e) {
			e.preventDefault();
			var check = $(this).parsley('validate');
			if(check) {
				Loading(true);
				
				var form_data = {
					type: "update",
					did: get_id,
					payee : $("#payee").position(),
					payee_w : $("#payee").width(),
					date : $("#date").position(),
					date_w : $("#date").width(),
					date_lspace : $("#date-letter-space").val(),
					amounttext : $("#amount-text").position(),
					amounttext_indent : $("#amount-indent").val(),
					amounttext_lheight : $("#amount-line-height").val(),
					amounttext_w : $("#amount-text").width(),
					amountnumber : $("#amount-number").position(),
					amountnumber_w : $("#amount-number").width(),
					bearer : $("#bearer").position(),
					bearer_w : $("#bearer").width(),
					cheque_preview : $("#cheque_preview").val(),
					cmark : $("#marking").position(),
					not_more : $("#not-more").position(),
					not_more_w : $("#not-more").width(),
				};
				
				
				
				$.ajax({
					type: "POST",
					cache:false,
					url: root_domain+'app/cheque/design',
					data: form_data,
					success: function(response)
					{
						console.log(form_data);
						Unloading();
						response=response.trim();
						if(response == "1") {
							bootbox.alert("<h4>Design Updated Successfully.</h4>",function(e) {
								window.close();
							});
						}
						else if(response == "-1") {
							$.gritter.add({
								text: 'Design already exists !',
								class_name: 'danger',
								time: 1500,
							});
						}
						else {
							$.gritter.add({
									text: 'Error Occured while adding record.',
									class_name: 'danger',
									time: 1500,
								});
						}
						console.log(response);
					}
				});
			}
		});	
	});
	</script>
</body>
</html>
