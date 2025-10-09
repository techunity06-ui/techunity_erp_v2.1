<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Request Expense";
	
	$mode="Request";
	$id=$dbcon->real_escape_string($_REQUEST['id']);
	//echo $id;
	$query="select * from tbl_expense_detail as expmst where expmst.ex_id=$id";
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
					<span class="english">  <?=$form?></span>
					</header>	
				<div class="panel-body">
	<form class="form-horizontal" role="form" id="estimate_add" action="javascript:;" method="post" name="estimate_add">
					<div class="row">
							<div class="form-group">
							  <label class="col-md-2  control-label"><span class="english">Date*</span></label>
							  <div class="col-md-2 col-xs-11">
								<input id="expense_date" name="expense_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?=$date?>" placeholder="Expense Date">
								</div>
                             </div>	
							 
							 <div class="form-group">
							 	<label class="col-md-2 control-label" > <span class="english">Expense *</span></label>
								<div class="col-md-3 col-xs-11">
									<select class="select2"  title="Select Account" name="accountid" id="accountid">
										<?=get_all_expense($dbcon,$rel['exp_accountid']);?>
									</select>
								</div>
					         </div>
							
							 <div class="form-group">
							 	<label class="col-md-2 control-label" > <span class="english">Paid Amount *</span></label>
								<div class="col-md-3 col-xs-11">
									<input type="number"  title="Enter Amount" min="0" id="paid_amount" name="paid_amount" class="form-control" value="<?=$rel['paid_amount']?>" />
								</div>
					         </div>
							 
							 <div class="form-group">
							 	<label class="col-md-2 control-label" > <span class="english">Party</span></label>
								<div class="col-md-3 col-xs-11">
									<select class="select2" name="cust_id" id="cust_id" onChange="" >
										<?=getcust($dbcon,$rel['vendorid']);?>	
									</select>
								</div>
								
					         </div>

							 <div class="form-group">
							 	<label class="col-md-2 control-label" > <span class="english">Complain</span></label>
								<div class="col-md-3 col-xs-11">
									<select class="select2" name="comp_id" id="comp_id" onChange="" >
										<option value="">Select Complain</option>
										
									</select>
								</div>
								
					         </div>
							 
							 <div class="form-group">
							 	<label class="col-md-2 control-label" > <span class="english">Remark</span></label>
								<div class="col-md-3 col-xs-11">
									<textarea id="remark" name="remark" class="form-control" ><?=$rel['remark'];?></textarea>
								</div>
					         </div>
							 
							 <div class="form-group" id="s_hamount">
								<label class="col-md-2 control-label">Attach File </label>
								<div class="col-md-3">
									<input type="file" name="file" id="file" />
									
								</div>
								<div class="col-md-3">
									<?php 
										
										if($mode=='Edit')
										{
									?>
										<img src="<?php echo ROOT.'upload/expense_img/'.$rel['c_img']; ?>" width="150" height="150" />
									<?php } ?>
								</div>
							</div>	
					
				</div>
				
						
							<div class="col-md-12">
							<button type="submit" class="btn btn-success" id="save" name="save"><span class="english">Save</span></button>
							<button type="submit" class="btn btn-success" id="saveprint" name="saveprint" onClick="submit_estimate()"><span class="english">Save and New</span></button> &nbsp;
							<a href="<?=ROOT.'expense_detail'?>" type="button" class="btn btn-danger"><span class="english">Cancel</span></a><div class="col-md-3"></div>			</div>		
							<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
							<input type='hidden' name='eid' id='eid' value='<?=$rel['ex_id']?>' />
							<input type='hidden' name='comp_id_hid' id='comp_id_hid' value='<?php echo $rel['expense_complain'];  ?>' />
							<input type='hidden' name='save_new' id='save_new' value='' />
							<input type='hidden' name='expense_paymentid' id='expense_paymentid' value='<?=$rel['expensereceipt_id']?>' />
							<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />	
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
	  
	<?php include_once('../include/add_cust.php');?>
	<?php include_once('../include/add_product.php');?>
	
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
   <script src="<?=ROOT?>js/app/expense_entry.js?<?=time()?>"></script>
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
echo "<script>show_data() </script>";

?>
  </body>
</html>
