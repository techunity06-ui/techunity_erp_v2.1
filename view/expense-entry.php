<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Expense";
	//Ankit Sompura 09-01-2021
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		FINANCE_EXPENSE_DETAIL_CREATE,
		FINANCE_EXPENSE_DETAIL_EDIT
	]);
	if(strpos($_SERVER[REQUEST_URI], "expense_edit")==false)
	{
		if(!in_array(FINANCE_EXPENSE_DETAIL_CREATE,$bulkAccessArray)){
       		header("Location: ".DOMAIN."permission_access");
    	}
		$mode="Add";
		$date=date('d-m-Y');
		$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
		
		$date=date('d-m-Y');
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));	
		$condition=$set_head['quot_condition'];
		$cust_id='';
		if($_SESSION['last_exp_cust_id']){
			$cust_id=$_SESSION['last_exp_cust_id'];
		}
	}
	else
	{
		if(!in_array(FINANCE_EXPENSE_DETAIL_EDIT,$bulkAccessArray)){
       		header("Location: ".DOMAIN."permission_access");
    	}
		$mode="Edit";
		$id=$dbcon->real_escape_string($_REQUEST['id']);
		//echo $id;
		$query="select * from tbl_expense_detail as expmst where expmst.ex_id='$id'";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		$date=date('d-m-Y',strtotime($rel['expense_date']));
		//echo $rel['g_total'];
		$cust_id=$rel['vendorid'];
	}
	
	 $userid=$_SESSION['user_id'];
	 $emp_id=getEmployeeIdUser($dbcon,$userid);
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
					<span class="english">  New <?=$form?></span>
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
							 	<label class="col-md-2 control-label" > <span class="english">Claim Amount *</span></label>
								<div class="col-md-3 col-xs-11">
									<input type="number"  title="Enter Amount" min="0" id="paid_amount" name="paid_amount" class="form-control" value="<?=$rel['paid_amount']?>" />
								</div>
					         </div>
							 
							 <!--<div class="form-group">
							 	<label class="col-md-2 control-label" > <span class="english">Status *</span></label>
								<div class="col-md-3 col-xs-11">
									<select class="select2"  title="Select Account" name="e_status" id="e_status">
										<option value="">--Select Status--</option>
										<option value="0" <?php if($mode=='Edit'){ if($rel['paid_status']=='0'){ echo "selected"; } } ?>>Paid</option>
										<option value="1" <?php if($mode=='Edit'){ if($rel['paid_status']=='1'){ echo "selected"; } } ?>>Unpaid</option>
									</select>
								</div>
					         </div>-->
					         <input type="hidden" id="e_status" name="e_status" value="1">
							 
							 <!--<div class="form-group">
							 	<label class="col-md-2 control-label" > <span class="english">Amount *</span></label>
								<div class="col-md-3 col-xs-11">
									<input type="number"  title="Enter Amount" min="0" id="expense_amount" name="expense_amount" onkeyup="return get_amount();" class="form-control" value="<?=$rel['g_total']?>" />
								</div>
					         </div> -->
							 
							 <div class="form-group">
							 	<label class="col-md-2 control-label" > <span class="english">Narration</span></label>
								<div class="col-md-3 col-xs-11">
									<textarea id="remark" name="remark" class="form-control" ><?php echo $rel['remark'] ?></textarea>
								</div>
					         </div>
							 
							<!-- <div class="form-group">
							 	<label class="col-md-2 control-label" > <span class="english">Formula</span></label>
								<div class="col-md-3 col-xs-11">
									<select class="form-control" name="formulaid" id="formulaid" onchange="get_amount();"  title="Select Formula">
										<?=getformula($dbcon,$rel['exp_formula']);?>
									</select>
								</div>
					         </div>
							 
							 <div class="form-group">
							 	<label class="col-md-2 control-label" > <span class="english">Paid Amount *</span></label>
								<div class="col-md-3 col-xs-11">
									<input type="number"  title="Enter Amount" min="0" id="paid_amount" name="paid_amount" readonly class="form-control" value="<?=$rel['paid_amount']?>" />
									<input type="hidden" class="form-control" name="tax_amt" id="tax_amt" />
								</div>
					         </div> -->
							 
							 <div class="form-group">
							 	<label class="col-md-2 control-label" > <span class="english">Party</span></label>
								<div class="col-md-3 col-xs-11">
									<select class="select2" name="cust_id" id="cust_id"  onChange="get_all_complain(this.value,'')" >
										<?=getcust($dbcon,$cust_id);?>	
									</select>
								</div>
								
					         </div>

							 <div class="form-group">
							 	<label class="col-md-2 control-label" > <span class="english">Complain *</span></label>
								<div class="col-md-3 col-xs-11">
									<select class="select2" name="comp_id" id="comp_id" onChange="get_cust_by_comp(this.value);" title="Choose Complain" required>
										<option value="">Select Complain</option>
										<?=get_customer_complain_expense($dbcon,$rel['expense_complain'],$mode,$cust_id);?>
									</select>
								</div>
								
					         </div>
							 
							 
							 
							<div class="form-group" id="s_hamount">
								<label class="col-md-2 control-label">Attach File </label>
								<div class="col-md-3">
									<input type="file" name="file" id="file" title="Choose Attach File" <?=($mode=='Add')?'required':''?> />
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
							<?if($mode=='Add'){?>
								<button type="submit" class="btn btn-success" id="saveprint" name="saveprint" onClick="submit_estimate()"><span class="english">Save and New</span></button> &nbsp;
							<?}?>
							<a href="<?=ROOT.'expense_detail'?>" type="button" class="btn btn-danger"><span class="english">Cancel</span></a><div class="col-md-3"></div>			</div>		
							<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
							<input type='hidden' name='eid' id='eid' value='<?=$rel['ex_id']?>' />
							<input type='hidden' name='comp_id_hid' id='comp_id_hid' value='<?php if($mode=='Edit'){ echo $rel['expense_complain']; } else { echo "0"; } ?>' />
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
<?if($_SESSION['last_exp_comp_id'] && $_SESSION['last_exp_cust_id']){?>
	get_all_complain(<?=$_SESSION['last_exp_cust_id']?>,<?=$_SESSION['last_exp_comp_id']?>)
<?}?>
<?if($mode=='Edit'){?>
	$('#cust_id').select2('readonly',true);
	$('#comp_id').select2('readonly',true);
<?}?>
</script>
</body>
</html>
