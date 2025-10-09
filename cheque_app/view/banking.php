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
		$TITLE = "Banking Manager";
		include("../common/head.php");
		
	?>
	<!--<link rel="stylesheet" type="text/css" href="<?php echo DOMAIN_CHEQUE; ?>js/jquery.select2/select2.css" />-->
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
				  <li class="active">Banking Manager</li>
				</ol>
			</div>		
			<!--<div class="row">
				<div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
					<div class="pull-left">
						
						<button class="btn btn-primary" data-toggle="modal" data-target="#ModalNewAccount"><i class="fa fa-th-large"></i> New Bank Account</button>
						
					</div>
				</div>
			</div>-->
			<div class="row">
			<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
					<div class="block-flat">
						<div class="header">							
							<h3>New Bank Account</h3>
						</div>
						<div class="content">
							<form id="FormNewAccount" role="form" method="post" parsley-validate novalidate>
				<div class="form-group">
					<label class="control-label">SELECT BANK</label>
					<select id="bank_id" class="select2" required>
						<option selected disabled value="">SELECT BANK</option>
						<?php
							$query = $dbcon->query("SELECT `bank_id`,`bank_name` FROM `coro_banks` WHERE `bank_status` = 1 AND `bank_of` in(0,$_SESSION[user_id])");
							while($r = $query -> fetch_assoc()) {
								echo '<option value="'.$r['bank_id'].'">'.($r['bank_name']).'</option>';
							}
						?>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">BRANCH</label>
					<input type="text" name="branch"  id="branch" class="form-control parsley-validated" parsley-trigger="change" required>
				</div>
				<div class="form-group">
					<label class="control-label">CITY</label>
					<input type="text" name="city"  id="city" class="form-control parsley-validated" parsley-trigger="change" required>
				</div>
				<div class="form-group">
					<label class="control-label">ACCOUNT NAME</label>
					<input type="text" id="account_name" class="form-control parsley-validated" parsley-trigger="change" required>
				</div>
				<div class="form-group">
					<label class="control-label">ACCOUNT NUMBER</label> 
					<input type="text" id="account_number" class="form-control parsley-validated" parsley-trigger="change" parsley-type="digits" required>
				</div>
				<div class="form-group">
					<label class="control-label">CHEQUE SERIES STARTING NUMBER</label>
					<input type="text" id="account_chequeno" class="form-control parsley-validated" parsley-trigger="change" parsley-type="digits" required>
				</div>
				<div class="form-group">
					<label class="control-label">NUMBER OF CHEQUES</label>
					<input type="text" id="account_cheque_num" class="form-control parsley-validated" parsley-trigger="change" parsley-type="digits" required>
				</div>
			</div>
			<div class="modal-footer">
				<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-primary btn-flat" type="submit">Add Account</button>
			</div>
			</form>
						</div>
					</div>
				
				<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
					<div class="block-flat">
						<div class="header">							
							<h3><i class="fa fa-check"></i> Bank Accounts</h3>
						</div>
						<div class="content">
							<div class="table-responsive">
								<table class="no-border red" id="datatable">
									<thead class="no-border">
										<tr>
											<th>SR</th>
											<th>ACC NAME</th>
											<th>ACC NUMBER</th>
											<th>BANK</th>
											<th>BRANCH</th>
											<th>CITY</th>
											<th>CHEQUES LEFT</th>
											<th>&nbsp;</th>
										</tr>
									</thead>
									<tbody class="no-border-x">
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>			
		</div>
		
		
	</div> 
</div>
<!-- Add Bank Account -->
<!-- Modal -->
<div class="modal colored-header" id="ModalNewAccount" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
				<h3>Edit Bank Account</h3>
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body form">
			<form id="FormNewAccount" role="form" method="post" parsley-validate novalidate>
				<div class="form-group">
					<label class="control-label">SELECT BANK</label>
					<select id="bank_id" class="select2" required>
						<option selected disabled value="">SELECT BANK</option>
						<?php
							$query = $dbcon->query("SELECT `bank_id`,`bank_name` FROM `coro_banks` WHERE `bank_status` = 1 AND `bank_of` in(0, $_SESSION[user_id])");
							while($r = $query -> fetch_assoc()) {
								echo '<option value="'.$r['bank_id'].'">'.$r['bank_name'].'</option>';
							}
						?>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">ACCOUNT NAME</label>
					<input type="text" id="account_name" class="form-control parsley-validated" parsley-trigger="change" required>
				</div>
				<div class="form-group">
					<label class="control-label">ACCOUNT NUMBER</label> 
					<input type="text" id="account_number" class="form-control parsley-validated" parsley-trigger="change" parsley-type="digits" required>
				</div>
				<div class="form-group">
					<label class="control-label">CHEQUE SERIES STARTING NUMBER</label>
					<input type="text" id="account_chequeno" class="form-control parsley-validated" parsley-trigger="change" parsley-type="digits" required>
				</div>
				<div class="form-group">
					<label class="control-label">NUMBER OF CHEQUES</label>
					<input type="text" id="account_cheque_num" class="form-control parsley-validated" parsley-trigger="change" parsley-type="digits" required>
				</div>
			</div>
			<div class="modal-footer">
				<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-primary btn-flat" type="submit">Add Account</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- Add Bank Account -->
<!-- Modal -->
<div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
				<h3>Edit Bank Account</h3>
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body form">
			<form id="FormEditAccount" role="form" method="post" parsley-validate novalidate>
				<div class="form-group">
					<label class="control-label">SELECT BANK</label>
					<select id="edit_bank_id" class="select2" required>
						<option selected disabled value="">SELECT BANK</option>
						<?php
							$query = $dbcon->query("SELECT `bank_id`,`bank_name` FROM `coro_banks` WHERE `bank_status` = 1 AND `bank_of` in (0,$_SESSION[user_id])");
							while($r = $query -> fetch_assoc()) {
								echo '<option value="'.$r['bank_id'].'">'.($r['bank_name']).'</option>';
							}
						?>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">BRANCH</label>
					<input type="text" name="branch"  id="edit_bank_branch" class="form-control parsley-validated" parsley-trigger="change" required>
				</div>
				<div class="form-group">
					<label class="control-label">CITY</label>
					<input type="text" name="city"  id="edit_bank_city" class="form-control parsley-validated" parsley-trigger="change" required>
				</div>
				<div class="form-group">
					<label class="control-label">ACCOUNT NAME</label>
					<input type="text" id="edit_account_name" class="form-control parsley-validated" parsley-trigger="change" required>
				</div>
				<div class="form-group">
					<label class="control-label">ACCOUNT NUMBER</label>
					<input type="text" id="edit_account_number" class="form-control parsley-validated" parsley-trigger="change" parsley-type="digits" required>
				</div>
				<div class="form-group">
					<label class="control-label">CHEQUE SERIES NUMBER</label>
					<input type="text" id="edit_account_chequeno" class="form-control parsley-validated" parsley-trigger="change" parsley-type="digits" required>
				</div>
				<div class="form-group">
					<label class="control-label">NUMBER OF CHEQUES</label>
					<input type="text" id="edit_account_cheque_num" class="form-control parsley-validated" parsley-trigger="change" parsley-type="digits" required>
				</div>
			</div>
			<div class="modal-footer">
				<input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
				<input type="hidden" name="token" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update Account</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

	<?php include("../common/js.php"); ?>

	
	<script type="text/javascript">
		var  accountTable;
		$(document).ready(function(){
			//initialize the javascript
			//Basic Instance
			accountTable = $("#datatable").dataTable({
				"bAutoWidth" : false,
				"bFilter" : true,
				"bSort" : false,
				"bProcessing": true,
				"bServerSide" : true,
				"oLanguage": {
						"sLengthMenu": "_MENU_",
						"sProcessing": "<img src='"+root_domain+"images/loading.gif'/> Loading ...",
						"sEmptyTable": "NO ACCOUNTS ADDED YET !",
				},
				"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
				"iDisplayLength": 10,
				"sAjaxSource": root_domain+'app/account/',
				"fnServerParams": function ( aoData ) {
					aoData.push( { "name": "type", "value": "fetch" } );
				},
				"fnDrawCallback": function( oSettings ) {
					$('.ttip, [data-toggle="tooltip"]').tooltip();
				}
			}).fnSetFilteringDelay();

			//Search input style
			$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
			$('.dataTables_length select').addClass('form-control');
			

			$("#FormNewAccount").submit(function(e) {
				e.preventDefault();
				var check = $(this).parsley('validate');
				if(check) {
					Loading(true);
					var form_data = {
						type: "add",
						token :  $("#token").val(),
						bank : $("#bank_id").val(),
						city : $("#city").val(),
						branch : $("#branch").val(),
						name : $("#account_name").val(),
						number : $("#account_number").val(),
						cheque : $("#account_chequeno").val(),
						cheque_num : $("#account_cheque_num").val()
					};
					
					console.log(form_data);

					$.ajax({
						type: "POST",
						cache:false,
						url: root_domain+'app/account/',
						data: form_data,
						success: function(response)
						{
							if(response == "1") {
								$.gritter.add({
									text: 'ACCOUNT ADDED SUCCESSFULLY.',
									class_name: 'success',
									time: 1000,
								});
								accountTable.fnReloadAjax();
								$("#FormNewAccount").reset();
								$("#ModalNewAccount").modal("hide");
								Unloading();
							}
							else if(response == "-1") {
								$.gritter.add({
									text: 'ALREADY EXISTS !',
									class_name: 'info',
									time: 1000,
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
								console.log(response);
							}
							Unloading();
						}
					});
				}
			});


			$("#FormEditAccount").submit(function(e) {
				e.preventDefault();
				var check = $(this).parsley('validate');
				if(check) {
					Loading(true);
					var form_data = {
						type: "edit",
						token :  $("#edit_token").val(),
						id : $("#edit_id").val(),
						bank : $("#edit_bank_id").val(),
						name : $("#edit_account_name").val(),
						branch: $("#edit_bank_branch").val(),
						city: $("#edit_bank_city").val(),
						number : $("#edit_account_number").val(),
						cheque : $("#edit_account_chequeno").val(),
						cheque_num : $("#edit_account_cheque_num").val()
					};

					console.log(form_data);
					
					$.ajax({
						type: "POST",
						cache:false,
						url: root_domain+'app/account/',
						data: form_data,
						success: function(response)
						{
							if(response == "1") {
								$.gritter.add({
									text: 'UPDATED SUCCESSFULLY !',
									class_name: 'success',
									time: 1000,
								});
								accountTable.fnReloadAjax();
								$("#FormEditAccount").reset();
								$("#ModalEditAccount").modal("hide");
								Unloading();
							}
							else if(response == "-1") {
								$.gritter.add({
									text: 'ALREADY EXISTS !',
									class_name: 'danger',
									time: 1000,
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
									text: 'SERVER ERROR, CONTACT ADMIN !',
									class_name: 'danger',
									time: 1000,
								});
								console.log(response);
							}
							Unloading();
						}
					});
				}
			});
		});
		
		var editReq = null;
		function edit_account(id) {
			Loading(true);
			if(editReq != null)
				editReq.abort();

			editReq = $.ajax({
				type: "POST",
				url: root_domain+'app/account/',
				data: { type : "preedit", id : id },
				success: function(response)
				{
					console.log(response);
					var obj = jQuery.parseJSON(response);
					$("#ModalEditAccount").modal("show");
					$("#edit_id").val(id);
					$("#edit_bank_id").select2("val",obj.acc_bank);
					$("#edit_bank_city").val(obj.bank_city);
					$("#edit_bank_branch").val(obj.bank_branch);
					$("#edit_account_name").val(obj.acc_name);
					$("#edit_account_number").val(obj.acc_number);
					$("#edit_account_chequeno").val(obj.acc_chequeno);
					$("#edit_account_cheque_num").val(obj.acc_chequeleft);
					Unloading();
				}
			});	
		}

		function delete_account(id) {
			bootbox.confirm("<h4 class='text-warning'><i class='fa fa-warning'></i> Are you sure ?</h4>",function(e) {
				if(e) {
					Loading(true);
					$.ajax({
						type: "POST",
						url: root_domain+'app/account/',
						data: { type : "delete", token :  $("#token").val(), id : id },
						success: function(response)
						{
							Unloading();
							if(response == "1") {
								$.gritter.add({
									text: 'REMOVED SUCCESSFULLY !',
									class_name: 'success',
									time: 1000,
								});
								accountTable.fnReloadAjax();
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
									text: 'OOPS, SERVER ERROR !',
									class_name: 'danger',
									time: 1000,
								});
								
								console.log(response);
							}
						}
					});	
				}
			});
		}
	</script>
</body>
</html>