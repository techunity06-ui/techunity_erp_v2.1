<?php 

	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Start Process";
	$countryid='101';$stateid='1';$cityid='1';
	
	$mode="add_start_process";
	$id=$dbcon->real_escape_string($_REQUEST['id']);
	//echo $id;
	$query="select ap.*,p.product_name,p.product_type,pr.process_name,dqty,r.rp_req_no,j.jobwork_no from tbl_allocate_process as ap left join product_mst as p on p.product_id=ap.p_product_id left join process_mst as pr on pr.process_id=ap.process_id left join tbl_request_product as r on r.rp_id=ap.p_ref_id left join tbl_jobwork as j on j.j_alloc_process_id=ap.p_id
	left join (select sum(pt_qty) as dqty,pt_alloc_id from tbl_allocate_process_trn group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id where p_id='$id'";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	
	$order_date='';$dispatch_date='';
	
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	
	if($rel['pr_process_type']==1)
	{
		$pr_type='inhouse';
	}
	else
	{
		$pr_type='outward';
	}
	
	//get Jobcard NO 
	
	$sel1=$dbcon->query("select jobwork_id from tbl_jobwork where j_alloc_process_id='$id'");
	$count_job=mysqli_num_rows($sel1);
	
	
	
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
</head>
<body>
<section id="container" class="sidebar-closed">
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
				  
					<section class="panel" >
						
						<header class="panel-heading">
							<h3 style="float:left;"> <?=$mode .' '.$form?></h3>
						</header>	
						<div class="" style="padding:20px !important;">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li><a href="<?=ROOT.'opening_detail_list/'.$rel['process_id'].'/'.$rel['pr_process_type'];?>">Start Process </a></li>
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
					  New <?=$form?>
					</header>	
				<div class="panel-body">
		<form class="form-horizontal" role="form" id="start_allocate_add" action="javascript:;" method="post" name="start_allocate_add">
			<div class="row">
				<div class="col-md-4">
						<label class="col-md-4 control-label"> Product Name </label>
						<div class="col-md-6 col-xs-11">
							<input type="text" class="form-control" id="pr_product_id" name="pr_product_id" value="<?=$rel['product_name']; ?>" readonly />
						</div>
				</div>
				<div class="col-md-4">
						<label class="col-md-4 control-label"> Process Name </label>
						<div class="col-md-6 col-xs-11">
							<input type="text" class="form-control" id="pr_process_id" name="pr_process_id" value="<?=$rel['process_name']; ?>" readonly />
						</div>
				</div>
				<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">Process Type  *</label>
							<div class="col-md-6 col-xs-11">
								<input id="pr_process_type" name="pr_process_type" type="text" class="form-control" title="Process Type" value="<?=$pr_type;?>" placeholder="Process Type" required readonly>		
							</div>
						 </div>
				</div>
				
				<div class="col-md-12"></div>
				
				<div class="col-md-4">
					<div class="form-group">
						<label class="col-md-4 control-label">Start Time *</label>
						<div class="col-md-6 col-xs-11">
							<input type="text" class="form-control" id="pr_st_time1" name="pr_st_time1" value="<?=date('d-m-Y h:i:sa') ?>" readonly />
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label class="col-md-4 control-label">Process No*</label>
						<div class="col-md-6 col-xs-11">
							<input id="pr_process_no" name="pr_process_no" type="text" class="form-control" title="Enter Challan No" value="<?=$rel['rp_req_no'];?>" placeholder="Process No"  readonly>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
					  <label class="col-md-4 control-label">Jobcard No*</label>
						<div class="col-md-6 col-xs-11">
							<input id="pr_job_no" name="pr_job_no" type="text" class="form-control required valid" title="Date" value="<?php if($count_job>0){  echo $rel['jobwork_no']; } ?>" readonly placeholder="Jobwork No">
						</div>
					</div>	
				</div>
									
				<div class="col-md-12"></div>
				
				<div class="col-md-4">
					<div class="form-group">  	
						<label class="col-md-4 control-label">Pending Qty*</label>
						<div class="col-md-6 col-xs-11">
							<input type="text" id="pr_p_qty1" name="pr_p_qty1" class="form-control"  value="<?=$rel['pen_qty']-$rel['dqty'];?>" placeholder="" readonly>
						</div>
					</div>	
				</div>
			
				
				<div class="col-md-4">
					<div class="form-group">
						<label class="col-md-4 control-label">Process Qty *</label>
						<div class="col-md-6 col-xs-11">
							<input type="text" name="machine_no" id="machine_no" class="form-control" value="" required  />
							<input type="hidden" name="request_no" id="request_no" class="form-control" value="<?=$rel['p_ref_id'];?>" readonly />
						</div>
					</div>
				</div>	
				
				<?php if($rel['pr_process_type']=='2') { ?>
				<div class="col-md-4">
					<div class="form-group">
					  <label class="col-md-4 control-label">Jobwork No*</label>
						<div class="col-md-6 col-xs-11">
							<input id="pr_jobwork_no" name="pr_jobwork_no" type="text" class="form-control required valid" title="Date" value="<?php if($count_job>0){  echo $rel['jobwork_no']; } ?>" readonly placeholder="Jobwork No">
						</div>
					</div>	
				</div>
				<?php } ?>
				
			
				
				<?php if($rel['pr_process_type']=='2') { ?>
				
					<div class="col-md-12">
						
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Select Vendor *</label>
								<div class="col-md-6 col-xs-11">
									<select class="select2" id="pr_vendor_id" name="pr_vender_id">
										<?=getcust($dbcon,'');?>
									</select>
								</div>
							</div>
						</div>
						
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Chalan No *</label>
								<div class="col-md-6 col-xs-11">
									<input type="text" name="pr_chalan_no" id="pr_chalan_no" class="form-control" value=""  />
								</div>
							</div>
						</div>
					
					</div>
					
				<?php } ?>
		
				<div class="col-md-12">
				
				
					<div class="col-md-6 col-md-offset-4">  	
						<strong style='color:red;display:none' id="error_start_msg">You can Not Start The Process As the Machine Qty is not Available</strong><br>
						<input type="submit" id="sp_btn" name="submit" class="btn btn-success" value="Start The Process" />
					</div>
				</div>		
			
			
			</div>
			</div><!--Vendor row end-->	
			<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
			<input type='hidden' name='save_print' id='save_print' value='' />
			<input type='hidden' name='eid' id='eid' value='<?=$id;?>' />
			<input type='hidden' name='product_id_hid' id='product_id_hid' value='<?=$rel['p_product_id'];?>' />
			<input type='hidden' name='product_type_hid' id='product_type_hid' value='<?=$rel['product_type'];?>' />
			<input type='hidden' name='product_qty_hid' id='product_qty_hid' value='<?=$rel['pen_qty'];?>' />
			<input type='hidden' name='process_id_hid' id='process_id_hid' value='<?=$rel['process_id'];?>' />
			<input type='hidden' name='process_type_hid' id='process_type_hid' value='<?=$rel['pr_process_type'];?>' />
			<input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
			<input type="hidden" name="invoicetype_id_jobwork" id="invoicetype_id_jobwork" value="" />
			
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

    <!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php
	include_once('../include/get_warehose_deduction_modal.php');
	include_once('../include/include_js_file.php');

	//include_once('../include/serial_number_add.php');
?>   

<script src="<?=ROOT?>js/app/allocate_process_opening.js"></script>

	
<!--<script src="js/count.js"></script>-->
<script>
$(".select2").select2({
	width: '100%'
});
$("#product_id").select2({
	width: '83%'
});

$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
$(".form_datetime-meridian").datetimepicker({
    format: "dd-mm-yyyy HH:ii P",
    showMeridian: true,
    autoclose: true,
    todayBtn: true,
    pickerPosition: "bottom-left"
});

function consinee_change(val){
	if(val=='1'){
		$('#consignee_id').select2("val","");
		$('#consignee').hide();
	}
	else{
		$('#consignee').show();
	}
}

</script>

<?php
if($mode=="add_start_process" && $count_job==0){
	echo "<script>get_series_no()</script>";
	echo "<script>get_series_no_jobwork()</script>";
} 
?>
</body>
</html>