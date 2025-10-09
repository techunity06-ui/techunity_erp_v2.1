<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Expense Detail";
	if(strpos($_SERVER[REQUEST_URI], "expense_edit")==false) {
		$mode="Add";
		
	}
	else {
		$mode="Edit";
		$ex_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_expense_detail where ex_id=$ex_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
	}
	//echo $_SESSION['employee_id'];
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
<!--sidebar end-->
<!--main content start-->
<section id="main-content">
<section class="wrapper">			
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
					<li><a href="<?=ROOT.'customer_list'?>"><?=$form?> List</a></li>
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
			<div class="panel-body ">
				<form class="form-horizontal" role="form" id="expense_add" action="javascript:;" method="post" name="expense_add">
					<div class="row">
						<div class="col-md-10">
							
							<div class="form-group">
								<label class="col-md-3 control-label">Date *</label>
								<div class="col-md-6 col-xs-11">
									<input id="expense_date" name="expense_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?=$mode=="Add"?date('d-m-Y'): date("d-m-Y",strtotime($rel['expense_date'])); ?>" placeholder="Expense Date" tabindex="1">
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Expense * </label>
								<div class="col-md-6 col-xs-11">
									<select class="select2" name="expense_name" id="expense_name"  tabindex="2">
										<option value="">--Select Expense--</option>
										<?=get_expense($dbcon,$rel['expense_name'])?>
									</select>
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-md-3 control-label">Complain *</label>
								<div class="col-md-6 col-xs-11">
									<select class="select2" name="expense_complain" id="expense_complain"  tabindex="3">
										<option value="">--Select Complain--</option>
									<?=get_all_complain($dbcon,$rel['expense_complain'])?>
									</select>
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-md-3 control-label">Amount *</label>
								<div class="col-md-6 col-xs-11">
									<input id="expense_amount" name="expense_amount" type="text" class="form-control required" title="amount" value="<?=$rel['expense_amount'];?>" placeholder="Expense Amount"  tabindex="4">
								</div>
							</div>	
							
							
							<div class="form-group">
								<div class="checkbox">
									<label class="col-md-offset-3">
										<input type="checkbox" id="multi_company" name="multi_company" <?=($mode=="Add"?'checked':($rel['company_id']=="0"?'checked':''))?> value="1">  View in all Company
									</label>
								</div>
							</div>
							<button type="submit" class="btn btn-success"  tabindex="5">Submit</button> &nbsp;
							<a href="<?=ROOT.'expense_detail'?>" type="button" class="btn btn-danger" tabindex="6">Cancel</a><div class="col-md-3"></div>					</div>
					</div><!--Vendor row end-->	
					<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
					<input type='hidden' name='eid' id='eid' value='<?=$rel['ex_id']?>' />
					
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
<script src="<?=ROOT?>js/app/expense_detail.js?<?=time()?>"></script>

<script>
$(".select2").select2({
	width: '100%'
});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
</script>

</body>
</html>
