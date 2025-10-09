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
		$TITLE = "Passbook New Entry";
		include("../common/head.php");
	?>
<style>
.datepicker{z-index:9999 !important}

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
				  <li class="active">New Cheque</li>
				</ol>
			</div>
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-5 col-lg-4">
					<div class="block-flat">
						<div class="content">
							<form method="post" id="FormPassbookEntry" role="form" parsley-validate novalidate> 
								<div class="form-group" >
									<label class="col-sm-2" style="padding-left:0px;">TYPE :</label>
									<div class="col-sm-9">
										
										<label style="margin-right: 20px;"> <input id="typeid1" name="typeid" type="radio" value="1" parsley-trigger="change" required onclick="show_hide_cheque();"> DEBIT</label>  
										<label style="margin-right: 20px;"> <input id="typeid2" name="typeid" type="radio" value="2" parsley-trigger="change" required> CREDIT</label> 
										<ul id="parsley-typeid" class="parsley-error-list"><li class="required" style="display: list-item;"></li></ul>
									</div>
								</div>
								<div class="form-group ">
									<label>SELECT ACCOUNT</label>
									<select id="acc_id" class="select2" parsley-trigger="change" required>
										<option selected disabled value="">SELECT BANK</option>
										<?php
											$query = $dbcon->query("SELECT `acc_id`,`acc_name`,`acc_chequeno`,`bank_name`,`bank_branch` FROM `coro_accounts` INNER JOIN `coro_banks` ON `bank_id` = `acc_bank`  WHERE `acc_status` = 1 AND `acc_of` = $_SESSION[user_id] ORDER BY `acc_name`");
											while($r = $query -> fetch_assoc()) {
												echo '<option value="'.$r['acc_id'].'">'.$r['acc_name'].' ('.$r['bank_name'].' - '.$r['bank_branch'].')</option>';
											}
										?>
									</select>
									
								</div>
								<div class="form-group ">
									<label>SELECT TRANSACTION TYPE</label>
									<select id="paymentmodeid" class="select2" parsley-trigger="change" required>
										<option selected disabled value="">SELECT TRANSACTION</option>
										<?php
											$query = $dbcon->query("SELECT `mode_id`,`mode_name` FROM `payment_mode` WHERE `mode_status` = 1 AND `user_id` = $_SESSION[user_id] ORDER BY `mode_name`");
											while($r = $query -> fetch_assoc()) {
												echo '<option value="'.$r['mode_id'].'" id="'.$r['mode_name'].'">'.$r['mode_name'].'</option>';
											}
										?>
									</select>
									
								</div>
								<div class="form-group " >
									<div class="col-lg-6" style="padding-left:0px;" >
										<label>AMOUNT</label>
										<input type="text" id="amount"  placeholder="Upto 99 Crore" class="form-control" parsley-trigger="change" required>
									</div>
									<div class="col-lg-6"  >
										<label>DATE</label>
										<input type="text" id="entry_date" value="<?php echo date("d-m-Y"); ?>" class="form-control datetime" parsley-trigger="change" required>
									</div>
								</div>
								<div class="form-group ">
									<label class="col-lg-12" style="padding-left: 0px">SELECT CUSTOMER</label>
									<div class="col-lg-9" style="padding-left:0px;">
									<select id="customer_id" class="select2 float-left" parsley-trigger="change" required style="padding-left:0px;">
										<option selected disabled value="">SELECT CUSTOMER</option>
										<?php
											$query = $dbcon->query("SELECT `cust_id`,`cust_name`,`cust_city` FROM `coro_customers` WHERE `cust_status` = 1 AND `cust_of` = $_SESSION[user_id] ORDER BY `cust_name`");
											while($r = $query -> fetch_assoc()) {
												echo '<option value="'.$r['cust_id'].'" data-name="'.$r['cust_name'].'" >'.$r['cust_name'].' - '.$r['cust_city'].'</option>';
											}
										?>
									</select>
									
								</div>
									<div class="col-lg-3" style="padding-left:0px;">
									<a class="btn btn-success float-left" href="javascript:;" onclick="add_customer();" title="Add Customer" style="width: 100px;"><i class="fa fa-plus"></i> Customer</a>
									</div>
								
								</div>
								<div class="form-group ">
									<label class="col-lg-12" style="padding-left: 0px" >ADD NOTE :</label>
									<textarea id="passbook_note" style="width: 100%;"></textarea>
								</div>
								<div class="form-group">
									<input type="hidden" id="token" name="token" value="<?php echo $token; ?>" />
									<input type="hidden" id="cheque_num" name="cheque_num" value="" />
									<button class="btn btn-default" type="reset">CLEAR</button>
									<button type="submit" id="save_only" class="btn btn-primary"><i class="fa fa-save"></i> Save Data</button>
									
								</div>
							</form>
						</div>
					</div>
				</div>
			<div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
					<div class="block-flat">
						<div class="content">
							<div class="table-responsive">
								<div class="header">							
									<h3><i class="fa fa-eye"></i> Filter</h3>
								</div>
								<form role="form" class="col-lg-12"> 
								<div class="form-group col-lg-4">
									<label>TYPE :</label>
									<select id="ps_typeid" name="ps_typeid" class="select2" tabindex="1" required>
										<option value="-1" selected>BOTH</option>
										<option value="1" >DEBIT</option>
										<option value="2" >CREDIT</option>
									</select>
								</div>
								
								<div class="form-group col-lg-4">
									<label>Account :</label>
									<select id="ps_accid" name="ps_accid" class="select2" tabindex="1" required>
										<option value="-1" selected>NOT CONSIDERED</option>
									<?php
										$query = $dbcon -> query("SELECT `acc_id`,`acc_name`,`bank_name` FROM `coro_accounts` INNER JOIN `coro_banks` ON `bank_id` = `acc_bank` WHERE `acc_of` = '$_SESSION[user_id]' ORDER BY `acc_name`,`bank_name`");
										while($r = $query->fetch_assoc()) {
											echo '<option value="'.$r['acc_id'].'">'.$r['acc_name'].'( '.$r['bank_name'].' )</option>';
										}
									?>
									</select>
								</div>
									<div class="form-group col-lg-4">
									<label>TRANSACTION :</label>
									<select id="ps_pmodeid" name="ps_pmodeid" class="select2" tabindex="1" required>
										<option value="-1" selected>NOT CONSIDERED</option>
									<?php
											$query = $dbcon->query("SELECT `mode_id`,`mode_name` FROM `payment_mode` WHERE `mode_status` = 1 AND `user_id` = $_SESSION[user_id] ORDER BY `mode_name`");
											while($r = $query -> fetch_assoc()) {
												echo '<option value="'.$r['mode_id'].'">'.$r['mode_name'].'</option>';
											}
										?>
									</select>
								</div>
								<div class="form-group col-lg-4">
									<label>CUSTOMER :</label>
									<select id="ps_custid" name="ps_custid" class="select2" tabindex="2">
										<option value="-9" selected>NOT CONSIDERED</option>
									<?php
										$query = $dbcon -> query("SELECT `cust_id`,`cust_name`,`cust_city` FROM `coro_customers` WHERE `cust_of` = '$_SESSION[user_id]' ORDER BY `cust_name`,`cust_city`");
										while($r = $query->fetch_assoc()) {
											echo '<option value="'.$r['cust_id'].'">'.$r['cust_name'].' ( '.$r['cust_city'].' )</option>';
										}
									?>
									</select>
								</div>
									<div class="form-group col-lg-4">
									<label>Date Range :</label>
									<input type="text" style="width:100%" name="date_range" id="date_range" class="form-control" value="" placeholder="SELECT DATERANGE" tabindex="1" />
								</div>
								<div class="form-group col-lg-4">
									<label>Amount :</label>
									<div class="input-group">
										<input type="text" id="ps_amount" name="ps_amount" class="form-control" placeholder="Amount" />
										<!--<span class="input-group-btn">
											<button class="btn btn-primary" id="sub_amount" name="sub_amount" type="button">Submit</button>
										</span>-->
									</div>
								</div>
								
								</form>
								<div class="header">							
									<h3><i class="fa fa-list"></i> List</h3>
								</div>
								<table id="datatable" class="no-border red">
									<thead class="no-border">
										<tr>
											<th>SR</th>
											<th>TYPE</th>
											<th>ACCOUNT</th>
											<th>TRANSACTION</th>
											<th>CUSTOMER</th>
											<th>AMOUNT</th>
											<th>DATE</th>
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
	
<!-- Add New Cheque book -->
<!-- Modal -->
<div class="modal colored-header" id="ModalEditPassbook" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
				<h3>New Chequebook</h3>
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<form id="FormEditPassbook" role="form" method="post" parsley-validate novalidate>
			<div class="modal-body form">
			
				<div class="form-group" >
					<label class="col-sm-2" style="padding-left:0px;">TYPE :</label>
					<div class="col-sm-9">
						<label style="margin-right: 20px;"> <input id="edit_typeid1" name="edit_typeid" type="radio" value="1"> DEBIT</label>  
						<label style="margin-right: 20px;"> <input id="edit_typeid2" name="edit_typeid" type="radio" value="2"> CREDIT</label> 
					</div>
				</div>
				<div class="form-group ">
					<label>SELECT ACCOUNT</label>
					<select id="edit_acc_id" class="select2" parsley-trigger="change" required>
						<option selected disabled value="">SELECT BANK</option>
						<?php
							$query = $dbcon->query("SELECT `acc_id`,`acc_name`,`acc_chequeno`,`bank_name`,`bank_branch` FROM `coro_accounts` INNER JOIN `coro_banks` ON `bank_id` = `acc_bank`  WHERE `acc_status` = 1 AND `acc_of` = $_SESSION[user_id] ORDER BY `acc_name`");
							while($r = $query -> fetch_assoc()) {
								echo '<option value="'.$r['acc_id'].'">'.$r['acc_name'].' ('.$r['bank_name'].' - '.$r['bank_branch'].')</option>';
							}
						?>
					</select>

				</div>
				<div class="form-group ">
					<label>SELECT TRANSACTION TYPE</label>
					<select id="edit_paymentmodeid" class="select2" parsley-trigger="change" required>
					<option selected disabled value="">SELECT TRANSACTION</option>
						<?php
							$query = $dbcon->query("SELECT `mode_id`,`mode_name` FROM `payment_mode` WHERE `mode_status` = 1 AND `user_id` = $_SESSION[user_id] ORDER BY `mode_name`");
							while($r = $query -> fetch_assoc()) {
								echo '<option value="'.$r['mode_id'].'">'.$r['mode_name'].'</option>';
							}
						?>
					</select>

				</div>
				<div class="form-group " >
					<div class="col-lg-6" style="padding-left:0px;" >
						<label>AMOUNT</label>
						<input type="text" id="edit_amount"  placeholder="Upto 99 Crore" class="form-control" parsley-trigger="change" required>
					</div>
					<div class="col-lg-6"  >
						<label>DATE</label>
						<input type="text" id="edit_entry_date" value="<?php echo date("d-m-Y"); ?>" class="form-control datetime" parsley-trigger="change" required>
					</div>
				</div><br/>
				<div class="form-group ">
					<label class="col-lg-12" style="padding-left: 0px">SELECT CUSTOMER</label>
					<div class="col-lg-9" style="padding-left:0px;">
					<select id="edit_customer_id" class="select2 float-left" parsley-trigger="change" required style="padding-left:0px;">
						<option selected disabled value="">SELECT CUSTOMER</option>
						<?php
							$query = $dbcon->query("SELECT `cust_id`,`cust_name`,`cust_city` FROM `coro_customers` WHERE `cust_status` = 1 AND `cust_of` = $_SESSION[user_id] ORDER BY `cust_name`");
							while($r = $query -> fetch_assoc()) {
								echo '<option value="'.$r['cust_id'].'" data-name="'.$r['cust_name'].'" >'.$r['cust_name'].' - '.$r['cust_city'].'</option>';
							}
						?>
					</select>

				</div>
				
				</div>
				<div class="form-group ">
					<label class="col-lg-12" style="padding-left: 0px" >ADD NOTE :</label>
					<textarea id="edit_passbook_note" style="width: 100%;"></textarea>
				</div>
			</div><!--Modal Body close-->
			<div class="modal-footer">
				<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-primary btn-flat" type="submit">Update Data</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- Add Customer Modal -->
<div class="modal colored-header" id="customer_add" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
				<h3>New Payee</h3>
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<form id="FormNewCustomer" role="form" method="post" parsley-validate novalidate>
			<div class="modal-body form">
			
				<div class="form-group">
					<label class="control-label">NAME</label>
					<input type="text" name="name"  id="name" class="form-control parsley-validated" parsley-trigger="change" required>
				</div>
				<div class="form-group">
					<label class="control-label">CITY</label>
					<input type="text" name="city"  id="city" class="form-control parsley-validated" parsley-trigger="change" required>
				</div>				
				<div class="form-group">
					<label class="control-label">PHONE</label>
					<input type="text" name="phone"  id="phone" class="form-control parsley-validated" parsley-trigger="change" parsley-type="digits">
				</div>
				<div class="form-group">
					<label class="control-label">EMAIL</label>
					<input type="text" name="mail"  id="mail" class="form-control parsley-validated" parsley-trigger="change" parsley-type="email">
				</div>
				<div class="form-group">
					<label class="control-label">PAN NUMBER</label>
					<input type="text" name="pannumber"  id="pannumber" class="form-control">
					<input type="hidden" id="panphotoid" name="panphotoid" value="" />
				</div>				
			</div>
			<div class="modal-footer">
				<input type="hidden" name="token" id="token" value="<?php echo $token; ?>" />				
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-primary btn-flat" type="submit">Add Payee</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->	
	<?php include("../common/js.php"); ?>
	<script type="text/javascript" src="js/bootstrap.daterangepicker/moment.min.js"></script>
	<script type="text/javascript" src="js/bootstrap.daterangepicker/daterangepicker.js"></script>
	<script type="" src="<?php echo ROOT_CHEQUE; ?>js/app/passbook_entry.js"></script>
</body>
</html>
