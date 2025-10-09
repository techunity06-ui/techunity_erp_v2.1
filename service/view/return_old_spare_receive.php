<?php 
	session_start();
	include('../include/urlfile.php');
	$incPath = $path.'include/';

	$form="Receive Old Spare Part";
	
	$id=isset($_GET['id'])?$_GET['id']:'';
	
	$id=$dbcon->real_escape_string($_REQUEST['id']);
	
	$query="select pr.branch_id,pr.s_id,pr.sc_comp_id,pr.sc_cust_id,pr.courier_name,pr.courier_no,pr.courier_del_date,pr.sc_user_id,pr.sc_date,pr.sc_product,pr.sc_qty,pr.sc_rate,pr.sc_amount,pr.sc_remark,pm.product_name,c.complaint_no,c.complaint_date,l.l_name,l.cust_mobile from tbl_complain_close_spare_part as pr inner join product_mst as pm on pr.sc_product=pm.product_id left join tbl_complaint as c on c.complaint_id=pr.sc_comp_id  inner join tbl_ledger as l on l.l_id=pr.sc_cust_id where pr.s_id='$id'";
	
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	
	$date=date('d-m-Y',strtotime($rel['sc_date']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once($incPath.'include_css_file.php');?>
</head>
<body>
  <section id="container"  >
      <?php include_once($incPath.'include_top_menu.php');?>
      <!--sidebar start-->
      <?php include_once($incPath.'left_menu.php');?>
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
							  <li ><a href="<?=ROOT.SERVICE_ROOT.'return_old_spare'?>"><span class="english"><?=$form?> List</span></a></li>
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
											
						<form class="form-horizontal" role="form" id="old_spare_part_update" action="javascript:;" method="post" name="old_spare_part_update">
						
						<table class="table table-bordered">
							<tr>
								<th>Complain No:</th>
								<td><?=$rel['complaint_no']; ?></td>
								<th>Date</th>
								<td><?=date('d-m-Y',strtotime($rel['complaint_date']))?></td>
							</tr>
							
							<tr>
								<th>Customer Name</th>
								<td><?=$rel['l_name']; ?></td>
								<th>Mobile No</th>
								<td><?=$rel['cust_mobile']; ?></td>
							</tr>
							
							<tr>
								<th colspan="3" style="text-align:right">Qty</th>
								<td><?=$rel['sc_qty']; ?></td>
							</tr>
							<tr>
								<th colspan="3"  style="text-align:right">Rate</th>
								<td><?=$rel['sc_rate']; ?></td>
							</tr>
							<tr>
								<th colspan="3"  style="text-align:right">Amount</th>
								<td><?=$rel['sc_amount']; ?></td>
							</tr>
							<tr>
								<th colspan="3"  style="text-align:right">Requested On</th>
								<td><?=date("d/m/Y",strtotime($rel['sc_date'])); ?></td>
							</tr>
							
							<tr>
								<th>Courier Type</th>
								<td>
									<select class="form-control" name="c_type1" id="c_type1" onchange="showCourierDiv1(this.value)">
										<option value="">--Select Courier Type--</option>
										<option value="1">By Hand</option>
										<option value="2">By Courier</option>
									</select>
								</td>
								<td colspan="2">
                                    <?php if($_SESSION['user_type']=='2'){ ?>
                                        <?php echo getBranchBox($dbcon, $_SESSION['branch_id'], $rel['branch_id'], false, true); ?>
                                    <?php } ?>
                                </td>
							</tr>
							
							<tr  id="c_rdiv1" style="display:none">
								<th>Courier Name:</th>
								<td><input type="text" class="form-control" name="sc_name" id="sc_name" value=""  /></td>
								<th>Courier No:</th>
								<td><input type="text" class="form-control" name="sc_no" id="sc_no" value=""  /></td>
							</tr>
							
							<tr  id="c_rdiv2" style="display:none">
								<th>Courier Date:</th>
								<td><input type="text" class="form-control default-date-picker" name="sc_date" id="sc_date" value=""  /></td>
							</tr>
							
							<tr>
								<th>Remark</th>
								<td>
									<textarea class="form-control" name="c_remark1" id="c_remark1"></textarea>
								</td>
							</tr>

							<tr>
								<td colspan="4">
									<div class="col-md-12 col-md-offset-2">
										<button type="submit" class="btn btn-success" id="save" name="save"><span class="english">Submit</span></button>
										
										<a href="<?=ROOT.SERVICE_ROOT.'return_old_spare'?>" type="button" class="btn btn-danger"><span class="english">Cancel</span></a><div class="col-md-3"></div>			
									</div>
								</td>
							</tr>
						</table>
						
						<input type="hidden" id="mode" name="mode" value="update_spare_part" />
						<input type="hidden" id="sc_id" name="sc_id" value="<?=$rel['s_id'];?>" />
	
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

	<?php include_once($incPath.'footer.php');?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($incPath.'include_js_file.php');?>   
    
	<script src="<?=ROOT?><?=SERVICE_ROOT?>js/app/complaint.js?<?=time()?>"></script>
	
<script>
$(".select2").select2({
	width: '100%'
});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
</script>
<?php 
echo "<script>show_data() </script>";
?>
</body>
</html>