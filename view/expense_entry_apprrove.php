<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Expense";
	
	$id=isset($_GET['id'])?$_GET['id']:'';
	$mode="Change Status of ";
	$id=$dbcon->real_escape_string($_REQUEST['id']);
	//echo $id;
	$query="select exp.*,comp.complaint_no,u.user_name,comp.cust_id,l.l_name as expense_name,l1.l_name as customer_name from tbl_expense_detail as exp left join tbl_complaint as comp on  comp.complaint_id=exp.expense_complain left join users as u on u.user_id=exp.user_id left join tbl_ledger as l1 on l1.l_id=exp.vendorid left join tbl_ledger as l on l.l_id=exp.exp_accountid where exp.ex_id=$id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	$date=date('d-m-Y',strtotime($rel['expense_date']));
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
					  <h3> <span class="english"><?=$mode .' '.$form?></span></h3>
						
					  </header>	
							<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> 
							 Home
							</a></li>
							  <li ><a href="<?=ROOT.'expense_detail'?>"><span class="english"><?=$form?> List</span></a></li>
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
					
					</header>	
					<div class="panel-body">
						<form class="form-horizontal" role="form" id="expense_change_status" action="javascript:;" method="post" name="expense_change_status">
						
						<table class="table table-bordered">
							<tr>
								<th>Employee Name:</th>
								<td colspan="3"><?=$rel['user_name']; ?></td>
							</tr>
							<tr>
								<th>Date</th>
								<td><?=$date; ?></td>
								<th>Expense Type</th>
								<td><?=$rel['expense_name']; ?></td>
							</tr>
							<tr>
								<th>Customer</th>
								<td><?=$rel['customer_name']; ?></td>
								<th>Complain</th>
								<td><?=$rel['complaint_no']; ?></td>
							</tr>
							<tr>
								<th colspan="3" style="text-align:right">Expense Amount</th>
								<td><?=$rel['paid_amount']; ?></td>
							</tr>
							
							<tr>
								<th>Change Status</th>
								<td>
									<select class="form-control"  id="emp_status" name="emp_status">
										<option value="">--Selet Status--</option>
										<option value="1">Approved</option>
										<option value="2">Rejected</option>
									</select>
								</td>
								<th>Remark</th>
								<td>
									<textarea class="form-control" id="remark_emp" name="remark_emp"></textarea>
								</td>
							</tr>
							<tr>
								<th colspan="4">
									<img src="<?=ROOT.'view/upload/expense_img/'.$rel['c_img']; ?>" width="200" height="200" />
								</th>
							</tr>
							<tr>
								<td colspan="4">
									<div class="col-md-12 col-md-offset-2">
										<button type="submit" class="btn btn-success" id="save" name="save"><span class="english">Submit</span></button>
										
										<a href="<?=ROOT.'expense_detail'?>" type="button" class="btn btn-danger"><span class="english">Cancel</span></a><div class="col-md-3"></div>			
									</div>
								</td>
							</tr>
						</table>
						<input type="hidden" id="emp_id" name="emp_id" value="<?=$rel['emp_id']?>" />
						<input type="hidden" id="mode" name="mode" value="change_status" />
						<input type="hidden" id="ex_id" name="ex_id" value="<?=$id;?>" />
						<input type="hidden" id="amount" name="amount" value="<?=$rel['paid_amount'];?>" />
						</form>
					</div>	
					
					<div class="panel-body">
						
						<table class="table table-bordered">
							<tr>
								<th style="text-align:center;background-color:#F5F5F5" colspan="5" >
									Expense History
								</th>
							</tr>
							<tr>
								<th>#</th>
								<th>Date</th>
								<th>Status</th>
								<th>Amount</th>
								<th>Remark</th>
							</tr>
							<?php 
								$q=$dbcon->query("select * from tbl_expense_status_history where eh_ex_id='$id'"); 
								$cnt=1;
								while($row=mysqli_fetch_assoc($q))
								{
									if($row['eh_status']==0)
									{
										$status='<a class="btn btn-warning btn-xs">Pending</a>';
									}
									else if($row['eh_status']==1)
									{
										$status='<a class="btn btn-success btn-xs">Approved</a>';
									}
									else
									{
										$status='<a class="btn btn-danger btn-xs">Rejected</a>';
									}
							?>
								<tr>
									<td><?php echo $cnt; ?></td>
									<td><?php echo date("d/m/Y",strtotime($row['eh_date'])); ?></td>
									<td><?php echo $status; ?></td>
									<td><?php echo $row['eh_amount']; ?></td>
									<td><?php echo $row['eh_remark']; ?></td>
								</tr>
							<?php $cnt++; } ?>
						</table>
						
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
<script src="<?=ROOT?>js/app/employee_expense.js?<?=time()?>"></script> 
    <script src="<?=ROOT?>js/app/customer.js?<?=time()?>"></script>
 	<script src="<?=ROOT?>js/app/product_mst.js?<?=time()?>"></script>
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
echo "<script>load_state(229,'stateid',0)</script>";
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
