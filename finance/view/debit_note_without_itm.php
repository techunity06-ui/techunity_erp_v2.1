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
		$form="Debit Note Without Item";
		$countryid='101';
		$stateid='1';
		$cityid='1';
		if(strpos($_SERVER['REQUEST_URI'], "debit_note_without_itm_edit")==true)
		{
			if(!in_array(FINANCE_JOURNAL_EDIT,$bulkAccessArray)){
          header("Location: ".DOMAIN."permission_access");
      }
      $mode="Edit";
      $poid=$dbcon->real_escape_string($_REQUEST['id']);
      $query="select * from  tbl_journal where journal_id=$poid";
      $rel=mysqli_fetch_assoc($dbcon->query($query));	
      if(!$rel){
          header("Location: ".ROOT."debit_note_without_itm_list");
      }
          
      $date='';
      if($rel['journal_date']!="1970-01-01" && $rel['journal_date']!="0000-00-00")
      {
              $date=date('d-m-Y',strtotime($rel['journal_date']));
      }
      $currency_enable="";
		if($rel['currency_enable']==1){
			$currency_enable="checked";
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

		}
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));	
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>DEBIT NOTE WOTHOUT ITEM</title>
		<?php include_once($include.'include_css_file.php');?>
		<style type="text/css">
			.currency_icon{
				color: green;
				font-size: 12px;
			}
		</style>
	</head>
	<body>
		<section id="container">
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
									  <li><a href="<?=ROOT.FINANCE_ROOT.'debit_note_without_itm_list'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="debit_note_add" action="javascript:;" method="post" name="debit_note_add">
										<div class="row">
											<div class="col-md-12 respclear"  style="margin-top:10px;">
												<div class="col-md-6">
													<div class="form-group">
													  <label class="col-md-4 control-label">Debit Entry No </label>
														<div class="col-md-6 col-xs-11">
															<input id="journal_entry_no" name="journal_entry_no" type="text" class="form-control" title="Journal Entry No" value="<?=$rel['journal_no']?>" placeholder="Journal Entry No" >
														</div>
													 </div>	
												</div>	
												<div class="col-md-6">  	
													 <div class="form-group">  	
													  <label class="col-md-4 control-label" >Debit Entry date </label>
													  <div class="col-md-4 col-xs-11">
														<input id="journal_entry_date" name="journal_entry_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$date;?>" placeholder="Journal Entry Date">
														</div>
													 </div>	
												</div>	
											</div>
                                                <div class="col-md-12"  style="margin-top:10px;">
                                                    <div class="col-md-5">
														<label class="col-md-5 control-label">Select Branch *</label>
														<div class="col-md-7 col-xs-11 resclear" >
															<select class="select2" name="branch_id" id="branch_id" tabindex="2">
																<option value="">--Please Select Branch--</option>
																<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
																<?=getBranchBox_new($dbcon, $branch);?>
															</select>
														</div>
													</div>
                                                    <div class="col-md-5">							
														<div class="form-group">
															<label class="col-md-4 control-label">GST Nature *</label>
															<div class="col-md-7 col-xs-11">
																<?php $gst_nature = isset($rel['gst_nature']) ? $rel['gst_nature'] : '98'; ?>
																<select class="select2" name="gst_nature" id="gst_nature" onchange="get_data_description(this.value);showHideLink(this.value);">
																	<?php echo get_common_category($dbcon, 34,'GST Nature',$gst_nature); ?>
																</select>							
																<a href="#" style="display: none;" onclick="return get_debit_credit_note_popup(101)" id="checkCreditLink" >Check Credit Note</a>
																<a href="#" style="display: none;" onclick="return get_debit_credit_note_popup(87)" id="checkDebitLink" >Check Debit Note</a>
															</div>
															<div class="col-md-1">
																<a href="#" class='gst_nature_link'  data-original-title="" rel="tooltip" data-placement="top"><i class="fa fa-info-circle fa-sm"></a></i>
															</div>	
														</div>								
													</div>
                                                </div>

                                                <div class="col-md-12">												
													<div class="col-md-5">
														<div class="form-group">
														  <label class="col-md-5 control-label">Currency Converter *</label>
															<div class="col-md-4 col-xs-11">													
																<input id="currency_enable" name="currency_enable" type="checkbox" class="" title="" value="1" style="width:20%;height:25px;" onChange="currency_change();"  <?=$currency_enable?>>
															</div>
														 </div>
													</div>				
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
																<input id="currency_rate" name="currency_rate" type="text" class="form-control valid numbersOnly" title="Currency Rate" value="<?=$rel['currency_rate']?>" placeholder="">
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
															<th width="15%" class="text-center">Ledger</th>
															<th width="10%" class="text-center">Amount <span class="currency_icon"></span></th>
															<th width="5%" class="text-center"></th>
														</tr>
														<tr id="field" >
															<td data-label="Type">
																<select class="select2" name="entry_type" id="entry_type" title="Select Entry Type">
																	<?=getbalance_type($dbcon,"")?>
																</select>
															</td>
															<td data-label="Ledger">
																<select class="select2" name="ledger_id" id="ledger_id" title="Select Ledger">
																	<?=get_ledger($dbcon,"","");?>	
																</select>
															</td>
															<td data-label="Amount">
																<input type="number"  title="Enter Amount" min="0" id="amount" name="amount"  class="form-control numbersOnly" />
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
													<a href="<?=ROOT.FINANCE_ROOT.'debit_note_without_itm_list'?>" type="button" class="btn btn-danger">Cancel</a>
												</center>
											</div>
										</div>
										<input type="hidden" name="journal_id" id="journal_id" value="<?=$rel['journal_id']?>" />
										<input type="hidden" id="mode" name="mode" value="<?=$mode?>" />
										<input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />

										<input type="hidden" name="sales_ledger_id" id="sales_ledger_id" value="<?=SALES_ACCOUNT?>" />

										<input type="hidden" name="entry_type_id" id="entry_type_id" value="2" placeholder="0-JV,1-CR,2-DR"/>


										<!-- voucher type -->
										<input type="hidden" name="payment_voucher" id="payment_voucher" value="<?=JV_VOUCHER?>" placeholder="Voucher Type eg. sale , purchase,receipt">
										<input type="hidden" name="payment_voucher_table" id="payment_voucher_table" value="tbl_journal" placeholder="table name of sale , purchase , payment..">
										<input type="hidden" name="payment_voucher_id" id="payment_voucher_id" value="" 		placeholder="primary key of that inserted table">

										<input type="hidden" name="cr_dr_entry_type" id="cr_dr_entry_type" value="2" placeholder="0-JV,1-CR,2-DR">
									</form>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php
				include_once($include1.'add_debit_credit_note.php'); //adedd by dhruv

			?>
		</section>
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.FINANCE_ROOT?>js/app/debit_note_without_itm.js"></script>
		<script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/common_form_finance.js?<?=time()?>"></script><!-- adedd by dhruv -->
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
	window.onkeyup = function(e){
	var event = e.which || e.keyCode || 0; // .which with fallback
		if(event== 27)
		{
			history.back();	
			return false;
		}			   	
	}
	/* Added By Jayesh 30-07-2021 For tab and enter key */   
			
		</script>

		<?php echo "<script>show_data() </script>";?>
		<?php if($mode=="Add")
		{
			echo "<script>get_series_no() </script>";
		}
		if($mode=="Edit")
		{
			//echo "<script>show_data() </script>";
			if($rel['gst_nature'] == 101 || $rel['gst_nature'] == 87){
	    	echo "<script>
	    			showHideLink(".$rel['gst_nature'].");
	    		</script>";
	  	}
	  }

		?>
		<script type="text/javascript">
    $(function () {
        $("[rel='tooltip']").tooltip();
    });
</script>
	</body>
</html>
