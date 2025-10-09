<?php 
	session_start();
	include('../include/urlfile.php');	
	$form="Product ";
	$countryid='101';
	$stateid='1';
	$cityid='1';
	
	
	$mode="Add";$direct_add='1';$request=1;
	$id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from tbl_jobwork where jobwork_id='$id'";
	$rel=mysqli_fetch_assoc($dbcon->query($query));	

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Jobcard Detail</title>
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
			<?php//include_once('../include/equick_link.php');?>
    		<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
					<section class="panel">
						<header class="panel-heading">
						  <h3><?=$mode.' '.$form?></h3>
						</header>	
						<div class="">
							<ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li><a href="<?=ROOT.'po_list'?>"><?=$form?> List</a></li>
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
				<form class="form-horizontal" role="form" id="product_request_add" action="javascript:;" method="post" name="product_request_add">
						<div class="row">
							
							<div class="col-md-12">
								
								<div class="col-md-6">
									 <div class="form-group">
									  <label class="col-md-4 control-label">Jobcard No</label>
									  <div class="col-md-6 col-xs-11">
										<input id="po_req_no" name="po_req_no" type="text" class="form-control" title="Req No" value="<?=$rel['jobwork_no'];?>" placeholder="" readonly>
										</div>
									 </div>	
								</div>
								
								<div class="col-md-6">
									 <div class="form-group">
									  <label class="col-md-3 control-label">Date</label>
										<div class="col-md-5 col-xs-11">
											<input id="po_req_date" name="po_req_date" type="text" class="form-control default-date-picker" title="Date" value="<?=date("d/m/Y",strtotime($rel['jobwork_date']));?>" placeholder="">
										</div>
									 </div>	
								</div>	
								
								
							</div>	
							
							
							<div class="col-md-12" style="margin-top:10px;">
								<div class="col-md-6">
								 <div class="form-group">
								  <label class="col-md-4 control-label">Product Name </label>
								  <div class="col-md-6 col-xs-11">
									<input id="po_product_name" name="po_product_name" type="text" class="form-control" title="Date" value="<?=get_pro_field($dbcon,$rel['sel_product_id'],'product_name');?>" placeholder="Product Name" readonly>
									</div>
								 </div>	
								</div>	
								<div class="col-md-6">
								 <div class="form-group">  	
								  <label class="col-md-3 control-label" > Requested Quantity </label>
									<div class="col-md-5 col-xs-11">
										<input id="rp_req_qty" name="rp_req_qty" type="text" class="form-control" title="Date" value="<?=$rel['sel_product_qty'];?>" placeholder="Request Qty" readonly>
									</div>
								 </div>	
								</div>
							</div>
				
					 		 	
							<div class="col-md-12" style="margin-top:10px;">
															
								
								<table class="table table-bordered">
									
									<thead>
										<tr>					
											<th><strong>SR. NO.</strong></th>
											<th><strong>Item Description</strong></th>
											<th><strong>Minimum Qty</strong></th>
											<th><strong>Current Stock</strong></th>
											<th><strong>Requested Qty</strong></th>
											<th><strong>Alloted Qty</strong></th>
											<th><strong>Allocate</strong></th>
										</tr>
									</thead>
									
									<tbody>
										
										<?php 
											
											$cnt=1;$counter_tree = 0;
											$qry="select * FROM `tbl_bomtrn` as trn 
											left join product_mst as product on product.product_id=trn.product_id 
											left join unit_mst as per on per.unitid=product.product_base_unit
											where bom_trn_status!=1 and trn.product_id='$rel[sel_product_id]' order by bom_trn_id";
											$result=$dbcon->query($qry);		
											$i=1;$total=0;$discount=0;
											$cnt1=mysqli_num_rows($result);
											//echo $qry;
											while($row=mysqli_fetch_assoc($result))
											{
												$number="1.".$cnt;
											echo '
											<tr>';
													
												
												get_tree_request_jobwork($dbcon,$id,$row['parent_id'],$row['bom_level'],$cnt,$row['bom_id'],$number,$row['product_qty'],$row['bom_trn_id'],$row['product_type']);
													
											echo '</tr>';
										
											$cnt++;$counter_tree++;
											
											}
										
										
										
										?>
									
									</tbody>
									
									
								</table>
									
								
								 <div class="col-md-6">
										
										<div class="form-group">
										  <label class="col-md-3 control-label">Remarks </label>
												<div class="col-md-9 col-xs-11">
												<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
											</div>
										 </div> 
								</div>
								 
								</div>
							<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
							
							<a href="<?=ROOT.'po_list'?>" type="button" class="btn btn-danger">Cancel</a>
							<div class="col-md-3"></div>					
					</div>
							<!--Vendor row end-->	
					<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
					<input type='hidden' name='eid' id='eid' value='<?=$id;?>' />	
		
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
	<?php include_once($include.'footer.php');?>
      <!--footer end-->
  </section>
	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include.'include_js_file.php');?>   
	<!--<script src="<?=ROOT?>js/app/vender.js"></script>-->
	<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/jobcard_detail.js"></script>
	

<!--<script src="js/count.js"></script>-->
<script>
$(".select2").select2({
	width: '100%'
});
$("#product_id").select2({
	width: '86%'
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
function add_customer_purchase()
{
	$("#bs-example-modal-lg").modal("show");
	$("#cat_id").val('1');
}
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

if($mode=="Add"){
	echo "<script>load_salesno();</script>";
}

?>
</body>
</html>
