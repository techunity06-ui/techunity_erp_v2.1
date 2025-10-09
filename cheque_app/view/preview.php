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
	body {
				display: block;
				padding-top: 0px;
				width: 8.05in;
				height: 3.85in;
				margin:0px 0px 0px 0px;
				/*border: 2px dashed;*/
			}
			
			.hidden {
				display:none;
			}
			#cheque {
				display: inline-block;
				margin:0px;
				padding:0px;
				font-family: 'Roboto', sans-serif;
				width: 8.05in;
				height: 3.85in;
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
				font-size: 12px;
				line-height: 12px;
			}
			
			#amount-number {
				font-size:16px;
				line-height: 16px;
			}
			
			.vertical {
				top:208px;
				/* FF Chrome Opera etc */
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
			}
			
			#img_ac_e {
				position: absolute;
				top : 0px;
				left : 0px;
			}
			
			#not_more_than {
				position: absolute;
				font-size:12px;
			}
			
			.ui-widget-content {
				border: none;
			}
	</style>
</head>
<body>
	<div id="output" class="">
		<div id="cheque">
			<div id="payee" class="ui-widget-content cheque-item"><?php echo COMPANY; ?></div>
			<div id="date" class="ui-widget-content cheque-item"><?php echo str_replace("-","",date("d-m-Y")); ?></div>
			<div id="amount-number" class="ui-widget-content cheque-item">99,99,999.00/-</div>
			<div id="amount-text" class="ui-widget-content cheque-item">NINETY-NINE LAKH NINETY-NINE THOUSAND NINE HUNDRED NINETY NINE RUPEE ONLY</div>
			<div id="bearer" class="ui-widget-content cheque-item">*************</div>
			<img src="<?php echo DOMAIN_CHEQUE; ?>cheque_marks/ac_nn_f.png" id="img_nn" class="mark" />
			<div id="not_more_than" class="ui-widget-content cheque-item"><b>NOT OVER THAN 99,99,999.00/-</b></div>
		</div>
	</div>
	<script type="text/javascript" src="<?php echo DOMAIN_CHEQUE; ?>js/jquery.js"></script>

	
	<script type="text/javascript">
	var root_domain = '<?php echo DOMAIN_CHEQUE; ?>';
	var reply;
	var get_id = '<?php echo $_GET['id']; ?>';
		$(document).ready(function() {
			
			var form_data = {
					type: "preview",
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
						$("#not_more_than").removeClass("hidden").attr("style","width:"+reply.design_notmoreWidth+"px;top:"+reply.design_notmore.top+"px;left:"+reply.design_notmore.left+"px;");
						$(".mark").attr("style","position:absolute;top:"+reply.design_mark.top+"px;left:"+reply.design_mark.left+"px;");
						$('#cheque').css("background", "rgba(255,255,255,0.5) url("+root_domain+"upload/check/"+reply.design_preview+") no-repeat");
						$('#cheque').css("background-size", "100% 100%");
						$("#no_preview").hide();
					}
					console.log(reply);
				}					
			});
		});
	</script>
</body>
</html>
