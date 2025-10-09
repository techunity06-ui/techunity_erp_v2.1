<?php 
	session_start();
	include('../include/urlfile.php');
	$form="Zone";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	//check permission for process type add
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	ADMINISTRATOR_ZONE_LIST,
        ADMINISTRATOR_ZONE_CREATE
    ]);

    if(!in_array(ADMINISTRATOR_ZONE_LIST,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
</head>
<body>
<section id="container" >
<?php include_once('../include/include_top_menu.php');?>
<!--sidebar start-->
<?php include_once('../include/left_menu.php');?>

<style>
	.common_form_error
	{
		color:red !important;
	}
</style>
<!--sidebar end-->
<!--main content start-->
<section id="main-content">
	<section class="wrapper">
		<div class="row">
			<div class="col-lg-12">
				<!--breadcrumbs start -->
				<section class="panel">
					<header class="panel-heading">
						<h3>New <?=$form?></h3>
					</header>	
					<div class="">
						<ul class="breadcrumb">
							<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							<li><a href="<?=ROOT.'masters_list'?>"> Masters List</a></li>
							<li class="active"><?=$form?> List</li>
						</ul>
					</div>
				</section>
				<!--breadcrumbs end -->
			</div>	
		</div>
		<!--unit overview start-->
		<div class="row">
			<?php if(in_array(ADMINISTRATOR_ZONE_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-12">
					<section class="panel">
						<header class="panel-heading">
							New <?=$form?>
						</header>	
						<div class="panel-body">
							
							<div class="col-md-3">
								<div class="form-group">
									<label for="edit_zone_name">Cost Center</label>
									<select class="form-control" name="cost_center" id="cost_center" onchange="get_cost_center(this.value)">
										<option value="">--Select Cost Center--</option>
										<option value="yes">Yes</option>
										<option value="no">No</option>
									</select>
								</div>			  
							</div>
							
							<div class="col-md-3">
								<div class="form-group">
									<label for="edit_zone_name">Transportation</label>
									<select class="form-control" name="ledger_transportation" id="ledger_transportation" onchange="get_ledger_transportation(this.value)">
										<option value="">--Select Transportation--</option>
										<option value="yes">Yes</option>
										<option value="no">No</option>
									</select>
								</div>			  
							</div>
							
							<div class="col-md-3">
								<div class="form-group">
									<label for="edit_zone_name">Salesman</label>
									<select class="form-control" name="ledger_salesman" id="ledger_salesman" onchange="get_ledger_salesman(this.value)">
										<option value="">--Select Salesman--</option>
										<option value="yes">Yes</option>
										<option value="no">No</option>
									</select>
								</div>			  
							</div>
							
							
						</div>
					</section>
				</div>
			<?php } ?>
			
			</div>
		</div>
		
		<!--unit overview end-->
	</section>
</section>
<!--main content end-->
<!--footer start-->
<?php include_once('../include/footer.php');?>
<!--footer end-->
</section>


<!-- Modal Cost Center-->
<div class="modal colored-header info" id="ModalCostCenter" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Cost Center Detail</h3>
			
		</div>
		<div class="modal-body form">
			
			<?php 
				$master_details=get_table_details_option($dbcon,'tbl_cost_center','cost_center_id','cost_center_name',' and isdelete=0');
				$balance_type=getbalance_type_new($dbcon);
			?>
			
			<div class="row">
			<div class="col-md-12 margin_row">
			
				<div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">Cost Center</label>
						<select class="form-control" name="costcenter_id" id="costcenter_id" tabindex="1">
							<option value="">--Select Cost Center--</option>
							<?=$master_details;?>
						</select>
						<strong id="cost_error_id" class="common_form_error"></strong>
					</div>	
				</div>
				
				<div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">Amount</label>
						<input type="text" class="form-control" name="costcenter_amount" id="costcenter_amount"  tabindex="2" />
						<strong id="cost_amount_id" class="common_form_error"></strong>
					</div>	
				</div>

				<div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">Type</label>
						<select class="form-control" name="costcenter_entry_type" id="costcenter_entry_type"  tabindex="3">
							<?=$balance_type;?>
						</select>
						<strong id="cost_entry_id" class="common_form_error"></strong>
					</div>
				</div>
				
			</div>
			
			<div class="col-md-12 margin_row">
				<div class="col-md-3">
					<input type="button" id="add_cost_center_btn" value="Add"  class="btn btn-primary" onclick="add_cost_center()"  tabindex="4" />
				</div>
			</div>
			
			<div class="extra_data">
								
				<input type="hidden" name="cost_center_voucher_type" id="cost_center_voucher_type" placeholder="Voucher Type eg. sale , purchase">
				<input type="hidden" name="cost_center_ledger_id" id="cost_center_ledger_id" placeholder="Ledger Id">
				<input type="hidden" name="cost_center_table" id="cost_center_table" placeholder="table name of sale , purchase , payment..">
				<input type="hidden" name="cost_center_table_id" id="cost_center_table_id" placeholder="primary key of that inserted table ">
				<input type="hidden" id="edit_id" value="" />
			</div>
		</div>
		
		<div class="row">
			<div class="col-md-12 margin_row">
				<div class="adv-table">
					<table class="display table table-bordered table-striped" id="cost-table">
						<thead>
							<tr>
								<th>Sr. NO.</th>
								<th>Cost Center Name</th> 
								<th>Amount</th> 
								<th>Entry Type</th> 
								<th class="hidden-phone">Action</th>					  
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->



<!-- Modal Transportation-->
<div class="modal colored-header info" id="ModalTransportation" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Transportation Detail</h3>
			
		</div>
		<div class="modal-body form">
			
			<?php 
				$master_details=get_table_details_option($dbcon,'transportation_details','id','transportation_name');
				$balance_type=getbalance_type_new($dbcon);
			?>
			
			<div class="row">
			<div class="col-md-12 margin_row">
			
				<div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">Transportation Details</label>
						<select class="form-control" name="transport_id" id="transport_id" tabindex="1">
							<option value="">--Select Transportation--</option>
							<?=$master_details;?>
						</select>
						<strong id="transport_id_error" class="common_form_error"></strong>
					</div>	
				</div>
				
				<div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">GR/RR No</label>
						<input type="text" class="form-control" name="transport_gr_no" id="transport_gr_no"  tabindex="2" />
					</div>	
				</div>
			
				<div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">GR/RR Date</label>
						<input type="text" class="form-control default-date-picker" name="transport_gr_date" id="transport_gr_date" value="<?=date("d/m/Y");?>"  tabindex="2" />
					</div>
				</div>
				
			</div>
			
			<div class="col-md-12 margin_row">
			
				<div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">Vehicle No</label>
						<input type="text" class="form-control" name="transport_vehicle_no" id="transport_vehicle_no"  tabindex="2" />
					</div>	
				</div>
				
				<div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">Staion Name</label>
						<input type="text" class="form-control" name="transport_station" id="transport_station"  tabindex="2" />
					</div>	
				</div>
			
			</div>
			
			<div class="col-md-12 margin_row">
				<div class="col-md-3">
					<input type="button" id="add_transport_btn" value="Add"  class="btn btn-primary" onclick="add_transport()"  tabindex="4" />
				</div>
			</div>
			
			<div class="extra_data">
								
				<input type="hidden" name="transport_voucher" id="transport_voucher" placeholder="Voucher Type eg. sale , purchase">
				<input type="hidden" name="transport_transaction_table" id="transport_transaction_table" placeholder="table name of sale , purchase , payment..">
				<input type="hidden" name="transport_transaction_table_id" id="transport_transaction_table_id" placeholder="primary key of that inserted table ">
				<input type="hidden" id="edit_id_transport" value="" />
			</div>
		</div>
		
		<div class="row">
			<div class="col-md-12 margin_row">
				<div class="adv-table">
					<table class="display table table-bordered table-striped" id="transport-table">
						<thead>
							<tr>
								<th>Sr. NO.</th>
								<th>Transporter Name</th> 
								<th>GR/RR No</th> 
								<th>GR/RR Date</th> 
								<th>Vehicle No</th> 
								<th>Station</th> 
								<th class="hidden-phone">Action</th>					  
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->



<!-- Modal Salesman  -->
<div class="modal colored-header info" id="ModalCostCenter" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Salesman Detail</h3>
			
		</div>
		<div class="modal-body form">
			
			<?php 
				$master_details=get_table_details_option($dbcon,'tbl_cost_center','cost_center_id','cost_center_name',' and isdelete=0');
				$balance_type=getbalance_type_new($dbcon);
			?>
			
			<div class="row">
			<div class="col-md-12 margin_row">
			
				<div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">Cost Center</label>
						<select class="form-control" name="costcenter_id" id="costcenter_id" tabindex="1">
							<option value="">--Select Cost Center--</option>
							<?=$master_details;?>
						</select>
						<strong id="cost_error_id" class="common_form_error"></strong>
					</div>	
				</div>
				
				<div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">Amount</label>
						<input type="text" class="form-control" name="costcenter_amount" id="costcenter_amount"  tabindex="2" />
						<strong id="cost_amount_id" class="common_form_error"></strong>
					</div>	
				</div>

				<div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">Type</label>
						<select class="form-control" name="costcenter_entry_type" id="costcenter_entry_type"  tabindex="3">
							<?=$balance_type;?>
						</select>
						<strong id="cost_entry_id" class="common_form_error"></strong>
					</div>
				</div>
				
			</div>
			
			<div class="col-md-12 margin_row">
				<div class="col-md-3">
					<input type="button" id="add_cost_center_btn" value="Add"  class="btn btn-primary" onclick="add_cost_center()"  tabindex="4" />
				</div>
			</div>
			
			<div class="extra_data">
								
				<input type="hidden" name="cost_center_voucher_type" id="cost_center_voucher_type" placeholder="Voucher Type eg. sale , purchase">
				<input type="hidden" name="cost_center_ledger_id" id="cost_center_ledger_id" placeholder="Ledger Id">
				<input type="hidden" name="cost_center_table" id="cost_center_table" placeholder="table name of sale , purchase , payment..">
				<input type="hidden" name="cost_center_table_id" id="cost_center_table_id" placeholder="primary key of that inserted table ">
				<input type="hidden" id="edit_id" value="" />
			</div>
		</div>
		
		<div class="row">
			<div class="col-md-12 margin_row">
				<div class="adv-table">
					<table class="display table table-bordered table-striped" id="cost-table">
						<thead>
							<tr>
								<th>Sr. NO.</th>
								<th>Cost Center Name</th> 
								<th>Amount</th> 
								<th>Entry Type</th> 
								<th class="hidden-phone">Action</th>					  
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   
<script src="<?=ROOT?>js/app/common_form_finance.js?<?=time()?>"></script>
<script>
$(".select2").select2({
	width: '100%'
});
</script>
</body>
</html>
