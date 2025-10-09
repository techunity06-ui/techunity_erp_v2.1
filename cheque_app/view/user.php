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
		$TITLE = "User";
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
				include("../common/dashboard_quick_link.php");
			?>
			<div class="page-head">
				<h2><?php echo $TITLE; ?></h2>
				<ol class="breadcrumb">
				  <li><a href="<?php echo DOMAIN_CHEQUE.'home'; ?>">Home</a></li>
				  <li class="active"><?php echo $TITLE; ?></li>
				</ol>
			</div>					
			<div class="row">
				<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
					<div class="block-flat">
						<div class="header">							
							<h3>New <?php echo $TITLE; ?></h3>
						</div>
						<div class="content">
							<form id="FrmUser" autocomplete="off"  role="form" method="post" parsley-validate novalidate>
								<div class="form-group">
									<label class="control-label">COMPANY NAME</label>
									<input type="text" name="company_name"  id="company_name" class="form-control parsley-validated" parsley-trigger="change" required>
								</div>
								<div class="form-group">
									<label class="control-label">Address</label>
									<textarea id="address" name="address" class="form-control parsley-validated" parsley-trigger="change" required></textarea>
								</div>
								<div class="form-group">
									<label class="control-label">Contact No.</label>
									<input type="text" id="contact_no" name="contact_no" class="form-control parsley-validated" parsley-trigger="change" parsley-type="digits" required>
								</div>		
								<div class="form-group">
									<label class="control-label">Email</label>
									<input type="email" id="email" name="email" class="form-control parsley-validated" parsley-trigger="change" parsley-type="email" required autocomplete="off">
								</div>
								<div class="form-group">
									<label class="control-label">Password</label>
									<input type="password" id="password" name="password" class="form-control parsley-validated" parsley-trigger="change" required>
								</div>
								<br><br>
								<div class="form-group">
									<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
									<input type="hidden" name="type" id="type" value="add" />
									<button class="btn btn-default" type="reset">CLEAR</button>
									<button class="btn btn-primary" type="submit">ADD USER</button>
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
											<th>COMPANY NAME</th>
											<th>EMAIL</th>
											<th>PHONE</th>											
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
<div class="modal colored-header" id="ModalEditUser" data-backdrop="static" data-keyboard="false" role="dialog">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
				<h3>EDIT <?php echo $TITLE;?></h3>
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body form">
			<form id="EditCustomer" role="form" method="post" parsley-validate novalidate>
				<div class="form-group">
					<label class="control-label">COMPANY NAME</label>
					<input type="text" id="edit_name" name="company_name" class="form-control parsley-validated" parsley-trigger="change" required>
				</div>
				<div class="form-group">
					<label class="control-label">Address</label>
					<textarea id="edit_address" name="address" class="form-control parsley-validated" parsley-trigger="change" required></textarea>
				</div>
				<div class="form-group">
					<label class="control-label">Contact No.</label>
					<input type="text" id="edit_phone" name="contact_no" class="form-control parsley-validated" parsley-trigger="change" parsley-type="digits" required>
				</div>		
				<div class="form-group">
					<label class="control-label">Email</label>
					<input type="email" id="edit_email" name="email" class="form-control parsley-validated" parsley-trigger="change" parsley-type="email" required autocomplete="off">
				</div>
				<div class="form-group">
					<label class="control-label">Password</label>
					<input type="password" id="edit_password" name="password" class="form-control parsley-validated" parsley-trigger="change">
				</div>
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
	<script type="text/javascript" src="<?php echo ROOT_CHEQUE; ?>js/app/user.js"></script>	
</body>
</html>
