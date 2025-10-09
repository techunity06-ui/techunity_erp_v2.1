<?php 
	session_start();
	$path = '../../';
	$include1 = '../include/';
	$include = '../../include/';
	include_once($path."config/config.php");
	include_once($path."config/session.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
	error_reporting(E_ALL);
        $bulkAccessArray = canCheckPermissionAccess($dbcon, [
		FINANCE_CONTRA_CREATE,
		FINANCE_CONTRA_LIST,
        FINANCE_CONTRA_EDIT,
        FINANCE_CONTRA_DELETE
	]);
		$token = md5(rand(1000,9999));
		$_SESSION['token'] = $token;
                $branch_id = $_SESSION['branch_id'];
		$form="Contra Entry";
		$countryid='101';
		$stateid='1';
		$cityid='1';
		if(strpos($_SERVER['REQUEST_URI'], "contra_entry_edit")==false)
		{
                    if(!in_array(FINANCE_CONTRA_CREATE,$bulkAccessArray)){
                        header("Location: ".DOMAIN."permission_access");
                    }
			$mode="Add";
			$date=date('d-m-Y');
			$order_date='';
		}
		else
		{
                        if(!in_array(FINANCE_CONTRA_EDIT,$bulkAccessArray)){
                            header("Location: ".DOMAIN."permission_access");
                        }
			$mode="Edit";
			$poid=$dbcon->real_escape_string($_REQUEST['id']);
			$query="select * from  tbl_contra where contra_id=$poid";
			$rel=mysqli_fetch_assoc($dbcon->query($query));	
                        if(!$rel){
                            header("Location: ".ROOT."contra_list");
                        }
			$date='';
			if($rel['contra_date']!="1970-01-01" && $rel['contra_date']!="0000-00-00")
			{
				$date=date('d-m-Y',strtotime($rel['contra_date']));
			}
		}
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));

		$financial_year=get_financial_year_new($dbcon);	
		
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>CONTRA ENTRY</title>
		<?php include_once($include.'include_css_file.php');?>
		<style type="text/css">
			.currency_icon{
				color: green;
				font-size: 12px;
			}
			#main-content {
		    margin-left: 0px;
			}
		</style>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
								  <h3><?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
									  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									  <li><a href="<?=ROOT.FINANCE_ROOT.'contra_list'?>"><?=$form?> List</a></li>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">New <?=$form?></header>	
								<div class="panel-body">
									<form class="form-horizontal" role="form" id="contra_add" action="javascript:;" method="post" name="contra_add">
										<div class="row">
											<div class="col-md-12 respclear"  style="margin-top:10px;">
												<div class="col-md-4">
													<div class="form-group">
													  <label class="col-md-5 control-label">Contra Entry No </label>
														<div class="col-md-7 col-xs-11">
															<input id="contra_entry_no" name="contra_entry_no" type="text" class="form-control" title="Contra Entry No" value="<?=$rel['contra_no']?>" readonly placeholder="Contra Entry No" >
														</div>
													 </div>	
												</div>	
												<div class="col-md-4">  	
													 <div class="form-group">  	
													  <label class="col-md-5 control-label" >Contra Entry date </label>
													  <div class="col-md-7 col-xs-11">
														<input id="contra_entry_date" name="contra_entry_date" type="text" class="form-control default_date" title="Date" value="<?=$date;?>" placeholder="Contra Entry Date">
														</div>
													 </div>	
												</div>	
												<div class="col-md-4" >	                                                
	                            <label class="col-md-5 control-label">Select Branch *</label>
															<div class="col-md-7 col-xs-11 resclear" >
																<select class="select2" name="branch_id" id="branch_id" tabindex="1"  required title="Select Branch">
																	<option value="">Choose Branch</option>
																	<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
																	<?=getBranchBox_new($dbcon, $branch);?>
																</select>
															</div>
	                      </div>
											</div>
										</div>
										<div class="row">												
											<div class="col-md-4">
												<div class="form-group">
												  <label class="col-md-5 control-label">Currency Converter *</label>
													<div class="col-md-7 col-xs-11">
													
														<input id="currency_enable" name="currency_enable" type="checkbox" class="" title="" value="1" style="width:20%;height:25px;" onChange="currency_change();" <?php if($mode=='Edit' && $rel['currency_enable']==1){ echo "checked";  }  ?>>
													
													</div>
												 </div>
											</div>				
											<div class="col-md-4 currency_div"  style="display:none">
												<div class="form-group">
													<label class="col-md-5 control-label">Convert Currency *</label>
													<div class="col-md-7 col-xs-11">
														<select class="form-control" name="currency_id" id="currency_id" onChange="get_symbol();">
															<?=getcurrency($dbcon,$rel['currency_id']);?>
														</select>
														
													</div>
												</div>
											</div>				
											<div class="col-md-4 currency_div" style="display:none">
												<div class="form-group">
												  <label class="col-md-5 control-label">Currency Rate *</label>
													<div class="col-md-7 col-xs-11">
														<input id="currency_rate" name="currency_rate" type="text" class="form-control valid numbersOnly" title="" value="<?=$mode=='Edit'?$rel['currency_rate']:''?>" placeholder="">
													</div>
												</div>	
											</div>
										</div>
                                        <div class="row">
											<div class="col-md-12">
												<div class="col-md-1"></div>
												<div class="col-md-10">
													<table cellspacing="10" style=" border-spacing:10px;" class="display table  table-striped table12 table-bordered" id="product_list">
														<tr id="field" >
															<th width="10%" class="text-center">
																Type
															</th>
															<th width="15%" class="text-center">Ledger</th>
															<th width="10%" class="text-center">Amount<span class="currency_icon"></th>
															<th width="5%" class="text-center"></th>
														</tr>
														<tr id="field" >
															<td data-label="Type">
																<select class="select2" name="entry_type" id="entry_type" title="Select Entry Type">
																	<?=getbalance_type($dbcon,"")?>
																</select>
															</td>
															<td data-label="Ledger">
																<div class="col-md-8">
																	<select class="select2" name="ledger_id" id="ledger_id" title="Select Ledger">
																		<?//=get_ledger($dbcon);?>
																		<?=get_ledger_bank($dbcon,"");?>
																	</select>
																</div>
																<div class="col-md-4">
																	<button accesskey="n" style="margin-top: 5px;" class="btn btn-round btn-info btn-xs" type="button" data-toggle="modal" value="R1" onclick="showledger();"><i class="fa fa-plus"></i> Add Ledger</button>
																	<a href="#"  data-original-title="Short-Cut To Open PopUp, Shift + Alt + n " data-toggle="tooltip" data-placement="top" ><i class="fa fa-info-circle fa-sm" style="color: black;"></a></i>
																</div>
															</td>
															<td data-label="Amount">
																<input type="text"  title="Enter Amount" min="0" id="amount" name="amount"  class="form-control numbersOnly" />
															</td>
															<td data-label="">
																<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value='' />
														</tr>
													</table>
												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-1"></div>
												<div class="col-md-10" id="sale_productdata"></div>
											</div>
											<div class="col-md-12">
												<center>
													<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
													<a href="<?=ROOT.FINANCE_ROOT.'contra_list'?>" type="button" class="btn btn-danger">Cancel</a>
												</center>
											</div>
										</div>
										<input type="hidden" name="contra_id" id="contra_id" value="<?=$rel['contra_id']?>" />
										<input type="hidden" id="mode" name="mode" value="<?=$mode?>" />
										<input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
									</form>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<!-- <php// include_once('../include/add_cust.php');?> -->
		</section>
		<?php include_once($include.'include_js_file.php');
					include_once($include1.'add_ledger.php');
					include_once($path.'administration/include/add_multi_currency.php');
					include_once($path.'administration/include/add_multi_branch.php');
					include_once($path.'administration/include/add_billbybill_opening.php');
					include_once($path.'administration/include/add_depreciation.php');
					include_once($path.'administration/include/add_bill_sundry.php');
					include_once($path.'administration/include/add_monthly_budget.php');
					include_once($path.'administration/include/add_bank_cheque.php');
		?>   
		<script src="<?=ROOT.FINANCE_ROOT?>js/app/contra_entry.js"></script>
		<script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/add_ledger_js.js?<?=time()?>"></script>
		<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/ledger.js?<?=time()?>"></script>
		<script src="<?=ROOT.ADMINISTRATION_ROOT ?>js/app/consignee.js?<?=time()?>"></script>
		<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/customer.js?<?=time()?>"></script>
		<script>
			//$('#container').addClass('sidebar-closed');
			$(".select2").select2({
                            width: '100%'
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
			$('.default_date').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true,
				startDate:'<?php echo date("d-m-Y", strtotime($financial_year['financial_start_date'])) ?>',
				endDate:'<?php echo date("d-m-Y", strtotime($financial_year['financial_end_date'])) ?>',

			});

			//Start Added by Dhruv
			function currency_change()
			{
				if($('#currency_enable').is(":checked"))
				{
					$('.currency_div').show();
				}
				else
				{
					$('.currency_div').hide();
				}
			}
			//End Code By Dhruv
			
		</script>
		<?phpecho "<script>show_data() </script>";?>
		<?if($mode=="Add")
		{
			echo "<script>get_series_no() </script>";
		}

		echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
		echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";

		?>
	</body>
</html>
