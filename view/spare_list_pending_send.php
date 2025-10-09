<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Send Spare Part";
	
	$id=isset($_GET['id'])?$_GET['id']:'';
	//$mode="Change Status of ";
	$id=$dbcon->real_escape_string($_REQUEST['id']);
	//echo $id;
	$query="select pr.s_id,pr.s_comp_id,pr.s_cust_id,pr.s_user_id,pr.s_date,pr.s_product,pr.s_qty,pr.s_rate,pr.s_amount,pr.s_courier_name,pr.s_courier_no,pr.s_courier_del_date,pr.s_status,pm.product_name,c.complaint_no,l.l_name,l.cust_mobile from tbl_complain_spare_part as pr inner join product_mst as pm on pr.s_product=pm.product_id left join tbl_complaint as c on c.complaint_id=pr.s_comp_id  inner join tbl_ledger as l on l.l_id=pr.s_cust_id where pr.s_id='$id'";
	
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	
	$date=date('d-m-Y',strtotime($rel['s_date']));
	//echo $rel['g_total'];
	
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
					  <h3> <span class="english"><?=$form?></span></h3>
						
					  </header>	
							<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> 
							 Home
							</a></li>
							  <li ><a href="<?=ROOT.'spare_list_pending'?>"><span class="english"><?=$form?> List</span></a></li>
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
						<h3>Spare Part : <?php echo $rel['product_name']; ?></h3>
					</header>	
					<div class="panel-body">
											
						<form class="form-horizontal" role="form" id="spare_part_update" action="javascript:;" method="post" name="spare_part_update">
						
						<table class="table table-bordered">
							<tr>
								<th>Complain No:</th>
								<td><?=$rel['complaint_no']; ?></td>
								<th>Date</th>
								<td><?=$date; ?></td>
							</tr>
							
							<tr>
								<th>Customer Name</th>
								<td><?=$rel['l_name']; ?></td>
								<th>Mobile No</th>
								<td><?=$rel['cust_mobile']; ?></td>
							</tr>
							
							<tr>
								<th colspan="3" style="text-align:right">Qty</th>
								<td><?=$rel['s_qty']; ?></td>
							</tr>
							<tr>
								<th colspan="3"  style="text-align:right">Rate</th>
								<td><?=$rel['s_rate']; ?></td>
							</tr>
							<tr>
								<th colspan="3"  style="text-align:right">Amount</th>
								<td><?=$rel['s_amount']; ?></td>
							</tr>
							<tr>
								<th colspan="3"  style="text-align:right">Requested On</th>
								<td><?=date("d/m/Y",strtotime($rel['s_date'])); ?></td>
							</tr>
							
							<tr>
								<th>Courier Type</th>
								<td>
									<select class="form-control" name="c_type" id="c_type" onchange="showCourierDiv(this.value)">
										<option value="">--Select Courier Type--</option>
										<option value="1">By Hand</option>
										<option value="2">By Courier</option>
									</select>
								</td>
								<td colspan="2"></td>
							</tr>
							
							
							<tr id="c_div1" style="display:none">
								<th>Courier Name:</th>
								<td><input type="text" class="form-control" name="c_name" id="c_name" value="" /></td>
								<th>Courier No:</th>
								<td><input type="text" class="form-control" name="c_no" id="c_no" value="" /></td>
							</tr>
							
							<tr  id="c_div2" style="display:none">
								<th>Courier Date:</th>
								<td><input type="text" class="form-control default-date-picker" name="c_date" id="c_date" value="" /></td>
								
							</tr>
							
							<tr>
								<th>Remark</th>
								<td>
									<textarea class="form-control" name="c_remark" id="c_remark"></textarea>
								</td>
							</tr>
							
							
							<tr>
								<td colspan="4">
									<div class="col-md-12 col-md-offset-2">
										<button type="submit" class="btn btn-success" id="save" name="save"><span class="english">Submit</span></button>
										
										<a href="<?=ROOT.'spare_list_pending'?>" type="button" class="btn btn-danger"><span class="english">Cancel</span></a><div class="col-md-3"></div>			
									</div>
								</td>
							</tr>
						</table>
						
						<input type="hidden" id="mode" name="mode" value="update_spare_part" />
						<input type="hidden" id="s_id" name="s_id" value="<?=$rel['s_id'];?>" />
	
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
	  
	<?php include_once('../include/add_cust.php');?>
	<?php include_once('../include/add_product.php');?>
	
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
    
	<script src="<?=ROOT?>js/app/complaint.js?<?=time()?>"></script>
	
	<script>
	
	$(".select2").select2({
			width: '100%'
		});
	$('.default-date-picker').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true
        });
/*function paymentmode(id)
{
	if(id!="1" && id!="")
	{	
		$('#cheque_dtl').val('');
		$('#cheque_data').show();
		if(id=="2"){
			$('#cheque_display').show();
		}else{
			$('#cheque_display').hide();
		}
	}
	else{
		
		$('#cheque_data').hide();
	}
		
	
}*/
function paymentmode(id)
{
	if(id==2)//for cheque generate 
		$('#save_cheque').show();
	else
		$('#save_cheque').hide();
	if(id!="1" && id!="")
	{	
		$('#cheque_dtl').val('');
		$('.cheque_data').show();
	}
	else
		$('.cheque_data').hide();
		get_chequeno($("#pur_acc_id").val(),'cheque_dtl')
				
}
</script>
<?php 
//echo "<script>load_state(229,'stateid',0)</script>";
echo "<script>show_data() </script>";
if($mode=="Add")
{
	
	//echo "<script>load_estimateno(4) </script>";
}
else if($mode=="Edit")
{
	if($rel['paymentmodeid']==1)
	{
		echo "<script>get_cash_opening_bal(".$rel['paymentmodeid'].",'max_paid_amount','tran_amounterr')</script>";
	}
	else{
		echo "<script>get_opening_bal(".$rel['acc_id'].",'max_paid_amount','tran_amounterr');</script>";
	}
}
?>
  </body>
</html>
