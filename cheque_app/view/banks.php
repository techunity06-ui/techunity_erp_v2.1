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
		$TITLE = "Banks";
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
			
			<?php
				include("../common/user_steps.php");
			?>
			<div class="page-head">
				<h2><?php echo $TITLE; ?></h2>
				<ol class="breadcrumb">
				  <li><a href="<?php echo DOMAIN_CHEQUE.'home'; ?>">Home</a></li>
				  <li><a href="<?php echo DOMAIN_CHEQUE.'banking'; ?>">Banking</a></li>
				  <li class="active">Banks</li>
				</ol>
			</div>		
			<div class="row">
				<div class="hidden-xs hidden-sm col-md-12 col-lg-12">
					<div class="pull-left">
						<button onClick="window.open(root_domain+'export/banks?type=csv','_blank');" type="button" class="btn btn-default"><i class="fa fa-bars"></i> Export CSV</button>
						<button onClick="window.open(root_domain+'export/banks?type=pdf', '_blank');" type="button" class="btn btn-default"><i class="fa fa-cloud-upload"></i> Export PDF</button>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
					<div class="block-flat">
						<div class="header">							
							<h3>New Bank</h3>
						</div>
						<div class="content">
							<form id="FormNewBank" autocomplete="off"  role="form" method="post" parsley-validate novalidate>
								<div class="form-group">
									<label class="control-label">NAME</label>
									<input type="text" name="name"  id="name" class="form-control parsley-validated" parsley-trigger="change" required>
								</div>
								<!--<div class="form-group">
									<label class="control-label">BRANCH</label>
									<input type="text" name="branch"  id="branch" class="form-control parsley-validated" parsley-trigger="change" required>
								</div>
								<div class="form-group">
									<label class="control-label">CITY</label>
									<input type="text" name="city"  id="city" class="form-control parsley-validated" parsley-trigger="change" required>
								</div>-->
								<div class="form-group">
									<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
									<button class="btn btn-default" type="reset">CLEAR</button>
									<button class="btn btn-primary" type="submit">ADD BANK</button>
								</div>
							</form>
						</div>
					</div>
				</div>
				<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
					<div class="block-flat">
						<div class="content">
							<div class="table-responsive">
								<table id="datatable" class="no-border red">
									<thead class="no-border">
										<tr>
											<th>SR</th>
											<th>NAME</th>											
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
<!-- Modal -->
<div class="modal colored-header" id="ModalEditBank" data-backdrop="static" data-keyboard="false" role="dialog">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
				<h3>EDIT BANK</h3>
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body form">
			<form id="EditBank" role="form" method="post" parsley-validate novalidate>
				<div class="form-group">
					<label class="control-label">NAME</label>
					<input type="text" id="edit_name" class="form-control parsley-validated" parsley-trigger="change" required>
				</div>
				<!--<div class="form-group">
					<label class="control-label">BRANCH</label>
					<input type="text" id="edit_branch" class="form-control parsley-validated" parsley-trigger="change" required>
				</div>
				<div class="form-group">
					<label class="control-label">CITY</label>
					<input type="text" id="edit_city" class="form-control parsley-validated" parsley-trigger="change" required>
				</div>-->
			</div>
			<div class="modal-footer">
				<input type="hidden" name="edit_token" id="edit_token" value="<?php echo $token; ?>" />
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button type="submit" class="btn btn-primary btn-flat">Update</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

	<?php include("../common/js.php"); ?>

	<script type="text/javascript">
	var bankTable;
		$(document).ready(function(){
			//initialize the javascript
			//Basic Instance
			bankTable = $("#datatable").dataTable({
				"bAutoWidth" : false,
				"bFilter" : true,
				"bSort" : false,
				"bProcessing": true,
				"bServerSide" : true,
				"oLanguage": {
						"sLengthMenu": "_MENU_",
						"sProcessing": "<img src='"+root_domain+"images/loading.gif'/> Loading ...",
						"sEmptyTable": "NO BANKS ADDED YET !",
				},
				"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
				"iDisplayLength": 10,
				"sAjaxSource": root_domain+'app/bank/',
				"fnServerParams": function ( aoData ) {
					aoData.push( { "name": "type", "value": "fetch" } );
				},
				"fnDrawCallback": function( oSettings ) {
					$('.ttip, [data-toggle="tooltip"]').tooltip();
				}
			}).fnSetFilteringDelay();
			
			
			$("#pannumber").mask("aaaaa-9999-a");
			$("#edit_pannumber").mask("aaaaa-9999-a");
			
			//Search input style
			$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
			$('.dataTables_length select').addClass('form-control');
			
			$(".delete-row").click(function(e){
				e.preventDefault();
				bootbox.confirm("Are you sure ?", function(r) {
					if(r) {
					
					}
					else {
					
					}
				});
			});
			$("#FormNewBank").submit(function(e) {
				e.preventDefault();
				var check = $(this).parsley('validate');
				if(check) {
					Loading(true);
					var form_data = {
						type: "add",
						token :  $("#token").val(),
						name : $("#name").val(),
						/*city : $("#city").val(),
						branch : $("#branch").val(),*/
					};
					
					console.log(form_data);
					
					$.ajax({
						type: "POST",
						cache:false,
						url: root_domain+'app/bank/',
						data: form_data,
						success: function(response)
						{
							console.log(response);
							if(response == "1") {
								$.gritter.add({
									text: 'BANK ADDED SUCCESSFULLY.',
									class_name: 'success',
									time: 1000,
								});
								bankTable.fnReloadAjax();
								$("#FormNewBank").reset();
								/*$("#PanPreview").attr("src","");
								$("#PANPrevew_Large").attr("href","");*/
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
			
			$("#EditBank").submit(function(e) {
				e.preventDefault();
				var check = $(this).parsley('validate');
				if(check) {
					Loading(true);
					var form_data = {
						type: "edit",
						token :  $("#edit_token").val(),
						id : $("#edit_id").val(),
						name : $("#edit_name").val(),
						/*city : $("#edit_city").val(),
						branch : $("#edit_branch").val()*/
					};
					
					console.log(form_data);
					
					$.ajax({
						type: "POST",
						cache:false,
						url: root_domain+'app/bank/',
						data: form_data,
						success: function(response)
						{
							if(response == "1") {
								$.gritter.add({
									text: 'UPDATED SUCCESSFULLY !',
									class_name: 'success',
									time: 1000,
								});
								bankTable.fnReloadAjax();
								$("#EditBank").reset();
								$("#ModalEditBank").modal("hide");
								Unloading();
							}
							else if(response == "-1") {
								$.gritter.add({
									text: 'Bank already exists in the system.',
									class_name: 'info',
									time: 1000,
								});
							}
							else if(response == "0") {
								$.gritter.add({
									text: 'Error Occured while adding record.',
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
		});
		
		var editReq = null;
		function edit_bank(id) {
			Loading(true);
			if(editReq != null)
				editReq.abort();
			
			editReq = $.ajax({
				type: "POST",
				url: root_domain+'app/bank/',
				data: { type : "preedit", id : id },
				success: function(response)
				{
					console.log(response);
					var obj = jQuery.parseJSON(response);
					$("#ModalEditBank").modal("show");
					$("#edit_id").val(id);
					$("#edit_name").val(obj.bank_name);
					/*$("#edit_city").val(obj.bank_city);
					$("#edit_branch").val(obj.bank_branch);*/
					Unloading();
				}
			});	
		}
		
		function delete_bank(id) {
			bootbox.confirm("<h4 class='text-warning'><i class='fa fa-warning'></i> Are you sure ?</h4>",function(e) {
				if(e) {
					Loading(true);
					$.ajax({
						type: "POST",
						url: root_domain+'app/bank/',
						data: { type : "delete", token :  $("#token").val(), id : id },
						success: function(response)
						{
							if(response == "1") {
								$.gritter.add({
									text: 'REMOVED SUCCESSFULLY !',
									class_name: 'success',
									time: 1000,
								});
								bankTable.fnReloadAjax();
								Unloading();
							}
							else if(response == "0") {
								$.gritter.add({
									text: 'ERROR, TRY AGAIN !',
									class_name: 'danger',
									time: 1000,
								});
								Unloading();
							}
							else {
								$.gritter.add({
									text: 'OOPS, SERVER ERROR !',
									class_name: 'danger',
									time: 1000,
								});
								Unloading();
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