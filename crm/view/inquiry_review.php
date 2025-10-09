<?php 
session_start();
include('../include/urlfile.php');
$form="Inquiry Review";	
$inquiry_id=$dbcon->real_escape_string($_REQUEST['id']);
$query = "select * from tbl_inquiry_review where inquiry_review_status=0 and inquiry_id=".$inquiry_id;
$result = $dbcon->query($query);
$row = brp_mysqli_fetch_array($result);

$wo_date='';
if($row['wo_date'] != "1970-01-01" && $row['wo_date'] != "0000-00-00"){
    $wo_date = date('d-m-Y',strtotime($row['wo_date']));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>INQUIRY REVIEW</title>
	<?php include_once($include.'include_css_file.php'); ?>
	<style>
		label{
			font-size: 15px;
		}
		.row_margin
		{
			margin-top:10px;
		}
		.btn-group-vertical>.btn.active, .btn-group-vertical>.btn:active, .btn-group-vertical>.btn:focus, .btn-group-vertical>.btn:hover, .btn-group>.btn.active, .btn-group>.btn:active, .btn-group>.btn:focus, .btn-group>.btn:hover{
			z-index:2;
			background-color: #bbdce6;
		}
		.control-label{
			font-weight: bold;
		}
		.fa-info-circle
		{
			color: blue !important;
			font-size: 16px !important;
		}
		.submit_err
		{
			color: red;
		}
	</style>
</head>
<body>
	<section id="container">
		<?php include_once($include.'include_top_menu.php'); ?>
		<!--sidebar start-->
		<?php include_once($include.'left_menu.php'); ?>
		<!--sidebar end-->
		<!--main content start-->
		<section id="main-content">
			<section class="wrapper">
				
				<div class="row">
					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3><?=$form?></h3>
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><?=$form?></li>
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
								<?=$form?>
							</header>	
							
							<div class="panel-body">
								<form role="form" id="inquiry_reviewed_add" action="javascript:;" method="post" name="inquiry_reviewed_add">
								<div class="row">

									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-6 control-label">1. Customer Address </label>
											<div class="col-md-6 col-xs-11">
												<div class="btn-group btn-group-toggle" data-toggle="buttons">
													<label class="btn btn-secondary <?php if($row['customer_address'] == 0){ echo "active";}?>">
														<input type="radio" name="customer_address" id="customer_address1" autocomplete="off" value="0" <?php if($row['customer_address'] == 0){ echo "checked";}?>  > No
													</label>
													<label class="btn btn-secondary <?php if($row['customer_address'] == 1){ echo "active";}?>" >
														<input type="radio" name="customer_address" id="customer_address2" autocomplete="off" value="1" <?php if($row['customer_address'] == 1){ echo"checked"; }?>> Yes
													</label>
												</div>
											</div>
										</div>
									</div>	

									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-6 control-label">2. Enquiry No. & Date </label>
											<div class="col-md-6 col-xs-11">
												<div class="btn-group btn-group-toggle" data-toggle="buttons">
													<label class="btn btn-secondary <?php if($row['inquiry_no_date'] == 0){ echo "active";}?>">
														<input type="radio" name="inquiry_no_date" id="inquiry_no_date1" autocomplete="off" value="0" <?php if($row['inquiry_no_date'] == 0){ echo "checked";}?>  > No
													</label>
													<label class="btn btn-secondary <?php if($row['inquiry_no_date'] == 1){ echo "active";}?>" >
														<input type="radio" name="inquiry_no_date" id="inquiry_no_date2" autocomplete="off" value="1" <?php if($row['inquiry_no_date'] == 1){ echo"checked"; }?>> Yes
													</label>
												</div>
											</div>
										</div>
									</div>

									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-6 control-label">3. Technical Specification of the items available </label>
											<div class="col-md-6 col-xs-11">
												<div class="btn-group btn-group-toggle" data-toggle="buttons">
													<label class="btn btn-secondary <?php if($row['technical_spacification'] == 0){ echo "active";}?>">
														<input type="radio" name="technical_spacification" id="technical_spacification1" autocomplete="off" value="0" <?php if($row['technical_spacification'] == 0){ echo "checked";}?>  > No
													</label>
													<label class="btn btn-secondary <?php if($row['technical_spacification'] == 1){ echo "active";}?>" >
														<input type="radio" name="technical_spacification" id="technical_spacification2" autocomplete="off" value="1" <?php if($row['technical_spacification'] == 1){ echo"checked"; }?>> Yes
													</label>
												</div>
											</div>
										</div>
									</div>

									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-6 control-label">4. Applicable API Product Specification Requirements </label>
											<div class="col-md-6 col-xs-11">
												<div class="btn-group btn-group-toggle" data-toggle="buttons">
													<label class="btn btn-secondary <?php if($row['pro_speci_req'] == 0){ echo "active";}?>">
														<input type="radio" name="pro_speci_req" id="pro_speci_req1" autocomplete="off" value="0" <?php if($row['pro_speci_req'] == 0){ echo "checked";}?>  > No
													</label>
													<label class="btn btn-secondary <?php if($row['pro_speci_req'] == 1){ echo "active";}?>" >
														<input type="radio" name="pro_speci_req" id="pro_speci_req2" autocomplete="off" value="1" <?php if($row['pro_speci_req'] == 1){ echo"checked"; }?>> Yes
													</label>
												</div>
											</div>
										</div>
									</div>

									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-6 control-label">5. Customer Drawing Enclosed </label>
											<div class="col-md-6 col-xs-11">
												<div class="btn-group btn-group-toggle" data-toggle="buttons">
													<label class="btn btn-secondary <?php if($row['cust_draw_enclose'] == 0){ echo "active";}?>">
														<input type="radio" name="cust_draw_enclose" id="cust_draw_enclose1" autocomplete="off" value="0" <?php if($row['cust_draw_enclose'] == 0){ echo "checked";}?>  > No
													</label>
													<label class="btn btn-secondary <?php if($row['cust_draw_enclose'] == 1){ echo "active";}?>" >
														<input type="radio" name="cust_draw_enclose" id="cust_draw_enclose2" autocomplete="off" value="1" <?php if($row['cust_draw_enclose'] == 1){ echo"checked"; }?>> Yes
													</label>
												</div>
											</div>
										</div>
									</div>

									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-6 control-label">6. Scope of inspection </label>
											<div class="col-md-6 col-xs-11">
												<div class="btn-group btn-group-toggle" data-toggle="buttons">
													<label class="btn btn-secondary <?php if($row['scope_inspection'] == 0){ echo "active";}?>">
														<input type="radio" name="scope_inspection" id="scope_inspection1" autocomplete="off" value="0" <?php if($row['scope_inspection'] == 0){ echo "checked";}?>  > No
													</label>
													<label class="btn btn-secondary <?php if($row['scope_inspection'] == 1){ echo "active";}?>" >
														<input type="radio" name="scope_inspection" id="scope_inspection2" autocomplete="off" value="1" <?php if($row['scope_inspection'] == 1){ echo"checked"; }?>> Yes
													</label>
												</div>
											</div>
										</div>
									</div>


									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-6 control-label">7. Delivery</label>
											<div class="col-md-6 col-xs-11">
												<div class="btn-group btn-group-toggle" data-toggle="buttons">
													<label class="btn btn-secondary <?php if($row['delivery'] == 0){ echo "active";}?>">
														<input type="radio" name="delivery" id="delivery1" autocomplete="off" value="0" <?php if($row['delivery'] == 0){ echo "checked";}?>  > No
													</label>
													<label class="btn btn-secondary <?php if($row['delivery'] == 1){ echo "active";}?>" >
														<input type="radio" name="delivery" id="delivery2" autocomplete="off" value="1" <?php if($row['delivery'] == 1){ echo"checked"; }?>> Yes
													</label>
												</div>
											</div>
										</div>
									</div>

									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-6 control-label">8. Pricing Available</label>
											<div class="col-md-6 col-xs-11">
												<div class="btn-group btn-group-toggle" data-toggle="buttons">
													<label class="btn btn-secondary <?php if($row['pricing_available'] == 0){ echo "active";}?>">
														<input type="radio" name="pricing_available" id="pricing_available1" autocomplete="off" value="0" <?php if($row['pricing_available'] == 0){ echo "checked";}?>  > No
													</label>
													<label class="btn btn-secondary <?php if($row['pricing_available'] == 1){ echo "active";}?>" >
														<input type="radio" name="pricing_available" id="pricing_available2" autocomplete="off" value="1" <?php if($row['pricing_available'] == 1){ echo"checked"; }?>> Yes
													</label>
												</div>
											</div>
										</div>
									</div>

									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-6 control-label">9. Commercial terms clear </label>
											<div class="col-md-6 col-xs-11">
												<div class="btn-group btn-group-toggle" data-toggle="buttons">
													<label class="btn btn-secondary <?php if($row['com_term_clear'] == 0){ echo "active";}?>">
														<input type="radio" name="com_term_clear" id="com_term_clear1" autocomplete="off" value="0" <?php if($row['com_term_clear'] == 0){ echo "checked";}?>  > No
													</label>
													<label class="btn btn-secondary <?php if($row['com_term_clear'] == 1){ echo "active";}?>" >
														<input type="radio" name="com_term_clear" id="com_term_clear2" autocomplete="off" value="1" <?php if($row['com_term_clear'] == 1){ echo"checked"; }?>> Yes
													</label>
												</div>
											</div>
										</div>
									</div>

									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-6 control-label">10. Earnest Money Deposit</label>
											<div class="col-md-6 col-xs-11">
												<div class="btn-group btn-group-toggle" data-toggle="buttons">
													<label class="btn btn-secondary <?php if($row['earn_money_deposit'] == 0){ echo "active";}?>">
														<input type="radio" name="earn_money_deposit" id="earn_money_deposit1" autocomplete="off" value="0" <?php if($row['earn_money_deposit'] == 0){ echo "checked";}?>  > No
													</label>
													<label class="btn btn-secondary <?php if($row['earn_money_deposit'] == 1){ echo "active";}?>" >
														<input type="radio" name="earn_money_deposit" id="earn_money_deposit2" autocomplete="off" value="1" <?php if($row['earn_money_deposit'] == 1){ echo"checked"; }?>> Yes
													</label>
												</div>
											</div>
										</div>
									</div>


									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-6 control-label">11. Bank Guarantee /D.D. / TDR </label>
											<div class="col-md-6 col-xs-11">
												<div class="btn-group btn-group-toggle" data-toggle="buttons">
													<label class="btn btn-secondary <?php if($row['bank_guarantee_dd_tdr'] == 0){ echo "active";}?>">
														<input type="radio" name="bank_guarantee_dd_tdr" id="bank_guarantee_dd_tdr1" autocomplete="off" value="0" <?php if($row['bank_guarantee_dd_tdr'] == 0){ echo "checked";}?>  > No
													</label>
													<label class="btn btn-secondary <?php if($row['bank_guarantee_dd_tdr'] == 1){ echo "active";}?>" >
														<input type="radio" name="bank_guarantee_dd_tdr" id="bank_guarantee_dd_tdr2" autocomplete="off" value="1" <?php if($row['bank_guarantee_dd_tdr'] == 1){ echo"checked"; }?>> Yes
													</label>
												</div>
											</div>
										</div>
									</div>

									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-6 control-label">12. Separate Cover for Price & Technical BID</label>
											<div class="col-md-6 col-xs-11">
												<div class="btn-group btn-group-toggle" data-toggle="buttons">
													<label class="btn btn-secondary <?php if($row['sep_cov_price_techbid'] == 0){ echo "active";}?>">
														<input type="radio" name="sep_cov_price_techbid" id="sep_cov_price_techbid1" autocomplete="off" value="0" <?php if($row['sep_cov_price_techbid'] == 0){ echo "checked";}?>  > No
													</label>
													<label class="btn btn-secondary <?php if($row['sep_cov_price_techbid'] == 1){ echo "active";}?>" >
														<input type="radio" name="sep_cov_price_techbid" id="sep_cov_price_techbid2" autocomplete="off" value="1" <?php if($row['sep_cov_price_techbid'] == 1){ echo"checked"; }?>> Yes
													</label>
												</div>
											</div>
										</div>
									</div>

									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-6 control-label">13. Delivery Due Date </label>
											<div class="col-md-6 col-xs-11">
												<div class="btn-group btn-group-toggle" data-toggle="buttons">
													<label class="btn btn-secondary <?php if($row['del_due_date'] == 0){ echo "active";}?>">
														<input type="radio" name="del_due_date" id="del_due_date1" autocomplete="off" value="0" <?php if($row['del_due_date'] == 0){ echo "checked";}?>  > No
													</label>
													<label class="btn btn-secondary <?php if($row['del_due_date'] == 1){ echo "active";}?>" >
														<input type="radio" name="del_due_date" id="del_due_date2" autocomplete="off" value="1" <?php if($row['del_due_date'] == 1){ echo"checked"; }?>> Yes
													</label>
												</div>
											</div>
										</div>
									</div>

									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-6 control-label">14. Any Other Comments </label>
											<div class="col-md-6 col-xs-11">
												<div class="btn-group btn-group-toggle" data-toggle="buttons">
													<label class="btn btn-secondary <?php if($row['any_other_comment'] == 0){ echo "active";}?>">
														<input type="radio" name="any_other_comment" id="any_other_comment1" autocomplete="off" value="0" <?php if($row['any_other_comment'] == 0){ echo "checked";}?>  > No
													</label>
													<label class="btn btn-secondary <?php if($row['any_other_comment'] == 1){ echo "active";}?>" >
														<input type="radio" name="any_other_comment" id="any_other_comment2" autocomplete="off" value="1" <?php if($row['any_other_comment'] == 1){ echo"checked"; }?>> Yes
													</label>
												</div>
											</div>
										</div>
									</div>

									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-6 control-label">Ref. WO No. </label>
											<div class="col-md-6 col-xs-11">
												<input id="ref_wo_no" name="ref_wo_no" type="text" class="form-control" title="Ref. WO No." value="<?=$row['ref_wo_no']?>" placeholder="Ref. WO No." >
											</div>
										</div>
									</div>

									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-6 control-label">WO Date </label>
											<div class="col-md-6 col-xs-11">
												<input id="wo_date" name="wo_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$wo_date?>" placeholder="WO Date">
											</div>
										</div>
									</div>

									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-6 control-label">Reviewed By </label>
											<div class="col-md-6 col-xs-11">
												<input id="reviewed_by" name="reviewed_by" type="text" class="form-control" title="Reviewed By" value="<?=$row['reviewed_by']?>" placeholder="Reviewed By" >
											</div>
										</div>
									</div>

									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-6 control-label">Approved By </label>
											<div class="col-md-6 col-xs-11">
												<input id="approved_by" name="approved_by" type="text" class="form-control" title="Approved By" value="<?=$row['approved_by']?>" placeholder="Approved By" >
											</div>
										</div>
									</div>

									<div class="col-md-12 row_margin text-center">
										<input type="hidden" name="mode" id="mode" value="add">
										<input type="hidden" name="inquiry_id" id="inquiry_id" value="<?=$inquiry_id?>">
										<input type="hidden" name="inquiry_review_id" id="inquiry_review_id" value="<?=$row['inquiry_review_id']?>">
										<button type="submit" class="btn btn-success">Submit</button>
										<a href="<?=ROOT.CRM_ROOT.'inquiry_list'?>" type="button" class="btn btn-danger">Cancel</a>
									</div>

								</div>						
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
	<script src="<?=ROOT.CRM_ROOT?>js/app/inquiry_review.js?<?=time()?>"></script> 

	<script type="text/javascript">
		var date = new Date();
         var today = new Date(date.getFullYear(), date.getMonth(), date.getDate()); //start date is today
         $('.default-date-picker').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            //startDate: today,

        });
	</script>
	<!--<script src="js/count.js"></script>-->
</body>
</html>