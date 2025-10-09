<?php
	session_start();
	include("../../config/config.php");
	include("../../include/common_functions/common_functions.php");
	include("../../config/session.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php
		$TITLE = "Payees";
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
				  <li class="active">Payees</li>
				</ol>
			</div>		
			<div class="row">
				<div class="hidden-xs hidden-sm col-md-12 col-lg-12">
					<div class="pull-left">
						<button onClick="window.open(root_domain+'export/customers?type=csv','_blank');" type="button" class="btn btn-default"><i class="fa fa-bars"></i> Export CSV</button>
						<button onClick="window.open(root_domain+'export/customers?type=pdf', '_blank');" type="button" class="btn btn-default"><i class="fa fa-cloud-upload"></i> Export PDF</button>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
					<div class="block-flat">
						<div class="header">							
							<h3>New Payee</h3>
						</div>
						<div class="content">
							<form id="FormNewCustomer" autocomplete="off"  role="form" method="post" parsley-validate novalidate>
								<div class="form-group">
									<label class="control-label">NAME</label>
									<input type="text" name="name"  id="name" class="form-control parsley-validated" parsley-trigger="change" required>
								</div>
								<div class="form-group">
									<label class="control-label">ADDRESS</label>
									<textarea name="address"  id="address" class="form-control parsley-validated" parsley-trigger="change"></textarea>
								</div>
								<div class="form-group">
									<label class="control-label">STATE</label>
									<select id="stateid" class="select2" parsley-trigger="change" required onchange="load_city(this.value,'cityid','')" title="Choose State">
										<option selected disabled value="">SELECT STATE</option>
										<?=getstate($dbcon,0)?>
									</select>
									
								</div>
								<div class="form-group">
									<label class="control-label">CITY</label>
									<select id="cityid" class="select2" parsley-trigger="change" required title="Choose City">
										<option selected disabled value="">SELECT CITY</option>
										
									</select>
								</div>
								<div class="form-group">
									<label class="control-label">PHONE</label>
									<input type="text" name="phone"  id="phone" class="form-control parsley-validated" parsley-trigger="change" parsley-type="digits" parsley-trigger="change" required>
								</div>
								<div class="form-group">
									<label class="control-label">EMAIL</label>
									<input type="text" name="mail"  id="mail" class="form-control parsley-validated" parsley-trigger="change" parsley-type="email">
								</div>
								<!--<div class="form-group">
									<label class="control-label">PAN NUMBER</label>
									<input type="text" name="pannumber"  id="pannumber" class="form-control">
									<input type="hidden" id="panphotoid" name="panphotoid" value="" />
								</div>
								<div class="form-group">
									<button type="button" name="pan-upload-button" id="pan-upload-button" data-toggle="modal" data-target="#ModalPan" class="btn btn-default col-xs-12">Upload PAN Copy</button>
									<center><a target="_blank" id="PANPrevew_Large"><img id="PanPreview" class="hidden" /></a></center>
								</div>-->
								<br><br>
								<div class="form-group">
									<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
									<button class="btn btn-default" type="reset">CLEAR</button>
									<button class="btn btn-primary" type="submit">ADD PAYEE</button>
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
											<th>STATE</th>
											<th>CITY</th>
											<th>PHONE</th>
											<th>EMAIL</th>
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
<div class="modal colored-header" id="ModalEditCustomer" data-backdrop="static" data-keyboard="false" role="dialog">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
				<h3>EDIT PAYEE</h3>
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body form">
			<form id="EditCustomer" role="form" method="post" parsley-validate novalidate>
				<div class="form-group">
					<label class="control-label">NAME</label>
					<input type="text" name="name"  id="edit_name" class="form-control parsley-validated" parsley-trigger="change" required>
				</div>
				<div class="form-group">
					<label class="control-label">ADDRESS</label>
					<textarea name="address"  id="edit_address" class="form-control parsley-validated" parsley-trigger="change"></textarea>
				</div>
				<div class="form-group">
					<label class="control-label">STATE</label>
					<select id="edit_stateid" class="select2" parsley-trigger="change" required onchange="load_city(this.value,'edit_cityid','')" title="Choose State">
						<option selected disabled value="">SELECT STATE</option>
						<?=getstate($dbcon,0)?>
					</select>

				</div>
				<div class="form-group">
					<label class="control-label">CITY</label>
					<select id="edit_cityid" class="select2" parsley-trigger="change" required title="Choose City">
						<option selected disabled value="">SELECT CITY</option>

					</select>
				</div>
				<div class="form-group">
					<label class="control-label">PHONE</label>
					<input type="text" id="edit_phone" name="edit_phone" class="form-control parsley-validated" parsley-trigger="change" parsley-type="digits">
				</div>
				<div class="form-group">
					<label class="control-label">EMAIL</label>
					<input type="text" name="edit_mail" id="edit_mail" class="form-control parsley-validated" parsley-trigger="change" parsley-type="email">
				</div>
				<!--<div class="form-group">
					<label class="control-label">PAN NUMBER</label>
					<input type="text" id="edit_pannumber" name="edit_pannumber" placeholder="_____-____-_" class="form-control">
					<input type="hidden" name="edit_panphotoid" id="edit_panphotoid" value="" />
				</div>
				<div class="form-group">
					<label>PAN PHOTO</label>
					<input type="file" id="pan_picture" name="pan_picture" tabindex="5" />
				</div>
				<font color="red">Uploading New Image will overwrite previous one.</font>-->
			</div>
			<div class="modal-footer">
				<input type="hidden" name="type" id="type" value="edit" />
				<input type="hidden" name="edit_token" id="edit_token" value="<?php echo $token; ?>" />
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button type="submit" class="btn btn-primary btn-flat">Update</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- Modal PANUPLOAD -->
<div class="modal" id="ModalPan" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
				<h3>UPLOAD PAN SCAN</h3>
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body form">
			<form id="FormUploadPan" role="form" method="post" action="<?php echo DOMAIN_CHEQUE.'process_image_upload'; ?>" enctype="multipart/form-data" >
				<div class="form-group">
					<label class="control-label">SELECT SCAN</label>
					<input name="ImageFile" id="imageInput" type="file" />
				</div>
				<div id="output"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button type="submit" id="PANUpload" class="btn btn-info btn-flat" type="submit">Upload</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->


	<?php include("../common/js.php"); ?>

	
	<script type="text/javascript">
	var customerTable;
		$(document).ready(function(){
			//initialize the javascript
			//Basic Instance
			customerTable = $("#datatable").dataTable({
				"bAutoWidth" : false,
				"bFilter" : true,
				"bSort" : false,
				"bProcessing": true,
				"bServerSide" : true,
				"oLanguage": {
						"sLengthMenu": "_MENU_",
						"sProcessing": "<img src='"+root_domain+"images/loading.gif'/> Loading ...",
						"sEmptyTable": "NO CUSTOMER ADDED YET !",
				},
				"aoColumns": [
					null,
					null,
					null,
					null,
					null,
					{"sWidth": "20%"}
				],
				"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
				"iDisplayLength": 10,
				"sAjaxSource": root_domain+'app/customer/',
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
			
			$("#FormNewCustomer").submit(function(e) {
				e.preventDefault();
				var check = $(this).parsley('validate');
				if(check) {
					Loading(true);
					var form_data = {
						type: "add",
						token :  $("#token").val(),
						name : $("#name").val(),
						address : $("#address").val(),
						stateid : $("#stateid").val(),
						cityid : $("#cityid").val(),
						phone : $("#phone").val(),
						mail : $("#mail").val()
						/*,
						pannumber : $("#pannumber").val(),
						panphotoid : $("#panphotoid").val()*/
					};
					
					console.log(form_data);
					
					$.ajax({
						type: "POST",
						cache:false,
						url: root_domain+'app/customer/',
						data: form_data,
						success: function(response)
						{
							if(response == "1") {
								$.gritter.add({
									text: 'CUSTOMER ADDED SUCCESSFULLY.',
									class_name: 'success',
									time: 1500,
								});
								customerTable.fnReloadAjax();
								$("#FormNewCustomer").reset();
								$("#PanPreview").attr("src","");
								$("#PANPrevew_Large").attr("href","");
								Unloading();
							}
							else if(response == "-1") {
								$.gritter.add({
									text: 'Customer already exists in the system.',
									class_name: 'info',
									time: 1500,
								});
							}
							else if(response == "0") {
								$.gritter.add({
									text: 'Error Occured while adding record.',
									class_name: 'danger',
									time: 1500,
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
			
			$("#EditCustomer").submit(function(e) {
				e.preventDefault();
				var check = $(this).parsley('validate');
				if(check) {
					Loading(true);
					var form_data = {
						type: "edit",
						edit_token :  $("#edit_token").val(),
						edit_id :  $("#edit_id").val(),
						name : $("#edit_name").val(),
						address : $("#edit_address").val(),
						stateid : $("#edit_stateid").val(),
						cityid : $("#edit_cityid").val(),
						phone : $("#edit_phone").val(),
						mail : $("#edit_mail").val()
					};
					console.log(form_data);
					$.ajax({
						type: "POST",
						//processData: false,
						//contentType: false,
						cache:false,
						url: root_domain+'app/customer/',
						data: form_data,
						success: function(response)
						{
							response=response.trim();
							if(response == "1") {
								$.gritter.add({
									text: 'UPDATED SUCCESSFULLY !',
									class_name: 'success',
									time: 1000,
								});
								customerTable.fnReloadAjax();
								$("#EditCustomer").reset();
								$("#ModalEditCustomer").modal("hide");
								Unloading();
							}
							else if(response == "-2") {
								$.gritter.add({
									text: 'PAN Photo File Invalid. Try again !',
									class_name: 'danger',
									time: 4000,
								});
							}
							else if(response == "-1") {
								$.gritter.add({
									text: 'Customer already exists in the system.',
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
		function edit_customer(id) {
			Loading();
			if(editReq != null)
				editReq.abort();
			
			editReq = $.ajax({
				type: "POST",
				url: root_domain+'app/customer/',
				data: { type : "preedit", id : id },
				success: function(response)
				{
					console.log(response);
					var obj = jQuery.parseJSON(response);
					$("#ModalEditCustomer").modal("show");
					$("#edit_id").val(id);
					$("#edit_name").val(obj.l_name);
					$("#edit_address").val(obj.m_address);
					$("#edit_stateid").select2('val',obj.stateid);
					load_city(obj.stateid,"edit_cityid",obj.cityid)
					//$("#edit_cityid").select2('val',obj.cityid);
					$("#edit_phone").val(obj.cust_mobile);
					$("#edit_mail").val(obj.cust_email);
					Unloading();
				}
			});	
		}
		
		function delete_customer(id) {
			bootbox.confirm("<h4 class='text-warning'><i class='fa fa-warning'></i> Are you sure ?</h4>",function(e) {
				if(e) {
					Loading(true);
					$.ajax({
						type: "POST",
						url: root_domain+'app/customer/',
						data: { type : "delete", token :  $("#token").val(), id : id },
						success: function(response)
						{
							response=response.trim();
							if(response == "1") {
								$.gritter.add({
									text: 'REMOVED SUCCESSFULLY !',
									class_name: 'success',
									time: 1000,
								});
								customerTable.fnReloadAjax();
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
	<script type="text/javascript">
		var i = 1;
		
		$(document).ready(function() {
			
			var options = { 
				//target:   '#output',   // target element(s) to be updated with server response 
				beforeSubmit:  beforeSubmit,  // pre-submit callback 
				success:       afterSuccess,  // post-submit callback 
				resetForm: true        // reset the form after successful submit 
			}; 
			
			
			$('#FormUploadPan').submit(function(e) {
				Loading(false);
				e.preventDefault();
				$(this).ajaxSubmit(options);  			
				// always return false to prevent standard browser submit and page navigation 
				return false; 
			});
			
		});
		
		function afterSuccess(responseText) {
			var obj = jQuery.parseJSON( responseText );
			if(obj.response == "1") {
				$("#ImageUpload").html("Replace PAN Photo");
				$("#PanPreview").removeClass("hidden");
				$("#PanPreview").attr("src",root_domain+"upload/pan/thumb_"+obj.image);
				$("#PANPrevew_Large").attr("href",root_domain+"upload/pan/"+obj.image);
				$("#panphotoid").val(obj.image);
				$("#ModalPan").modal("hide");
				Unloading();
			}
			else {
				$("#output").html("<font color='red'>Error while uploading file. Please try again.</font>");
			}
			console.log("success" + responseText);
		}
		
		//function to check file size before uploading.
		function beforeSubmit(){
			//check whether browser fully supports all File API
		   if (window.File && window.FileReader && window.FileList && window.Blob)
			{
				
				if( !$('#imageInput').val()) //check empty input filed
				{
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
						$("#output").html("<b>"+ftype+"</b> Unsupported file type!");
						return false
				}
				
				//Allowed file size is less than 1 MB (1048576)
				if(fsize>1048576) 
				{
					$("#output").html("<b>"+bytesToSize(fsize) +"</b> Too big Image file! <br />Please reduce the size of your photo using an image editor.");
					return false
				}
				
				$("#output").html("");  
			}
			else
			{
				//Output error to older unsupported browsers that doesn't support HTML5 File API
				$("#output").html("Please upgrade your browser, because your current browser lacks some new features we need!");
				return false;
			}
		}
function load_city(parentid,control,val1)
{	
	$.ajax({
	type: "POST",
	url: root_domain+'app/customer/',
	data: { type : "load_city",  id : parentid},
	success: function(responce){
				//console.log(responce);
				$('#'+control).select2({
					width: '100%'
				});
				$('#'+control).html(responce);
				$("#"+control).select2("val",val1);
			}
	});

}
		//function to format bites bit.ly/19yoIPO
		function bytesToSize(bytes) {
		   var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
		   if (bytes == 0) return '0 Bytes';
		   var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
		   return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
		}
	</script>
</body>
</html>
