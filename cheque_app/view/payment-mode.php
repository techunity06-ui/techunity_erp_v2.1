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
		$TITLE = "Payment Mode";
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
				  <li class="active">Payment Mode</li>
				</ol>
			</div>		
			<!--<div class="row">
				<div class="hidden-xs hidden-sm col-md-12 col-lg-12">
					<div class="pull-left">
						<button onClick="window.open(root_domain+'export/banks?type=csv','_blank');" type="button" class="btn btn-default"><i class="fa fa-bars"></i> Export CSV</button>
						<button onClick="window.open(root_domain+'export/banks?type=pdf', '_blank');" type="button" class="btn btn-default"><i class="fa fa-cloud-upload"></i> Export PDF</button>
					</div>
				</div>
			</div>-->
			<div class="row">
				<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
					<div class="block-flat">
						<div class="header">							
							<h3>New Payment Mode</h3>
						</div>
						<div class="content">
							<form id="FormNewBank" autocomplete="off"  role="form" method="post" parsley-validate novalidate>
								<div class="form-group">
									<label class="control-label">MODE NAME</label>
									<input type="text" name="mode_name"  id="mode_name" class="form-control parsley-validated" parsley-trigger="change" required title="Enter Mode Name">
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
									<button class="btn btn-primary" type="submit">ADD MODE</button>
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
	<script type="" src="<?php echo ROOT_CHEQUE; ?>js/app/pay-mode.js"></script>
</body>
</html>