<?php 

	session_start();
	include('../include/urlfile.php');	
	$form="Start Process";
	$countryid='101';$stateid='1';$cityid='1';
	
	$mode="Process History";
	$id=$dbcon->real_escape_string($_REQUEST['id']);
	//echo $id;
	$query="select ap.*,p.product_name,p.product_type,pr.process_name,dqty,r.rp_req_no,j.jobwork_no,r.job_card_no from tbl_allocate_process as ap left join product_mst as p on p.product_id=ap.p_product_id left join process_mst as pr on pr.process_id=ap.process_id left join tbl_request_product as r on r.rp_id=ap.p_ref_id left join tbl_jobwork as j on j.j_alloc_process_id=ap.p_id
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
	
	$sel1=$dbcon->query("select job_work_sub_trn_id from tbl_job_work_sub_trn where job_work_sub_trn_status != 2 and p_id='$id'");
	$count_job=mysqli_num_rows($sel1);
	
	
	
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Process History</title>
<?php include_once($include.'include_css_file.php');?>
</head>
<body>
<section id="container" class="sidebar-closed">
    <?php include_once($include.'include_top_menu.php');?>
      <!--sidebar start-->
      <?php include_once($include.'left_menu.php');?>
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
							  <li><a href="<?=ROOT.PRODUCTION_ROOT.'process_detail_list/'.$rel['process_id'].'/'.$rel['pr_process_type'];?>">Start Process </a></li>
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
						<label class="col-md-4 control-label">Process No*</label>
						<div class="col-md-6 col-xs-11">
							<input id="pr_process_no" name="pr_process_no" type="text" class="form-control" title="Enter Challan No" value="<?=$rel['rp_req_no'];?>" placeholder="Process No" required readonly>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
					  <label class="col-md-4 control-label">Jobcard No*</label>
						<div class="col-md-6 col-xs-11">
							<input id="pr_job_no" name="pr_job_no" type="text" class="form-control required valid" title="Date" value="<?php if($count_job>0){  echo $rel['job_card_no']; } ?>" readonly placeholder="Jobwork No">
						</div>
					</div>	
				</div>
									
				<div class="col-md-12"></div>
				
				
				
				<div class="col-md-12">
					<div class="panel-body">
						<div class="adv-table">
							 <table class="display table table-bordered table-striped" id="">
								<thead>
								  <tr>
									<th>#</th>
									<th>Status</th>
									<th>Time</th>
									<th>Qty</th>
									<th>User</th>
								  </tr>
								  </tr>
								  </tr>
								  </tr>
								</thead>
								<tbody>
								
									<?php 
									
										$cnt=1;
										$sel2=$dbcon->query("select * from tbl_allocate_process_trn where pt_alloc_id='$id'"); 
										while($row2=mysqli_fetch_array($sel2))
										{
											if($row2['p_status']=='0')
											{
												//$status="<strong style='color:red'>Not Started</strong>";
												$status="<strong style='color:green'>Started</strong>";
											}
											else if($row2['p_status']=='1')
											{
												$status="<strong style='color:red'>Ended</strong>";
											}
											
									?>
									<tr>
										<td><?php echo $cnt ?></td>
										<td><?php echo $status; ?></td>
										<td><?php echo date("d/m/Y h:i:sa",strtotime($row2['process_time']));  ?></td>
										<td><?php echo $row2['pt_qty'] ?></td> 
										<td><?php echo find_user_name($dbcon,$row2['user_id']); ?></td> 
									</tr>
									<?php
											$cnt++;
										}
									?> 
								
								</tbody>				 
							</table>
						</div>
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
	include_once($include1.'get_warehose_deduction_modal.php');
	include_once($include.'include_js_file.php');

	//include_once('../include/serial_number_add.php');
?>   

<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/allocate_process.js"></script>

	
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
} 
?>
</body>
</html>