<?php 
	session_start();
	$path = '../../';
	$include1 = '../include/';
	$include = '../../include/';
	include_once($path."config/config.php");
	include_once($path."config/session.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
        
        $bulkAccessArray = canCheckPermissionAccess($dbcon, [
            FINANCE_JOURNAL_CREATE,
            FINANCE_JOURNAL_EDIT,
        ]);
		$token = md5(rand(1000,9999));
                $branch_id = $_SESSION['branch_id'];
                $_SESSION['token'] = $token;
		$form="Journal Voucher";
		$countryid='101';
		$stateid='1';
		$cityid='1';
		$company_config = getCompanyConfiguration($dbcon);
		if(strpos($_SERVER['REQUEST_URI'], "journal_entry_edit")==true)
		{
			if(!in_array(FINANCE_JOURNAL_EDIT,$bulkAccessArray)){
          header("Location: ".DOMAIN."permission_access");
      }
      $mode="Edit";
      $ledger_var = "'ledger_hid_id'";
      $amount_var = "'amount_hid_id'";
      $poid=$dbcon->real_escape_string($_REQUEST['id']);
      $query="select * from  tbl_journal where journal_id=$poid";
      $rel=mysqli_fetch_assoc($dbcon->query($query));	
      if(!$rel){
          header("Location: ".ROOT."journal_list");
      }
          
      $date='';
      if($rel['journal_date']!="1970-01-01" && $rel['journal_date']!="0000-00-00")
      {
              $date=date('d-m-Y',strtotime($rel['journal_date']));
      }
      
		}
		else
		{
      if(!in_array(FINANCE_JOURNAL_CREATE,$bulkAccessArray)){
          header("Location: ".DOMAIN."permission_access");
      }
			$mode="Add";
			$date=date('d-m-Y');
			$order_date='';
			$ledger_var = "'ledger_id'";
			$amount_var = "'amount'";
		}
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>JOURNAL VOUCHER</title>
		<?php include_once($include.'include_css_file.php');?>
		<style type="text/css">
			.currency_icon{
				color: green;
				font-size: 12px;
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
									  <li><a href="<?=ROOT.FINANCE_ROOT.'journal_list'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="journal_add" action="javascript:;" method="post" name="journal_add">
										<div class="row">
											<div class="col-md-12 respclear"  style="margin-top:10px;">
												<div class="col-md-6">
													<div class="form-group">
													  <label class="col-md-4 control-label">Journal Entry No *</label>
														<div class="col-md-6 col-xs-11">
															<input id="journal_entry_no" name="journal_entry_no" type="text" class="form-control" title="Journal Entry No" value="<?=$rel['journal_no']?>" placeholder="Journal Entry No" >
														</div>
													 </div>	
												</div>	
												<div class="col-md-6">  	
													 <div class="form-group">  	
													  <label class="col-md-4 control-label" >Journal Entry date *</label>
													  <div class="col-md-6 col-xs-11">
														<input id="journal_entry_date" autocomplete="off" name="journal_entry_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$date;?>" placeholder="Journal Entry Date">
														</div>
													 </div>	
												</div>	
											</div>
                      <div class="col-md-12"  style="margin-top:10px;">
                          <div class="col-md-6">
                          	<div class="form-group">
														<label class="col-md-4 control-label">Select Branch *</label>
														<div class="col-md-6 col-xs-11 resclear" >
															<select class="select2" name="branch_id" id="branch_id" tabindex="2" required title="Select Branch">
																<option value="">--Please Select Branch--</option>
																<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
																<?=getBranchBox_new($dbcon, $branch);?>
															</select>
														</div>
													</div>
													</div>
                          <div class="col-md-6">							
														<div class="form-group">
															<label class="col-md-4 control-label">GST Nature </label>
															<div class="col-md-6 col-xs-11">
																<?php $gst_nature = isset($rel['gst_nature']) ? $rel['gst_nature'] : '96'; ?>
																
																<select class="select2" name="gst_nature" id="gst_nature" onchange="get_data_description(this.value);" required title="Choose GST Nature">
																	<?php echo get_common_category($dbcon, 31,'GST Nature',$gst_nature); ?>
																</select>
																<a href="#" style="display: none;" onclick="return get_registered_expence_popup(94,'ledger_hid_id','amount_hid_id')" id="checkRegExpLink" >Check Register Expence</a>
																<a href="#" style="display: none;" onclick="return get_payment_gov_popup(92)" id="checkGovPayLink" >Check Payment To Gov.</a>

																<a href="#" style="display: none;" onclick="return get_debit_credit_note_popup(79)" id="checkCreditLink" >Check Credit Note</a>
																<a href="#" style="display: none;" onclick="return get_debit_credit_note_popup(80)" id="checkDebitLink" >Check Debit Note</a>
															</div>
															<div class="col-md-1">
																<a href="#" class='gst_nature_link'  data-original-title="test" rel="tooltip" data-placement="top"><i class="fa fa-info-circle fa-sm"></a></i>
															</div>	
														</div>								
													</div>
                                                </div>

                         <div class="col-md-12">												
													<div class="col-md-5">
														<div class="form-group">
														  <label class="col-md-5 control-label">Currency Converter *</label>
															<div class="col-md-4 col-xs-11">													
																<input id="currency_enable" name="currency_enable" type="checkbox" class="" title="" value="1" style="width:20%;height:25px;" onChange="currency_change();" <?php if($mode=='Edit' && $rel['currency_enable']==1){ echo "checked";  }  ?>>
															</div>
														 </div>
													</div>		
												</div>
												<div class="col-md-12">		
													<div class="col-md-5 currency_div"  style="display:none">
														<div class="form-group">
															<label class="col-md-5 control-label">Convert Currency *</label>
															<div class="col-md-7 col-xs-11">
																<select class="form-control" name="currency_id" id="currency_id" onChange="get_symbol();">
																	<?=getcurrency($dbcon,$rel['currency_id']);?>
																</select>
																
															</div>
														</div>
													</div>				
													<div class="col-md-5 currency_div" style="display:none">
														<div class="form-group">
														  <label class="col-md-5 control-label">Currency Rate *</label>
															<div class="col-md-7 col-xs-11">
																<input id="currency_rate" name="currency_rate" type="text" class="form-control valid numbersOnly" title="" value="<?=$mode=='Edit'?$rel['currency_rate']:''?>" placeholder="">
															</div>
														</div>	
													</div>
												</div>

                        <div class="col-md-12">
												<div class="col-md-1"></div>
												<div class="col-md-10">
													<table cellspacing="10" style=" border-spacing:10px;" class="display table  table-striped table12 table-bordered" id="product_list">
														<tr id="field" >
															<th width="10%" class="text-center">Type</th>
															<th width="10%" class="text-center">Amount <span class="currency_icon"></label></th>
															<th width="15%" class="text-center">Ledger</th>
															<th width="5%" class="text-center"></th>
														</tr>
														<tr id="field" >
															<td data-label="Type">
																<select class="select2" name="entry_type" id="entry_type" title="Select Entry Type">
																	<?=getbalance_type($dbcon,"")?>
																</select>
															</td>
															<td data-label="Amount">
																<input type="number"  title="Enter Amount" min="0"  id="amount" name="amount"  class="form-control numbersOnly" />
																<strong class="pl_amount" style="color:green"></strong>
															</td>
															<td data-label="Ledger">
																<div class="col-md-8">
																<select class="select2" onChange="check_pl_ledger(this.value);is_advance_payment('ledger_id','amount',2);get_ledger_details(this.value);get_bill_by_bill(this.value);"  name="ledger_id" id="ledger_id" title="Select Ledger">
																	<?=get_ledger($dbcon,"","");?>	
																</select>
																<a href="#" class="check_bill_adjustment" style="display:none" onclick="get_bill_show('yes','jv','amount','ledger_id')">Check Bill By Bill Adjustment</a>
																</div>
																<div class="col-md-4">   
			                            	<button accesskey="n" style="margin-top: 5px;" class="btn btn-round btn-info btn-xs" title="Short-Cut To Open PopUp, Shift + Alt + n " type="button" data-toggle="modal" value="R1" onclick="showledger();"><i class="fa fa-plus"></i> Add Ledger</button>
			                            	<!-- <a href="#"  data-original-title="Short-Cut To Open PopUp, Shift + Alt + n " data-toggle="tooltip" data-placement="top" ><i class="fa fa-info-circle fa-sm" style="color: black;"></a></i> -->
			                          </div>
																
																<input type='hidden' name='ledger_Tax_type' id='ledger_Tax_type' value='' />
															</td>
															
															<td data-label="">
																<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value='' />
															<input id="receipt_no_reference" name="receipt_no_reference" type="hidden" class="form-control"  value="<?=$rel['journal_no']?>" placeholder="RECEIPT NO" >
														</tr>
													</table>
												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-1"></div>
												<div class="col-md-10" id="sale_productdata"></div>
											</div>
											

											<div class="col-md-12">
												<div class="form-group">
													<label class="col-md-2 control-label">Remark:</label>
													<div class="col-md-7 col-xs-11">
														<textarea id="jv_remark" name="jv_remark" class="form-control" title="Remark" tabindex="21" ><?=$rel['jv_remark'];?></textarea>
													</div>
												</div>	
											</div>


											<div class="col-md-12">
												<center>
													<input type="hidden" name="bill_adjust_voucher_type" id="bill_adjust_voucher_type" value="<?=JV_VOUCHER?>" placeholder="Voucher Type eg. sale , purchase">
													<input type="hidden" id="edit_id_bill" value="" />
													<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
													<a href="<?=ROOT.FINANCE_ROOT.'journal_list'?>" type="button" class="btn btn-danger">Cancel</a>
												</center>
											</div>
										</div>
										<input type="hidden" name="journal_id" id="journal_id" value="<?=$rel['journal_id']?>" />
										<input type='hidden' name='receiptid' id='receiptid' value='<?=$rel['journal_id']?>' />
										<input type="hidden" id="mode" name="mode" value="<?=$mode?>" />
										<input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
										<input type="hidden" name="entry_type_id" id="entry_type_id" value="0" placeholder="0-JV,1-CR,2-DR"/>

										<!-- voucher type -->
										<input type="hidden" name="payment_voucher" id="payment_voucher" value="<?=JV_VOUCHER?>" placeholder="Voucher Type eg. sale , purchase,receipt">
										<input type="hidden" name="payment_voucher_table" id="payment_voucher_table" value="tbl_journal" placeholder="table name of sale , purchase , payment..">
										<input type="hidden" name="payment_voucher_id" id="payment_voucher_id" value="" placeholder="primary key of that inserted table">
										<input type="hidden" name="company_tds_per" id="company_tds_per" value="<?=$company_config['enable_tds_reporting']?>">
										<input type="hidden" name="cr_dr_entry_type" id="cr_dr_entry_type" value="0" placeholder="0-JV,1-CR,2-DR">
										
									</form>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php
				include_once($include1.'add_refund_advance_receipt.php'); //adedd by dhruv
				include_once($include1.'add_registered_expence.php'); //adedd by dhruv
				include_once($include1.'add_payment_to_gov.php'); //adedd by dhruv
				include_once($include1.'add_debit_credit_note.php'); //adedd by dhruv
				include_once($include1.'add_tds_advance_pyment.php'); //adedd by dhruv
				include_once($include1.'adjust_tds_reference.php'); // added by dhruv
				include_once($include1.'adjust_tcs_reference.php'); // added by dhruv
				include_once($include1.'add_billbybill_show.php');
				include_once($include1.'add_ledger.php');
				include_once($path.'administration/include/add_multi_currency.php');
				include_once($path.'administration/include/add_multi_branch.php');
				include_once($path.'administration/include/add_billbybill_opening.php');
				include_once($path.'administration/include/add_depreciation.php');
				include_once($path.'administration/include/add_bill_sundry.php');
				include_once($path.'administration/include/add_monthly_budget.php');
				include_once($path.'administration/include/add_bank_cheque.php');
			?>
		</section>

		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.FINANCE_ROOT?>js/app/journal_entry.js"></script>
		<script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/common_form_finance.js?<?=time()?>"></script>
		<script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/add_ledger_js.js?<?=time()?>"></script>
		<script src="<?=ROOT?><?=ADMINISTRATION_ROOT?>js/app/ledger.js?<?=time()?>"></script>
		<script src="<?=ROOT.ADMINISTRATION_ROOT ?>js/app/consignee.js?<?=time()?>"></script>
		<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/customer.js?<?=time()?>"></script>
		<!-- adedd by dhruv -->
		<script>
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
			/* Added By Jayesh 30-07-2021 For tab and enter key */   
	// window.onkeyup = function(e){
	// var event = e.which || e.keyCode || 0; // .which with fallback
	// 	if(event== 27)
	// 	{
	// 		history.back();	
	// 		return false;
	// 	}			   	
	// }
	/* Added By Jayesh 30-07-2021 For tab and enter key */   
			
		</script>
		<?phpecho "<script>show_data() </script>";?>
		<?if($mode=="Add")
		{
			echo "<script>get_series_no() </script>";
		}
		if($mode=="Edit")
		{
				echo "<script>show_data() </script>";
				if($rel['gst_nature'] == 94){
	    	echo "<script>
	    			showHideLink(94);
	    		</script>";
		    }
		    if($rel['gst_nature'] == 92){
		    	echo "<script>
		    			showHideLink(92);
		    		</script>";
		    }
		    if($rel['gst_nature'] == 79){
		    	echo "<script>
		    			showHideLink(79);
		    		</script>";
		    }
		    if($rel['gst_nature'] == 80){
		    	echo "<script>
		    			showHideLink(80);
		    		</script>";
		    }
		}

		echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
		echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";
		?>

		<script type="text/javascript">
    $(function () {
        $("[rel='tooltip']").tooltip();
    });
</script>
	</body>
</html>
