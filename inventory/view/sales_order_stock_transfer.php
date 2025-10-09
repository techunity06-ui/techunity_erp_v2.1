<?php 
session_start();
include('../include/urlfile.php');
$form="Sales Order Stock Transfer";

$branch_id = $_SESSION['branch_id'];
$companyID = $_SESSION['company_id'];
if(strpos($_SERVER[REQUEST_URI], "returnable_channal_update")==true){
	$mode="Edit";
	$returnable_channal_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select rtn.* from tbl_returnable_channal as rtn
	left join tbl_returnable_channal_item as rtnch on rtnch.returnable_id=rtn.id
	where rtn.id=$returnable_channal_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));	
	$customer_id=$rel['cust_id'];
	$return_date = date('d-m-Y',strtotime($rel['return_date']));
	$challan_date = date('d-m-Y',strtotime($rel['challan_date']));
	$issue_date   = date('d-m-Y h:i A',strtotime($rel['issue_date']));
	$challan_type = $rel['challan_type'];
	$challan_return_type = $rel['challan_return_type'];
	$returnable_type = $rel['returnable_type'];
	$back="returnable_channal_list";
}else{
	$mode="Add";
	$so_transfer_date=date('d-m-Y');
	$back="returnable_channal_list";
	$stock_transfer_no=load_common_no($dbcon,17);
	$mode="Add";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title><?=$form?></title>
	<?php include_once($include.'include_css_file.php');?>
</head>
<body>
	<section id="container" class="sidebar-closed">
		<?php include_once($include.'include_top_menu.php');?>
		<?php include_once($include.'left_menu.php');?>
		<section id="main-content">
			<section class="wrapper">
				<?php//include_once('../include/equick_link.php');?>
				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								<h3><?=$mode.' '.$form?></h3>
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?=ROOT.INVENTORY_ROOT.'sales_order_stock_transfer_list'?>"><?=$form?> List</a></li>
								</ul>
							</div>
						</section>
					</div>	
				</div>
				<div class="row">			
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading">
								New <?=$form?>
							</header>	
							<div class="panel-body">
								<form class="form-horizontal" role="form" id="sales_order_stock_transfer_add" action="javascript:;" method="post" name="sales_order_stock_transfer_add" enctype="multipart/form-data">
									<div class="row">
										<div class="col-md-12" style="margin-top:10px;">
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">So Transfer No </label>
													<div class="col-md-8 col-xs-11">
														<input id="so_transfer_no" name="so_transfer_no" type="text" class="form-control" title="Date" value="<?=$stock_transfer_no?>" placeholder="So Transfer No" >
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">So Transfer Date </label>
													<div class="col-md-8 col-xs-11">
														<input id="so_transfer_date" name="so_transfer_date" type="text" class="form-control default-date-picker" title="So Transfer Date" value="<?=$so_transfer_date?>" placeholder="So Transfer Date">
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Product </label>
													<div class="col-md-8 col-xs-11">
														<input id="product_id" name="product_id" style="width:100%;" placeholder="Select Product" onchange="load_sales_order();"/>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-12">
											<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
												<tr id="field" >
													<th width="25%" class="text-center">sales order</th>
													<th width="25%" class="text-center">Qty</th>
													<th width="25%" class="text-center">Transfer Sales Order</th>
													<th width="25%" class="text-center">Transfer Qty</th>
												</tr>
												<tr>
													<td style="vertical-align:top;">
														<select class="select2"  title="Select Sales Order" placeholder="Sales Order" name="main_sales_order" id="main_sales_order" onchange="load_main_qty();">
															<?//=getunit($dbcon,0);?>
														</select>
													</td>
													<td style="vertical-align:top;">
														<input type="number" title="Enter Qty" id="main_qty" name="main_qty" class="form-control" value="" readonly />
													</td>
													<td style="vertical-align:top;">
														<select class="select2"  title="Select Sales Order" placeholder="Sales Order" name="transfer_sales_order" id="transfer_sales_order" onchange="load_transfer_qty();">
															<?//=getunit($dbcon,0);?>
														</select>
													</td>
													<td style="vertical-align:top;">
														<input type="number" title="Enter Transfer Qty" id="transfer_qty" name="transfer_qty" class="form-control" value="" />
														<input type="hidden" name="transfer_qty_val" id="transfer_qty_val" value="" />
													</td>
												</tr>
											</table>	
										</div>
										<div class="col-md-12">
											<div class="col-md-6" style="margin-top:12px;padding:10px" >
												<label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;"> Description </label>
												<div class="col-md-12">
													<div class="form-group">
														<textarea class="form-control" placeholder="Product Description" name="pro_des" id="pro_des" ></textarea>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-12" >
											<center>
												<div class="row">
													<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
													<a href="<?=ROOT.PURCHASE_ROOT.'po_list'?>" type="button" class="btn btn-danger">Cancel</a>
													
												</div>
											</center>
											<input type="hidden" name="mode" id="mode" value="<?=$mode?>" />
										</div>
									</form>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
		</section>
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.INVENTORY_ROOT?>js/app/sales_order_stock_transfer.js?<?=time()?>"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});

			$("#item_id").select2({
				width: '100%',
				minimumInputLength: 3
			});
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			$(".form_datetime").datetimepicker({
				format: 'dd-mm-yyyy hh:ii',
				autoclose: true,
				todayBtn: true,
				pickerPosition: "bottom-left"
			}); 
			var max_followup_date = '<?=$max_followup_date?>';
			var date = new Date();
         	var today = new Date(date.getFullYear(), date.getMonth(), date.getDate()); //start date is today
         	var endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + parseInt(max_followup_date)); //end date should not greater than 15 days
         	$(".form_datetime-meridian").datetimepicker({
         		format: "dd-mm-yyyy HH:ii P",
         		showMeridian: true,
         		autoclose: true,
         		todayBtn: true,
         		pickerPosition: "bottom-left",
         		startDate: today,
         		endDate: endDate
         	}); 
         	CKEDITOR.replace( 'pro_des', {
         		enterMode: CKEDITOR.ENTER_BR
         	});
         </script> 
     </body>
     </html>