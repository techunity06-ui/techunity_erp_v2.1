<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="General Voucher";
	if(strpos($_SERVER['REQUEST_URI'], "account-voucher-update")==false) {
		$mode="Add";
		$date=date('d-m-Y');
	}
	else{
		$mode="Edit";
		$id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select accmst.* from account_voucher_mst as accmst 
		where accmst.voucher_mstid=$id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		$date=date('d-m-Y',strtotime($rel['voucher_date']));
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
</head>
<body>
<section id="container"  >
<?php include_once('../include/include_top_menu.php');?>
<!--sidebar start-->
<?php include_once('../include/left_menu.php');?>
<!--sidebar end-->
<!--main content start-->
<section id="main-content">
<section class="wrapper">


<div class="row">
<div class="col-lg-12">
<!--breadcrumbs start -->
<section class="panel">
	<header class="panel-heading">
		<h3> <span class="english"><?=$mode .' '.$form?></span></h3>
		
	</header>	
	<div class="">
		<ul class="breadcrumb">
			<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> 
				Home
			</a></li>
			<li ><a href="<?=ROOT.'account-voucher-list'?>"><span class="english"><?=$form?> List</span></a></li>
		</ul>
	</div>
</section>
<!--breadcrumbs end -->
</div>	
</div>
<!--state overview start-->
<div class="row">			
<div class="col-sm-12">
<section class="panel">
	<header class="panel-heading">
		<span class="english">  New <?=$form?></span>
	</header>	
	<div class="panel-body">
		<form class="form-horizontal" role="form" id="voucher_add" action="javascript:;" method="post" name="voucher_add">
			<div class="row">
				<!--<div class="col-md-12">
					<div class="col-md-6">
					<div class="form-group">
					<label class="col-md-4  control-label"><span class="english">Voucher Type*</span></label>
					<div class="col-md-6">
					<select class="select2" name="voucher_typeid" id="voucher_typeid" >
					<?php //=get_voucher_type_list_common($rel['voucher_typeid'],$dbcon);?>	
					</select>
					</div>
					</div>
					</div>
				</div>-->
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-md-4 control-label"><span class="english">Reference  No *</span></label>
						<div class="col-md-6">
							<input id="voucher_no" name="voucher_no" type="text" class="form-control" value="<?=$rel['voucher_no']?>" placeholder="Voucher No" required>		
						</div>
					</div>	
				</div>	
				<div class="col-md-5">
					<div class="form-group">
						<label class="col-md-4 control-label"><span class="english">Date*</span></label>
						<div class="col-md-6">
							<input id="voucher_date" name="voucher_date" type="text" class="form-control default-date-picker required" title="Date" value="<?=$date?>" placeholder="Voucher Date">
						</div>
					</div>
				</div>
				
				<div class="col-md-12">
					<div class="form-group">
						<table cellspacing="10" style="border-collapse:inherit; " id="product_list" class="display table table-bordered table-striped">
							<tr id="field">
								<th width="10%">Type</th>
								<th width="35%">Group</th>
								<th width="20%">Amount</th>
								<th width="5%">Action</th>
							</tr>
							<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
							<tr id="field1">
								<td>
									<select class="select2" id="type_id" name="type_id">
										<option value="">Choose Type</option>
										<option value="1">Credit</option>
										<option value="2">Debit</option>
									</select>
								</td>
								<td style="vertical-align:top;">
									<select class="select2" title="Select Account" name="l_id" id="l_id">
										<?=get_ledger_accounts($dbcon,'');?>
									</select>
								</td>
								<td style="vertical-align:top;">
									<input type="number"  title="Enter Amount" min="0" id="input_amt" name="input_amt" class="form-control" value="" />
								</td>
								<td style="vertical-align:top;">
									<input type="hidden" id="edit_id" name="edit_id" value="">
									<button type="button" class="btn btn-primary" id="add_btn" onClick="add_field()">Add</button>
								</td>
							</tr>
							
						</table>			
						
					</div>
				</div>
				<div id="sale_productdata"></div>
				
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-md-2 control-label"><span class="english">Notes *</span></label>
						<div class="col-md-8">
							<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
						</div>
					</div>
				</div>	
				
				<div class="col-md-12 text-center">
					<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
					<button type="submit" class="btn btn-success" id="saveprint" name="saveprint" onClick="submit_estimate()">Save and New</button> &nbsp;
					<a href="<?=ROOT.'account-voucher-list'?>" type="button" class="btn btn-danger">Cancel</a>
				</div>		
				<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
				<input type='hidden' name='eid' id='eid' value='<?=$rel['voucher_mstid']?>' />
				<input type='hidden' name='save_new' id='save_new' value='' />
				
			</div>
		</div><!--Vendor row end-->	
		
	</form>
</div>	
</section>
</div>
</div>
<!--state overview end-->
</section>
</section>
<!--main content end-->
<!--footer start-->


<?php include_once('../include/footer.php');?>
<!--footer end-->
</section>
	
<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   
<script src="<?=ROOT?>js/app/account_voucher_entry.js?<?=time()?>"></script>
<script>
$(".select2").select2({
	width: '100%'
});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
$('#l_id').select2({
	width: '100%',
	minimumInputLength: 2
});
</script>
</body>
</html>