<?php
	session_start();
	include("../../config/config.php");
	include("../../config/session.php");
	$query = $dbcon -> query("SELECT `user_mail`, `print_align` FROM `users` WHERE `user_id` = '$_SESSION[user_id]'");
	$record = $query -> fetch_assoc();
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php
		$TITLE = "Settings";
		include("../common/head.php");
	?>
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
			<div class="page-head">
				<h2><?php echo $TITLE; ?></h2>
				<ol class="breadcrumb">
				  <li><a href="<?php echo DOMAIN_CHEQUE.'home'; ?>">Home</a></li>
				  <li class="active">Settings</li>
				</ol>
			</div>		
    
			<div class="cl-mcont">
				<div class="row">
					<div class="pull-right">
						<a href="<?php //echo DOMAIN_CHEQUE; ?>backup" target="_blank">
							<button class="btn btn-success"><i class="fa fa-inbox"></i> BACKUP DATABASE</button>
						</a>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-6 col-md-6">
					      <div class="block-flat">
						      <div class="header">							
							      <h3>Change Password</h3>
						      </div>
						      <div class="content">
							      <form role="form" id="FormPassword" method="post" parsley-validate novalidate> 
								      <div class="form-group">
									      <label>New Password</label>
									      <input type="password" id="new_password" name="new_password" placeholder="New Password" class="form-control" required>
								      </div>
								      <div class="form-group"> 
									      <label>Repeat Password</label>
									      <input type="password" id="repeat_password" name="repeat_password" placeholder="Repeat Password" class="form-control" required>
								      </div> 
								      <div class="form-group">
										<input type="hidden" name="password_token" id="password_token" value="<?php echo $token; ?>" />
										<input type="submit" class="btn btn-primary" value="Change Password" />
										<input type="reset" class="btn btn-default" value="Reset" />
								      </div>
							      </form>
						      </div>
					      </div>				
					</div>
				  
					<div class="col-sm-6 col-md-6">
					      <div class="block-flat">
						      <div class="header">							
							      <h3>Admin Settings</h3>
						      </div>
						      <div class="content">
							      <form role="form" method="post" id="FormSettings" parsley-validate novalidate> 
								      <div class="form-group">
									      <label>Admin Email</label>
									      <input type="email" id="admin_email" name="admin_email" placeholder="Admin Email Address" class="form-control" value="<?php echo decrypt($record['user_mail']); ?>" parsley-type="email" required>
								      </div>
								      <div class="form-group"> 
									      <label>Printer Email</label>
									      <input type="email" id="printer_email" name="printer_email" placeholder="Printer Email Address" class="form-control" value="<?php echo $record['user_printer']; ?>" parsley-type="email">
								      </div>
										<div class="form-group"> 
									      <label>Print Alignment on Printer</label><br/>
										  <label class="radio-inline">
											<input type="radio" name="align" value="1" <?php if($record['print_align']=="1"){echo 'checked="checked"';}?>>Left<?php $record['print_align']?></label>
										   <label class="radio-inline"><input type="radio" name="align" value="0" <?php if($record['print_align']=="0"){echo 'checked="checked"';}?> >Center </label>
											<label class="radio-inline">
											<input type="radio" name="align" value="2" <?php if($record['print_align']=="2"){echo 'checked="checked"';}?>>Right</label>
											
								      </div> 
								      <div class="form-group">
										<input type="hidden" name="settings_token" id="settings_token" value="<?php echo $token; ?>" />
										<input type="submit" class="btn btn-success" value="Update" />
										<input type="reset" class="btn btn-default" value="Reset" />
								      </div>
							      </form>
						      </div>
					      </div>				
					</div>
				</div>
			</div>	
		</div>
	</div>
</div>
  
	<?php include("../common/js.php"); ?>
	<script type="text/javascript">
		$(document).ready(function(){
			$("#FormSettings").submit(function(e) {
				e.preventDefault();
				var check = $(this).parsley('validate');
				if(check) {
					Loading(true);
					var form_data = {
						type: "update",
						token :  $("#settings_token").val(),
						admin : $("#admin_email").val(),
						printer : $("#printer_email").val(),
						print_align : $("input[name='align']:checked").val()
					};					
					$.ajax({
						type: "POST",
						cache:false,
						url: root_domain+'app/admin/settings',
						data: form_data,
						success: function(response)
						{
							Unloading();
							if(response == "1") {
								bootbox.alert("SETTINGS UPDATED SUCCESSFULLY.",function(e) {
									window.location.reload();	
								});
								
							}
							else if(response == "0") {
								$.gritter.add({
									text: 'ERROR, TRY AGAIN !',
									class_name: 'danger',
									time: 1000,
								});
							}
							else {
								$.gritter.add({
									text: 'ERROR, TRY AGAIN !',
									class_name: 'danger',
									time: 1000,
								});
								console.log(response);
							}
						}
					});
				}
			});
			
			$("#FormPassword").submit(function(e) {
				e.preventDefault();
				var check = $(this).parsley('validate');
				if(check) {
					Loading(true);
					var form_data = {
						type: "password",
						token :  $("#password_token").val(),
						newpass :  $("#new_password").val(),
						repeatpass : $("#repeat_password").val(),
					};
					
					console.log(form_data);
					
					$.ajax({
						type: "POST",
						cache:false,
						url: root_domain+'app/admin/settings',
						data: form_data,
						success: function(response)
						{
							Unloading();
							if(response == "1") {
								bootbox.alert("PASSWORD UPDATED SUCCESSFULLY. YOU MUST RELOGIN AFTER PASSWORD CHANGE.",function(e) {
									window.location.href = root_domain+'logout';	
								});
								
							}
							else if(response == "-1") {
								$.gritter.add({
									text: 'PASSWORDS DO NOT MATCH, TRY AGAIN !',
									class_name: 'danger',
									time: 2000,
								});
							}
							else if(response == "0") {
								$.gritter.add({
									text: 'ERROR, TRY AGAIN !',
									class_name: 'danger',
									time: 2000,
								});
							}
							else {
								$.gritter.add({
									text: 'ERROR, TRY AGAIN !',
									class_name: 'danger',
									time: 2000,
								});
								console.log(response);
							}
						}
					});
				}
			});
		});
	</script>

</body>
</html>
