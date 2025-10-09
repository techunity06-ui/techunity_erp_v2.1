<?php
	session_start();
	include("../../config/config.php");
	include("../../config/session.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php
		$TITLE = "New Cheque Design";
		include("../common/head.php");
	?>
	<link href='css/jquery-ui.min.css' rel='stylesheet' type='text/css'>
	<link href='js/jquery.crop/css/jquery.Jcrop.css' rel='stylesheet' type='text/css'>
	
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
			width:730px;
			height:360px;
			max-width:730px;
			max-height:360px;
		}
		.cheque-item {
			display: inline-block;
			position:absolute;
			margin:0px;
			padding:0px;
			background:rgba(0,0,0,0.2);
			color:#fff;
			cursor:pointer;
			font-size:12px;
			line-height: 12px;
		}	
		
		#amount-number {
			font-size:16px;
			line-height: 16px;
		}
		
		#date {
			font-size:12px;
			line-height: 12px;
		}
		
		#payee {
			font-size:12px;
			line-height: 12px;
		}
		
		#amount-text {
			font-size:12px;
			line-height: 12px;
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
<div id="cl-wrapper" class="sb-collapsed">
	<?php
		include("../common/menu.php");
	?>
	<div class="container-fluid" id="pcont">
		<?php
			include("../common/header.php");
		?>
    
		<div class="cl-mcont">	
			<?php
				include("../common/user_steps.php");
			?>
			<div class="page-head">
				<h2><?php echo $TITLE; ?></h2>
				<ol class="breadcrumb">
				  <li><a href="<?php echo DOMAIN_CHEQUE.'home'; ?>">Home</a></li>
				  <li><a href="<?php echo DOMAIN_CHEQUE.'cheque'; ?>">Cheques</a></li>
				  <li><a href="<?php echo DOMAIN_CHEQUE.'designs'; ?>">Cheque Designs</a></li>
				  <li class="active">New Design</li>
				</ol>
			</div>
			<div class="row">
				<div class="hidden-xs hidden-sm col-md-9 col-lg-9">
					<div class="pull-left">
						<button type="button" class="btn btn-default" data-toggle="modal" data-target="#FromNewDesign"><i class="fa fa-camera-retro"></i> Upload Cheque Photo</button>
						<!--<button type="button" class="btn btn-info"><i class="fa fa-cloud-upload"></i> Browse Directory</button>-->
					</div>
				</div>
			</div>
			<div class="row" id="cheque_design">
				<div id="cheque" class="hidden-xs hidden-sm col-md-9 col-lg-9">
					<div id="payee" class="ui-widget-content cheque-item" style="top:74px;left: 73px;;"><?php echo COMPANY; ?></div>
					<div id="date" class="ui-widget-content cheque-item" style="top: 21px;left: 550px;
letter-spacing: 3px;">01042014</div>
					<div id="bearer" class="ui-widget-content cheque-item" style="left: 634px;top: 101px;">************</div>
					<div id="amount-number" class="ui-widget-content cheque-item" style="left: 554px;
top: 164px;">99,99,99,999.99/-</div>
					<div id="amount-text" class="ui-widget-content cheque-item" style="width: 556px;text-indent: 40px;line-height: 35px;position: absolute;top: 111px;left: 25px;height: 61px;">NINETY NINE CRORE NINETY NINE LAKH NINETY NINE THOUSAND NINE HUNDRED AND NINETY NINE RUPEE AND NINETY NINE PAISA ONLY</div>
					<div id="marking" class="ui-widget-content cheque-item" style="left: 251px;top: 197px;"><img src="<?php echo DOMAIN_CHEQUE;?>cheque_marks/ac_nn_f.png" /></div>
					<div id="not-more" class="ui-widget-content cheque-item" style="left: 508px;top: 198px;">NOT OVER THAN 99,99,99,999.99/-</div>
				</div>
				<div class="hidden-xs hidden-sm col-md-3 col-lg-3">
					<div class="block-flat">
						<div class="content">
							<form method="post" id="FromGenerateDesign" role="form" parsley-validate novalidate> 
								<div class="form-group">
									<label>DATE LETTER SPACE</label>
									<div class="input-group input-group-sm">
										<input type="number" id="date-letter-space" onKeyUp="updateUI()" placeholder="space between date" class="form-control" parsley-trigger="change" value="11" required onChange="updateUI();">
										<span class="input-group-addon">px</span>
									</div>
								</div>
								<div class="form-group">
									<label>AMOUNT TEXT INDENT</label>
									<div class="input-group input-group-sm">
										<input type="number" id="amount-indent" onKeyUp="updateUI()" placeholder="space before amount in words" class="form-control" parsley-trigger="change" value="40" required onChange="updateUI();">
										<span class="input-group-addon">px</span>
									</div>
								</div>
								<div class="form-group">
									<label>AMOUNT LINE HEIGHT</label>
									<div class="input-group input-group-sm">
										<input type="number" id="amount-line-height" onKeyUp="updateUI()" placeholder="space between amount lines" class="form-control" parsley-trigger="change" value="35" required onChange="updateUI();">
										<span class="input-group-addon">px</span>
									</div>
								</div>
								<div class="form-group">
									<label>SELECT BANK</label>
									<select id="bank" class="select2" parsley-trigger="change" required>
										<option selected disabled value="">SELECT BANK</option>
										<?php
											$query = $dbcon->query("SELECT `bankid`,`bank_name` FROM `bank_mst` WHERE `bank_status` = 0 ");
											while($r = $query -> fetch_assoc()) {
												echo '<option value="'.$r['bankid'].'">'.$r['bank_name'].'</option>';
											}
										?>
									</select>
								</div>
								<div class="form-group">
									<input type="hidden" id="cheque_preview" name="cheque_preview" value="" />
									<input type="hidden" id="token" name="token" value="<?php echo $token; ?>" />
									<button type="submit" id="generate_new_design" name="generate_new_design" class="btn btn-defailt btn-flat">Generate</button>
								</div>
							</form>
						</div>
				</div>
			</div>
		</div>
	</div>	
</div>
	
	<?php include("../common/js.php"); ?>
	
	<script type="text/javascript">
		var jcrop_api;
		
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
	</script>
<!-- Modal PANUPLOAD -->
<div class="modal" id="FromNewDesign" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog w_60">
		<div class="modal-content">
			<div class="modal-header">
				<h3>UPLOAD CHEQUE SCAN</h3>
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body form">
			<form id="FormUploadCheque" role="form" method="post" action="<?php echo DOMAIN_CHEQUE.'process_image_cheque'; ?>" enctype="multipart/form-data" >
				<div class="form-group">			
					<label class="control-label">SELECT SCAN</label>
					<input name="ImageFile" id="imageInput" type="file" />
				</div>
				<div class="form-group">
					<button type="submit" id="upload_cheque" class="btn btn-info btn-flat" >Upload</button>
				</div>
			</form>
			<form id="FormSaveCheque" role="form" method="post" action="<?php echo DOMAIN_CHEQUE.'process_image_cheque_save'; ?>" enctype="multipart/form-data">
				<div class="form-group">
					<input type="hidden" id="x" name="x" />
					<input type="hidden" id="y" name="y" />
					<input type="hidden" id="w" name="w" />
					<input type="hidden" id="h" name="h" />
					<input type="hidden" id="imgsrc" name="imgsrc" />
				</div>
				<img src="" id="preview" class="hidden" />
				<div class="modal-footer">
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-danger btn-flat" onClick="window.location.reload()">Clear</button>
				<button type="submit" id="save_picture" class="btn btn-info btn-flat" >Save Photo</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>
</div><!-- /.modal -->
	<script type="text/javascript">
		var i = 1;
		
		$(document).ready(function() {
			
			var options = { 
				//target:   '#output',   // target element(s) to be updated with server response 
				beforeSubmit:  beforeSubmit,  // pre-submit callback 
				success:       afterSuccess,  // post-submit callback 
				resetForm: true        // reset the form after successful submit 
			}; 
			
			
			$('#FormUploadCheque').submit(function(e) {
				e.preventDefault();
				if(jcrop_api) {
					jcrop_api.destroy();
				}
				clear_preview();
				Loading(true);
				$(this).ajaxSubmit(options);  			
				// always return false to prevent standard browser submit and page navigation 
			});
			
			var options2 = { 
				//target:   '#output',   // target element(s) to be updated with server response 
				beforeSubmit:  checkCoords,  // pre-submit callback 
				success:       afterSuccess2,  // post-submit callback 
				resetForm: true        // reset the form after successful submit 
			}; 
			
			$('#FormSaveCheque').submit(function(e) {
				e.preventDefault();
				if(jcrop_api) {
					jcrop_api.destroy();
				}
				clear_preview();
				$("#preview").attr('src', 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
				Loading(true);
				$(this).ajaxSubmit(options2);  			
				// always return false to prevent standard browser submit and page navigation 
			});
			
			//generate
			$('#FromGenerateDesign').submit(function(e) {
				e.preventDefault();
				var check = $(this).parsley('validate');
				if(check) {
					Loading(true);
					
					var form_data = {
						type: "generate",
						token :  $("#token").val(),
						bank : $("#bank").val(),
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
					
					console.log(form_data);
					
					$.ajax({
						type: "POST",
						cache:false,
						url: root_domain+'app/cheque/design',
						data: form_data,
						success: function(response)
						{
							
							Unloading();
							response=response.trim();
							if(response == "1") {
								bootbox.alert("<h4>Design Added Successfully.</h4>",function(e) {
									window.location.reload();
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
		
		$(function(){
		  $("#btn").click(function(){ 
			$("img").attr('src','image.php');  
		  });
		});
		
		function afterSuccess(responseText) {
			Unloading();
			console.log("success" + responseText);
			var obj = jQuery.parseJSON( responseText );
			if(obj.response == "1") {
				console.log("rgba(255,255,255,0.5) url(upload/pan/"+obj.image+") no-repeat;");
				//$('#cheque').css("background", "rgba(255,255,255,0.5) url(upload/pan/"+obj.image+") no-repeat");
				$("#preview").removeClass("hidden");
				$("#preview").prop("src",root_domain+"upload/check/temp/"+obj.image);
				$("#imgsrc").val(obj.image);
				$('#preview').Jcrop({
					onSelect: updateCoords
				});
				jcrop_api = $('#preview').data('Jcrop');
				$("#FormUploadCheque").hide();
				
			}
			else {
				$("#output").html("<font color='red'>Error while uploading file. Please try again.</font>");
			}
			
		}
		
		function clear_preview() {
			$("#preview").attr('src', 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
			$("#preview").addClass("hidden");
		}
		
		function afterSuccess2(responseText) {
			Unloading();
			console.log("success" + responseText);
			var obj = jQuery.parseJSON( responseText );
			if(obj.response == "1") {
				console.log("rgba(255,255,255,0.5) url(upload/check/"+obj.image+") no-repeat;");
				$('#cheque').css("background", "rgba(255,255,255,0.5) url(upload/check/"+obj.image+") no-repeat");
				$('#cheque').css("background-size", "100% 100%");
				$("#FromNewDesign").modal("hide");
				$("#cheque_design").show();
				$("#cheque_preview").val(obj.image);
				updateUI();
			}
			else {
				$("#output").html("<font color='red'>Error while uploading file. Please try again.</font>");
			}
			
		}
		
		//function to check file size before uploading.
		function beforeSubmit(){
			//check whether browser fully supports all File API
		   if (window.File && window.FileReader && window.FileList && window.Blob)
			{
				
				if( !$('#imageInput').val()) //check empty input filed
				{
					Unloading();
					$("#output").html("Are you kidding me?");
					return false
				}
				
				var fsize = $('#imageInput')[0].files[0].size; //get file size
				var ftype = $('#imageInput')[0].files[0].type; // get file type
				

				//allow only valid image file types 
				switch(ftype)
				{
					case 'image/png': case 'image/gif': case 'image/jpeg': case 'image/pjpeg':
						break;
					default:
						Unloading();
						$("#output").html("<b>"+ftype+"</b> Unsupported file type!");
						return false
				}
				
				//Allowed file size is less than 1 MB (1048576)
				if(fsize>1048576) 
				{
					Unloading();
					$("#output").html("<b>"+bytesToSize(fsize) +"</b> Too big Image file! <br />Please reduce the size of your photo using an image editor.");
					return false
				}
				
				$("#output").html("");  
			}
			else
			{
				//Output error to older unsupported browsers that doesn't support HTML5 File API
				Unloading();
				$("#output").html("Please upgrade your browser, because your current browser lacks some new features we need!");
				return false;
			}
		}

		//function to format bites bit.ly/19yoIPO
		function bytesToSize(bytes) {
		   var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
		   if (bytes == 0) return '0 Bytes';
		   var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
		   return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
		}
	</script>
	<script language="Javascript">
		
		function updateCoords(c)
		{
			console.log("updating");
			$('#x').val(c.x);
			$('#y').val(c.y);
			$('#w').val(c.w);
			$('#h').val(c.h);
		};

		function checkCoords()
		{
			if (parseInt($('#w').val())) {
				return true;
			}
			else {
				Unloading();
				bootbox.alert('<h4>Upload and Crop Image and before saving !</h4>');
				return false;
			}
		};

	</script>
</body>
</html>