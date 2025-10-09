<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");

$sales_ordertrn_id = $dbcon->real_escape_string($_REQUEST['id']);

$so_query = "select sales_order_id from tbl_sales_ordertrn where sales_ordertrn_id=".$sales_ordertrn_id;
$so_res = brp_mysqli_fetch_array($dbcon->query($so_query));
$sales_order_id = $so_res['sales_order_id'];
$query = "select * from tbl_sales_order_review_data where review_status=0 and sales_ordertrn_id=".$sales_ordertrn_id;
$result = $dbcon->query($query);
$cnt = brp_mysqli_num_rows($result);
$rel = brp_mysqli_fetch_array($result);
if($cnt>0){
	$mode = 'Edit';
}else{
	$mode = 'Add';
}
$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$form="Order Review Form";
$back = 'sales_order_list';

$valve_ot = $rel['valve_ot'];
$inspection_by = $rel['inspection_by'];
$valve_orientation = $rel['valve_orientation'];
$bonnet_gaskettype = $rel['bonnet_gaskettype'];
$bonnet_joint_flange_facing = $rel['bonnet_joint_flange_facing'];
$type_wedge = $rel['type_wedge'];
$stuffing_box_surface_finish = $rel['stuffing_box_surface_finish'];
?>


<!DOCTYPE html>
	<html lang="en">
	<head>
		<title><?=$form?></title>
		<?php include_once($include.'/include_css_file.php');?>
		<style type="text/css">

		.wizard > .content {
			overflow-y: scroll;
		}
		fieldset.scheduler-border {
		    border: 1px groove #ddd !important;
		    padding: 0 1.4em 1.4em 1.4em !important;
		    margin: 0 0 1.5em 0 !important;
		    -webkit-box-shadow:  0px 0px 0px 0px #000;
		            box-shadow:  0px 0px 0px 0px #000;
		}

	    legend.scheduler-border {
	        font-size: 1.2em !important;
	        font-weight: bold !important;
	        text-align: left !important;
	        width:auto;
	        padding:0 10px;
	        border-bottom:none;
	    }
	</style>
</head>
<body>
	<section id="container" class="sidebar-closed">
		<?php include_once($include.'/include_top_menu.php');?>
		<?php include_once($include.'/left_menu.php');?>
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								<h3> <?=$form .' '.$mode?></h3>
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li ><a href="<?=ROOT.CRM_ROOT.$back?>"><?=$form?> List</a></li>
								</ul>
							</div>
						</section>
					</div>	
				</div>
				<div class="row">			
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading">
								<?=$form?>
							</header>
							<div class="panel-body">
								<form class="form-horizontal" role="form" id="order_review_add" action="javascript:;" method="post" name="order_review_add" >
									<div>
										<h3> General Requirements</h3>
										<section>
											<div class="col-md-12">
												<div class="col-lg-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Project</label>
														<div class="col-md-8">
															<input type="text" id="project" name="project" class="form-control" placeholder="Project" value="<?=$rel['project']?>" >
														</div>
													</div>
												</div>

												<div class="col-lg-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Datasheet#</label>
														<div class="col-md-8">
															<input type="text" id="datasheet" name="datasheet" class="form-control" placeholder="Datasheet#" value="<?=$rel['datasheet']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-lg-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Our Quotation Ref.</label>
														<div class="col-md-8">
															<input type="text" id="our_quotation_ref" name="our_quotation_ref" class="form-control" placeholder="Our Quotation Ref." value="<?=$rel['our_quotation_ref']?>" >
														</div>
													</div>
												</div>

												<div class="col-lg-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Legal Requirements</label>
														<div class="col-md-8">
															<input type="text" id="legal_requirements" name="legal_requirements" class="form-control" placeholder="Legal Requirements" value="<?=$rel['legal_requirements']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-lg-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Operating Temperature Range</label>
														<div class="col-md-8">
															<input type="text" id="operating_temperature" name="operating_temperature" class="form-control" placeholder="Operating Temperature Range" value="<?=$rel['operating_temperature']?>" >
														</div>
													</div>
												</div>

												<div class="col-lg-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Operating Pressure Range</label>
														<div class="col-md-8">
															<input type="text" id="operating_pressure" name="operating_pressure" class="form-control" placeholder="Operating Pressure Range" value="<?=$rel['operating_pressure']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-lg-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Fluid / Service / Application</label>
														<div class="col-md-8">
															<input type="text" id="fluid_service_application" name="fluid_service_application" class="form-control" placeholder="Fluid / Service / Application" value="<?=$rel['fluid_service_application']?>" >
														</div>
													</div>
												</div>

												<div class="col-lg-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Design / Mfg. Standard</label>
														<div class="col-md-8">
															<input type="text" id="design_mfg_standard" name="design_mfg_standard" class="form-control" placeholder="Design / Mfg. Standard" value="<?=$rel['design_mfg_standard']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-lg-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Testing Standard</label>
														<div class="col-md-8">
															<input type="text" id="testing_standard" name="testing_standard" class="form-control" placeholder="Testing Standard" value="<?=$rel['testing_standard']?>" >
														</div>
													</div>
												</div>

												<div class="col-lg-6">
													<div class="form-group">
														<label class="col-md-4 control-label">QSL #</label>
														<div class="col-md-8">
															<input type="text" id="qsl" name="qsl" class="form-control" placeholder="QSL #" value="<?=$rel['qsl']?>" >
														</div>
													</div>
												</div>
											</div>


											<div class="col-md-12">
												<div class="col-lg-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Qty.</label>
														<div class="col-md-8">
															<input type="text" id="qty" name="qty" class="form-control" placeholder="Qty." value="<?=$rel['qty']?>" >
														</div>
													</div>
												</div>

												<div class="col-lg-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Required Delivery Date</label>
														<div class="col-md-8">
															<input type="text" id="required_del_date" name="required_del_date" class="form-control default-date-picker" placeholder="Required Delivery Date" value="<?=$rel['required_del_date']?>" >
														</div>
													</div>
												</div>
											</div>

												<!-- <h2 style="text-align:center;background-color: #484848d4;color: white;">Material Of Construction</h2> -->
											<div class="col-md-12">
											<fieldset class="scheduler-border">
												<legend class="scheduler-border">Material Of Construction :</legend>
												<div class="col-md-12">
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label">Body/Bonnet/Cover</label>
															<div class="col-md-8">
																<input type="text" id="body_bonnet_cover" name="body_bonnet_cover" class="form-control" placeholder="Body/Bonnet/Cover" value="<?=$rel['body_bonnet_cover']?>" >
															</div>
														</div>
													</div>

													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label">Gate/Ball/Disc/Plug</label>
															<div class="col-md-8">
																<input type="text" id="gate_ball_disc_plug" name="gate_ball_disc_plug" class="form-control" placeholder="Gate/Ball/Disc/Plug" value="<?=$rel['gate_ball_disc_plug']?>" >
															</div>
														</div>
													</div>
												</div>


												<div class="col-md-12">
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label">Seat Ring</label>
															<div class="col-md-8">
																<input type="text" id="seat_ring" name="seat_ring" class="form-control" placeholder="Seat Ring" value="<?=$rel['seat_ring']?>" >
															</div>
														</div>
													</div>

													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label">Steam</label>
															<div class="col-md-8">
																<input type="text" id="steam" name="steam" class="form-control" placeholder="Steam" value="<?=$rel['steam']?>" >
															</div>
														</div>
													</div>
												</div>

												<div class="col-md-12">
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label">Stud Nut</label>
															<div class="col-md-8">
																<input type="text" id="stud_nut" name="stud_nut" class="form-control" placeholder="Stud Nut" value="<?=$rel['stud_nut']?>" >
															</div>
														</div>
													</div>

													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label">Back Seat Bush</label>
															<div class="col-md-8">
																<input type="text" id="back_seat_bush" name="back_seat_bush" class="form-control" placeholder="Back Seat Bush" value="<?=$rel['back_seat_bush']?>" >
															</div>
														</div>
													</div>
												</div>

												<div class="col-md-12">
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label">Gasket</label>
															<div class="col-md-8">
																<input type="text" id="gasket" name="gasket" class="form-control" placeholder="Gasket" value="<?=$rel['gasket']?>" >
															</div>
														</div>
													</div>

													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label">Packing Seals</label>
															<div class="col-md-8">
																<input type="text" id="packing_seals" name="packing_seals" class="form-control" placeholder="Packing Seals" value="<?=$rel['packing_seals']?>" >
															</div>
														</div>
													</div>
												</div>
											</fieldset>
											</div>
											<div class="col-md-12">
												<div class="form-group">
													<label class="col-md-2 control-label">End Connection</label>
													<div class="col-md-6">
														<input type="text" id="end_connection" name="end_connection" class="form-control" placeholder="End Connection" value="<?=$rel['end_connection']?>" >
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="form-group">
													<label class="col-md-2 control-label">Valve Operating Type</label>
													
													<div class="col-md-2">
														<label class="radio-inline">	
															<input type="radio" name="valve_ot" value="0" <?=($valve_ot=='0')?'checked':''?>> Handwheel 
														</label>
													</div>
													<div class="col-md-2">
														<label class="radio-inline">
															<input type="radio" name="valve_ot" value="1" <?=($valve_ot=='1')?'checked':''?>> Lever	
														</label>
													</div>
													<div class="col-md-2">
														<label class="radio-inline">
															<input type="radio" name="valve_ot" value="2" <?=($valve_ot=='2')?'checked':''?>> Gear Box
														</label>
													</div>
													<div class="col-md-2">
														<label class="radio-inline">
															<input type="radio" name="valve_ot" value="3" <?=($valve_ot=='3')?'checked':''?>> Actuator
														</label>
													</div>
												</div>
											</div>


											<div class="col-md-12">
												<div class="form-group">
													<label class="col-md-2 control-label">Inspection By</label>
													
													<div class="col-md-2">
														<label class="radio-inline">
															<input type="radio" name="inspection_by" value="0" <?=($inspection_by=='0')?'checked':''?>> Internal 
														</label>
													</div>
													<div class="col-md-2">
														<label class="radio-inline">
															<input type="radio" name="inspection_by" value="1" <?=($inspection_by=='1')?'checked':''?> > Client
														</label>
													</div>
													<div class="col-md-2">
														<label class="radio-inline">
															<input type="radio" name="inspection_by" value="2" <?=($inspection_by=='2')?'checked':''?>> TPI
														</label>
													</div>
													<div class="col-md-2">
														<label class="radio-inline">
															<input type="radio" name="inspection_by" value="3" <?=($inspection_by=='3')?'checked':''?>>
														</label> 
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Scope Of Inspection</label>
														<div class="col-md-8">
															<input type="text" id="scope_of_inspaction" name="scope_of_inspaction" class="form-control" placeholder="Scope Of Inspection" value="<?=$rel['scope_of_inspaction']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Applicable NDE</label>
														<div class="col-md-8">
															<input type="text" id="applicable_nde" name="applicable_nde" class="form-control" placeholder="Applicable NDE" value="<?=$rel['applicable_nde']?>" >
														</div>
													</div>
												</div>
											</div>	

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">After Sales Service Req.</label>
														<div class="col-md-8">
															<input type="text" id="af_sales_service" name="af_sales_service" class="form-control" placeholder="After Sales Service Req." value="<?=$rel['af_sales_service']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Coating / Painting Req.</label>
														<div class="col-md-8">
															<input type="text" id="coating_painting_req" name="coating_painting_req" class="form-control" placeholder="Coating / Painting Req." value="<?=$rel['coating_painting_req']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Packing Req.</label>
														<div class="col-md-8">
															<input type="text" id="packing_req" name="packing_req" class="form-control" placeholder="Packing Req." value="<?=$rel['packing_req']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Making On Product</label>
														<div class="col-md-8">
															<input type="text" id="making_product" name="making_product" class="form-control" placeholder="Making On Product" value="<?=$rel['making_product']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Making On Packing</label>
														<div class="col-md-8">
															<input type="text" id="making_packing" name="making_packing" class="form-control" placeholder="Making On Packing" value="<?=$rel['making_packing']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">API Monogram Making</label>
														<div class="col-md-8">
															<input type="text" id="api_monogram_making" name="api_monogram_making" class="form-control" placeholder="API Monogram Making" value="<?=$rel['api_monogram_making']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Delivery Due Date</label>
														<div class="col-md-8">
															<input type="text" id="delivery_due_date" name="delivery_due_date" class="form-control default-date-picker" placeholder="Delivery Due Date" value="<?=$rel['delivery_due_date']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Customer Contact Detail</label>
														<div class="col-md-8">
															<input type="text" id="customer_contact_detail" name="customer_contact_detail" class="form-control" placeholder="Customer Contact Detail" value="<?=$rel['customer_contact_detail']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Delivery Location</label>
														<div class="col-md-8">
															<input type="text" id="delivery_location" name="delivery_location" class="form-control" placeholder="Delivery Location" value="<?=$rel['delivery_location']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Documents To be Submit</label>
														<div class="col-md-8">
															<input type="text" id="documents_submit" name="documents_submit" class="form-control" placeholder="Documents To be Submit" value="<?=$rel['documents_submit']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Payment Terms</label>
														<div class="col-md-8">
															<input type="text" id="payment_terms" name="payment_terms" class="form-control" placeholder="Payment Terms" value="<?=$rel['payment_terms']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Insurance</label>
														<div class="col-md-8">
															<input type="text" id="insurance" name="insurance" class="form-control" placeholder="Insurance" value="<?=$rel['insurance']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Freight</label>
														<div class="col-md-8">
															<input type="text" id="freight" name="freight" class="form-control" placeholder="Freight" value="<?=$rel['freight']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="form-group">
													<label class="col-md-2 control-label">Remarks</label>
													<div class="col-md-6">
														<textarea class="form-control" name="remark" id="remark" placeholder="Remarks"><?=$rel['remark']?></textarea>
													</div>
												</div>
											</div>
										</section>
										<h3> API 6D Specific Requirements</h3>
										<section>
											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Bore Type & Bore Size</label>
														<div class="col-md-8">
															<input type="text" id="bore_type_size" name="bore_type_size" class="form-control" placeholder="Bore Type & Bore Size" value="<?=$rel['bore_type_size']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Face - Face & End - End Dimension</label>
														<div class="col-md-8">
															<input type="text" id="face_end_dimension" name="face_end_dimension" class="form-control" placeholder="Face - Face & End - End Dimension" value="<?=$rel['face_end_dimension']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Intermediate Design Pressure & Temp</label>
														<div class="col-md-8">
															<input type="text" id="intermediate_design_pressure" name="intermediate_design_pressure" class="form-control" placeholder="Intermediate Design Pressure & Temp" value="<?=$rel['intermediate_design_pressure']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Service Compatibillity</label>
														<div class="col-md-8">
															<input type="text" id="service_compatibillity" name="service_compatibillity" class="form-control" placeholder="Service Compatibillity" value="<?=$rel['service_compatibillity']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Valve Oreintation</label>
														<div class="col-md-8">
															<input type="text" id="valve_orentation" name="valve_orentation" class="form-control" placeholder="Valve Oreintation" value="<?=$rel['valve_orentation']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Pressure Balance Hole</label>
														<div class="col-md-8">
															<input type="text" id="pressure_balance_hole" name="pressure_balance_hole" class="form-control" placeholder="Pressure Balance Hole" value="<?=$rel['pressure_balance_hole']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">End Connectors Type</label>
														<div class="col-md-8">
															<input type="text" id="end_connectors_type" name="end_connectors_type" class="form-control" placeholder="End Connectors Type" value="<?=$rel['end_connectors_type']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">External Loads</label>
														<div class="col-md-8">
															<input type="text" id="external_loads" name="external_loads" class="form-control" placeholder="External Loads" value="<?=$rel['external_loads']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<fieldset class="scheduler-border">
													<legend class="scheduler-border">Valve Operational Data to be submit to customer :</legend>
													<div class="col-md-12">
														<div class="col-md-6">
															<div class="form-group">
																<label class="col-md-4 control-label">Flow coefficient Cv or Kv</label>
																<div class="col-md-8">
																	<input type="text" id="flow_coefficient_cvkv" name="flow_coefficient_cvkv" class="form-control" placeholder="Flow coefficient Cv or Kv" value="<?=$rel['flow_coefficient_cvkv']?>" >
																</div>
															</div>
														</div>

														<div class="col-md-6">
															<div class="form-group">
																<label class="col-md-4 control-label">Valve top works dimentions.</label>
																<div class="col-md-8">
																	<input type="text" id="valve_topwork_diamention" name="valve_topwork_diamention" class="form-control" placeholder="Valve top works dimentions." value="<?=$rel['valve_topwork_diamention']?>" >
																</div>
															</div>
														</div>
													</div>


													<div class="col-md-12">
														<div class="col-md-6">
															<div class="form-group">
																<label class="col-md-4 control-label">Break-to-open torque or thrust (BTO)</label>
																<div class="col-md-8">
																	<input type="text" id="bto" name="bto" class="form-control" placeholder="Break-to-open torque or thrust (BTO)" value="<?=$rel['bto']?>" >
																</div>
															</div>
														</div>

														<div class="col-md-6">
															<div class="form-group">
																<label class="col-md-4 control-label">Break-to-close toque or thrust (BTC).</label>
																<div class="col-md-8">
																	<input type="text" id="btc" name="btc" class="form-control" placeholder="Break-to-close toque or thrust (BTC)." value="<?=$rel['btc']?>" >
																</div>
															</div>
														</div>
													</div>


													<div class="col-md-12">
														<div class="col-md-6">
															<div class="form-group">
																<label class="col-md-4 control-label">Run-to-open torque or thrust (RTO).</label>
																<div class="col-md-8">
																	<input type="text" id="rto" name="rto" class="form-control" placeholder="Run-to-open torque or thrust (RTO)." value="<?=$rel['rto']?>" >
																</div>
															</div>
														</div>

														<div class="col-md-6">
															<div class="form-group">
																<label class="col-md-4 control-label">Run-to-close (reseat) torque or thrust (RTC)</label>
																<div class="col-md-8">
																	<input type="text" id="rtc" name="rtc" class="form-control" placeholder="Run-to-close (reseat) torque or thrust (RTC)" value="<?=$rel['rtc']?>" >
																</div>
															</div>
														</div>
													</div>

													<div class="col-md-12">
														<div class="col-md-6">
															<div class="form-group">
																<label class="col-md-4 control-label">End-to-open torque or thrust (ETO)</label>
																<div class="col-md-8">
																	<input type="text" id="eto" name="eto" class="form-control" placeholder="End-to-open torque or thrust (ETO)" value="<?=$rel['eto']?>" >
																</div>
															</div>
														</div>

														<div class="col-md-6">
															<div class="form-group">
																<label class="col-md-4 control-label">End-to-close (reseat) torque or thrust (ETC)</label>
																<div class="col-md-8">
																	<input type="text" id="etc" name="etc" class="form-control" placeholder="End-to-close (reseat) torque or thrust (ETC)" value="<?=$rel['etc']?>" >
																</div>
															</div>
														</div>
													</div>

													<div class="col-md-12">
														<div class="col-md-6">
															<div class="form-group">
																<label class="col-md-4 control-label">
																	Valve Drive train MAST
																</label>
																<div class="col-md-8">
																	<input type="text" id="valve_drive_train_mast" name="valve_drive_train_mast" class="form-control" placeholder="Valve Drive train MAST" value="<?=$rel['valve_drive_train_mast']?>" >
																</div>
															</div>		
														</div>
													</div>

													<div class="col-md-12">
														<fieldset class="scheduler-border">
															<legend class="scheduler-border">Valve Characteristics</legend>
															<div class="col-md-12">
																<!-- <div class="col-md-6"> -->
																	<div class="form-group">
																		<label class="col-md-4 control-label">
																			Length and direction of stroke to open and close for linear valves.
																		</label>
																		<div class="col-md-8">
																			<input type="text" id="length_direction_stroke_oc_linear_valve" name="length_direction_stroke_oc_linear_valve" class="form-control" placeholder="Length and direction of stroke to open and close for linear valves." value="<?=$rel['length_direction_stroke_oc_linear_valve']?>" >
																		</div>
																	</div>	
																<!-- </div> -->
															</div>
															<div class="col-md-12">
																<div class="form-group">
																	<label class="col-md-4 control-label">
																		Angle and direction of rotation for part-turn or check valves. 
																	</label>
																	<div class="col-md-8">
																		<input type="text" id="angle_rotation_partturn_checkvalve" name="angle_rotation_partturn_checkvalve" class="form-control" placeholder="Angle and direction of rotation for part-turn or check valves." value="<?=$rel['angle_rotation_partturn_checkvalve']?>" >
																	</div>
																</div>
															</div>


															<div class="col-md-12">
																<div class="form-group">
																	<label class="col-md-4 control-label">
																		Direction of Rotation and number of turns for multi-turn valves. 
																	</label>
																	<div class="col-md-8">
																		<input type="text" id="direction_rotation_number_multiturn_valve" name="direction_rotation_number_multiturn_valve" class="form-control" placeholder="Direction of Rotation and number of turns for multi-turn valves." value="<?=$rel['direction_rotation_number_multiturn_valve']?>" >
																	</div>
																</div>
															</div>
															<!-- </div> -->
														</fieldset>
													</div>

													<div class="col-md-12">
														<div class="col-md-6">
															<div class="form-group">
																<label class="col-md-4 control-label">Thrust necessary to enable the valve to maintain position</label>
																<div class="col-md-8">
																	<input type="text" id="enable_valve_maintain_position" name="enable_valve_maintain_position" class="form-control" placeholder="Thrust necessary to enable the valve to maintain position" value="<?=$rel['enable_valve_maintain_position']?>" >
																</div>
															</div>
														</div>

														<div class="col-md-6">
															<div class="form-group">
																<label class="col-md-4 control-label">Valve breakaway angle or breakaway percent of stroke</label>
																<div class="col-md-8">
																	<input type="text" id="breakaway_anglepercent_stroke" name="breakaway_anglepercent_stroke" class="form-control" placeholder="Valve breakaway angle or breakaway percent of stroke" value="<?=$rel['breakaway_anglepercent_stroke']?>" >
																</div>
															</div>
														</div>
													</div>
													
													<div class="col-md-12">
														<div class="col-md-6">
															<div class="form-group">
																<label class="col-md-4 control-label">Number of turns for manually operated valves</label>
																<div class="col-md-8">
																	<input type="text" id="num_turns_manualy_opevalve" name="num_turns_manualy_opevalve" class="form-control" placeholder="Number of turns for manually operated valves" value="<?=$rel['num_turns_manualy_opevalve']?>" >
																</div>
															</div>
														</div>
													</div>
												</fieldset>
											</div>

											
											<div class="col-md-12">
												<div class="form-group">
													<label class="col-md-4 control-label">Flange Bolting for Studded-outlet End Connectors</label>
													<div class="col-md-8">
														<input type="text" id="flange_bolting_studded_outlet_endconnector" name="flange_bolting_studded_outlet_endconnector" class="form-control" placeholder="Flange Bolting for Studded-outlet End Connectors" value="<?=$rel['flange_bolting_studded_outlet_endconnector']?>" >
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="form-group">
													<label class="col-md-4 control-label">Chemical Composition of pressure-containing & controlling materials</label>
													<div class="col-md-8">
														<input type="text" id="chemcomp_prescontai_controlling_material" name="chemcomp_prescontai_controlling_material" class="form-control" placeholder="Chemical Composition of pressure-containing & controlling materials" value="<?=$rel['chemcomp_prescontai_controlling_material']?>" >
													</div>
												</div>
											</div>
											

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Valve Seat Functionality</label>
														<div class="col-md-8">
															<input type="text" id="valve_seat_functionality" name="valve_seat_functionality" class="form-control" placeholder="Valve Seat Functionality" value="<?=$rel['valve_seat_functionality']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Extended Steam and Shaft Assemblies</label>
														<div class="col-md-8">
															<input type="text" id="extended_steam_shaft_assemblies" name="extended_steam_shaft_assemblies" class="form-control" placeholder="Extended Steam and Shaft Assemblies" value="<?=$rel['extended_steam_shaft_assemblies']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Boulting For Sour Service</label>
														<div class="col-md-8">
															<input type="text" id="boulting_sour_service" name="boulting_sour_service" class="form-control" placeholder="Boulting For Sour Service" value="<?=$rel['boulting_sour_service']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Locking Device</label>
														<div class="col-md-8">
															<input type="text" id="locking_device" name="locking_device" class="form-control" placeholder="Locking Device" value="<?=$rel['locking_device']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Position Indicator</label>
														<div class="col-md-8">
															<input type="text" id="position_indicator" name="position_indicator" class="form-control" placeholder="Position Indicator" value="<?=$rel['position_indicator']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Drain</label>
														<div class="col-md-8">
															<input type="text" id="drain" name="drain" class="form-control" placeholder="Drain" value="<?=$rel['drain']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Vent</label>
														<div class="col-md-8">
															<input type="text" id="vent" name="vent" class="form-control" placeholder="Vent" value="<?=$rel['vent']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Drain /Vent Lines & Pressure Of lines</label>
														<div class="col-md-8">
															<input type="text" id="drain_pressure_ventlines" name="drain_pressure_ventlines" class="form-control" placeholder="Drain /Vent Lines & Pressure Of lines" value="<?=$rel['drain_pressure_ventlines']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Sealant injection & Pressure of lines</label>
														<div class="col-md-8">
															<input type="text" id="sealant_injection" name="sealant_injection" class="form-control" placeholder="Sealant injection & Pressure of lines" value="<?=$rel['sealant_injection']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Drain /Vent, and injection Valves</label>
														<div class="col-md-8">
															<input type="text" id="drain_vent_injection_valves" name="drain_vent_injection_valves" class="form-control" placeholder="Drain /Vent, and injection Valves" value="<?=$rel['drain_vent_injection_valves']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Paggabillity</label>
														<div class="col-md-8">
															<input type="text" id="paggabillity" name="paggabillity" class="form-control" placeholder="Paggabillity" value="<?=$rel['paggabillity']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Welding Overlay Iron Dilution</label>
														<div class="col-md-8">
															<input type="text" id="welding_overlay_iron_dilution" name="welding_overlay_iron_dilution" class="form-control" placeholder="Welding Overlay Iron Dilution" value="<?=$rel['welding_overlay_iron_dilution']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Weld Repair of Forgings and Plate Material</label>
														<div class="col-md-8">
															<input type="text" id="weld_repair_forgings" name="weld_repair_forgings" class="form-control" placeholder="Weld Repair of Forgings" value="<?=$rel['weld_repair_forgings']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Pressure Bounndary Bolting--Hardness Testing</label>
														<div class="col-md-8">
															<input type="text" id="pressure_boundary_bolting_hardness_testing" name="pressure_boundary_bolting_hardness_testing" class="form-control" placeholder="Pressure Bounndary Bolting--Hardness Testing" value="<?=$rel['pressure_boundary_bolting_hardness_testing']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">In-service / Field Testing</label>
														<div class="col-md-8">
															<input type="text" id="inservice_field_testing" name="inservice_field_testing" class="form-control" placeholder="In-service / Field Testing" value="<?=$rel['inservice_field_testing']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Anti-static Device Test</label>
														<div class="col-md-8">
															<input type="text" id="anti_static_device_test" name="anti_static_device_test" class="form-control" placeholder="Anti-static Device Test" value="<?=$rel['anti_static_device_test']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Torque Test</label>
														<div class="col-md-8">
															<input type="text" id="torque_test" name="torque_test" class="form-control" placeholder="Torque Test" value="<?=$rel['torque_test']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Fire Safe Test</label>
														<div class="col-md-8">
															<input type="text" id="fire_safe_test" name="fire_safe_test" class="form-control" placeholder="Fire Safe Test" value="<?=$rel['fire_safe_test']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Drive Train Strength Test</label>
														<div class="col-md-8">
															<input type="text" id="drive_train_strength_test" name="drive_train_strength_test" class="form-control" placeholder="Drive Train Strength Test" value="<?=$rel['drive_train_strength_test']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Supplementary Test</label>
														<div class="col-md-8">
															<input type="text" id="supplementry_test" name="supplementry_test" class="form-control" placeholder="Supplementary Test" value="<?=$rel['supplementry_test']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Cavity Relief Test</label>
														<div class="col-md-8">
															<input type="text" id="cavity_relief_test" name="cavity_relief_test" class="form-control" placeholder="Cavity Relief Test" value="<?=$rel['cavity_relief_test']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Double Block & Bleed (DBB) valves Test</label>
														<div class="col-md-8">
															<input type="text" id="dbb_valve_test" name="dbb_valve_test" class="form-control" placeholder="Double Block & Bleed (DBB) valves Test" value="<?=$rel['dbb_valve_test']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Double Isolation And Bleed DIB-1 (Both Seats Bidirectional) Test</label>
														<div class="col-md-8">
															<input type="text" id="dib1_test" name="dib1_test" class="form-control" placeholder="Double Isolation And Bleed DIB-1 (Both Seats Bidirectional) Test" value="<?=$rel['dib1_test']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Double Isolation And Bleed DIB-2 (One Undirectional and One Bidirectional Seat) Test</label>
														<div class="col-md-8">
															<input type="text" id="dib2_seat_test" name="dib2_seat_test" class="form-control" placeholder="Double Isolation And Bleed DIB-2 (One Undirectional and One Bidirectional Seat) Test" value="<?=$rel['dib2_seat_test']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Operations Testing-valves Required For Double Isolation And Bleed (DIB-1 or DIB-2) Test</label>
														<div class="col-md-8">
															<input type="text" id="dib1_dib2_test_valves" name="dib1_dib2_test_valves" class="form-control" placeholder="Operations Testing-valves Required For Double Isolation And Bleed (DIB-1 or DIB-2) Test" value="<?=$rel['dib1_dib2_test_valves']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Hardness Test</label>
														<div class="col-md-8">
															<input type="text" id="hardness_test" name="hardness_test" class="form-control" placeholder="Hardness Test" value="<?=$rel['hardness_test']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Charpy Impact Test</label>
														<div class="col-md-8">
															<input type="text" id="charpy_impact_test" name="charpy_impact_test" class="form-control" placeholder="Charpy Impact Test" value="<?=$rel['charpy_impact_test']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">HIC Test</label>
														<div class="col-md-8">
															<input type="text" id="hic_test" name="hic_test" class="form-control" placeholder="HIC Test" value="<?=$rel['hic_test']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">High Pressure Gas Test (Shell & Seat)</label>
														<div class="col-md-8">
															<input type="text" id="high_pressure_gas_test" name="high_pressure_gas_test" class="form-control" placeholder="High Pressure Gas Test (Shell & Seat)" value="<?=$rel['high_pressure_gas_test']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Fugitive Emissions Test</label>
														<div class="col-md-8">
															<input type="text" id="fugitive_emission_test" name="fugitive_emission_test" class="form-control" placeholder="Fugitive Emissions Test" value="<?=$rel['fugitive_emission_test']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Gauge / Drift Test</label>
														<div class="col-md-8">
															<input type="text" id="gauge_drift_test" name="gauge_drift_test" class="form-control" placeholder="Gauge / Drift Test" value="<?=$rel['gauge_drift_test']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Pressure Testing Valves with Hydrostatic End Load</label>
														<div class="col-md-8">
															<input type="text" id="pressure_testing_valve_hydrostatic" name="pressure_testing_valve_hydrostatic" class="form-control" placeholder="Pressure Testing Valves with Hydrostatic End Load" value="<?=$rel['pressure_testing_valve_hydrostatic']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Special Flanges or Mechanical joints</label>
														<div class="col-md-8">
															<input type="text" id="special_flanges_mechanical_joints" name="special_flanges_mechanical_joints" class="form-control" placeholder="Special Flanges or Mechanical joints" value="<?=$rel['special_flanges_mechanical_joints']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Third Party Witness</label>
														<div class="col-md-8">
															<input type="text" id="thirdparty_witness" name="thirdparty_witness" class="form-control" placeholder="Third Party Witness" value="<?=$rel['thirdparty_witness']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Hydro Shell Testing Of one-piece Bodies in non-assembled condition</label>
														<div class="col-md-8">
															<input type="text" id="hydroshell_nonassembled_cond" name="hydroshell_nonassembled_cond" class="form-control" placeholder="Hydro Shell Testing Of one-piece Bodies in non-assembled condition" value="<?=$rel['hydroshell_nonassembled_cond']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Corrosion Protection Measures for long term Storage or Unusual / harsh condition</label>
														<div class="col-md-8">
															<input type="text" id="corrosion_protection_measures_longterm_storage" name="corrosion_protection_measures_longterm_storage" class="form-control" placeholder="Corrosion Protection Measures for long term Storage or Unusual / harsh condition" value="<?=$rel['corrosion_protection_measures_longterm_storage']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">External Coating or Painting of corrosion-resistant valves</label>
														<div class="col-md-8">
															<input type="text" id="external_coating_painting_valves" name="external_coating_painting_valves" class="form-control" placeholder="External Coating or Painting of corrosion-resistant valves" value="<?=$rel['external_coating_painting_valves']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Corrosion - resistant Metalic Surfaces</label>
														<div class="col-md-8">
															<input type="text" id="corrosion_resistant_metalic_surfaces" name="corrosion_resistant_metalic_surfaces" class="form-control" placeholder="Corrosion - resistant Metalic Surfaces" value="<?=$rel['corrosion_resistant_metalic_surfaces']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Disassembly / Maintenance Tools Provision</label>
														<div class="col-md-8">
															<input type="text" id="disassembly_maintainance_tool" name="disassembly_maintainance_tool" class="form-control" placeholder="Disassembly / Maintenance Tools Provision" value="<?=$rel['disassembly_maintainance_tool']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Support Rib or Legs</label>
														<div class="col-md-8">
															<input type="text" id="support_rib_legs" name="support_rib_legs" class="form-control" placeholder="Support Rib or Legs" value="<?=$rel['support_rib_legs']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Valve Lifting</label>
														<div class="col-md-8">
															<input type="text" id="valve_lifting" name="valve_lifting" class="form-control" placeholder="Valve Lifting" value="<?=$rel['valve_lifting']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Use of Assembly Lubricant</label>
														<div class="col-md-8">
															<input type="text" id="use_assembly_lubricant" name="use_assembly_lubricant" class="form-control" placeholder="Use of Assembly Lubricant" value="<?=$rel['use_assembly_lubricant']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Additional Requirements</label>
														<div class="col-md-8">
															<input type="text" id="additional_requirements" name="additional_requirements" class="form-control" placeholder="Additional Requirements" value="<?=$rel['additional_requirements']?>" >
														</div>
													</div>
												</div>
											</div>
										</section>
										<h3>API 600 Specific Requirements</h3>
										<section>
											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Auxilliary Connection & Openings</label>
														<div class="col-md-8">
															<input type="text" id="auxilliary_connope" name="auxilliary_connope" class="form-control" placeholder="Auxilliary Connection & Openings" value="<?=$rel['auxilliary_connope']?>" >
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Valve Orientation / Mount Location</label>
														<div class="col-md-3">
															<label class="radio-inline">
																<input type="radio" name="valve_orientation" value="0" <?=($valve_orientation=='0')?'checked':''?>> Horizontal
															</label>
														</div>

														<div class="col-md-3">
															<label class="radio-inline">
																<input type="radio" name="valve_orientation" value="1" <?=($valve_orientation=='1')?'checked':''?>> Vertical
															</label>
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Hard Facing of Body or Wedge Guides</label>
														<div class="col-md-8">
															<input type="text" id="hard_facing_body_wedge_guides" name="hard_facing_body_wedge_guides" class="form-control" placeholder="Hard Facing of Body or Wedge Guides" value="<?=$rel['hard_facing_body_wedge_guides']?>" >
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="form-group">
													<label class="col-md-2 control-label">Bonnet GasketType </label>
													<div class="col-md-2">
														<label class="radio-inline">
															<input type="radio" name="bonnet_gaskettype" value="0" <?=($bonnet_gaskettype=='0')?'checked':''?>> Solid Metal 
														</label>
													</div>

													<div class="col-md-3">
														<label class="radio-inline">
															<input type="radio" name="bonnet_gaskettype" value="1" <?=($bonnet_gaskettype=='1')?'checked':''?>> SPW SS316 Metal Gasket with Graphite Filter & CS Inner Ring
														</label>
													</div>

													<div class="col-md-2">
														<label class="radio-inline">
															<input type="radio" name="bonnet_gaskettype" value="2" <?=($bonnet_gaskettype=='2')?'checked':''?>> Metal Ring Joint
														</label>  
													</div>

													<div class="col-md-3">
														<label class="radio-inline">
															<input type="radio" name="bonnet_gaskettype" value="3" <?=($bonnet_gaskettype=='3')?'checked':''?>> SPW Metal Gasket with Filter
														</label>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="form-group">
													<label class="col-md-2 control-label">Body - Bonnet Joint Flange Facing </label>
													<div class="col-md-2">
														<label class="radio-inline">
															<input type="radio" name="bonnet_joint_flange_facing" value="0" <?=($bonnet_joint_flange_facing=='0')?'checked':''?>> Flat Face
														</label> 
													</div>

													<div class="col-md-2">
														<label class="radio-inline">
															<input type="radio" name="bonnet_joint_flange_facing" value="1" <?=($bonnet_joint_flange_facing=='1')?'checked':''?>> Raise Face
														</label>
													</div>

													<div class="col-md-2">
														<label class="radio-inline">
															<input type="radio" name="bonnet_joint_flange_facing" value="2" <?=($bonnet_joint_flange_facing=='2')?'checked':''?>> Tongue & Groove  
														</label>
													</div>

													<div class="col-md-2">
														<label class="radio-inline">
															<input type="radio" name="bonnet_joint_flange_facing" value="3" <?=($bonnet_joint_flange_facing=='3')?'checked':''?>> Male - Female
														</label>
													</div>

													<div class="col-md-2">
														<label class="radio-inline">
															<input type="radio" name="bonnet_joint_flange_facing" value="4" <?=($bonnet_joint_flange_facing=='4')?'checked':''?>> Ring Joint
														</label>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Tapped Openings</label>
														<div class="col-md-8">
															<input type="text" id="tapped_openings" name="tapped_openings" class="form-control" placeholder="Tapped Openings" value="<?=$rel['tapped_openings']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="form-group">
													<label class="col-md-2 control-label">Type Of Wedge</label>
													<div class="col-md-2">
														<label class="radio-inline">
															<input type="radio" name="type_wedge" value="0" <?=($type_wedge=='0')?'checked':''?>> Solid
														</label>  
													</div>

													<div class="col-md-2">
														<label class="radio-inline">
															<input type="radio" name="type_wedge" value="1" <?=($type_wedge=='1')?'checked':''?>> Flexible
														</label>
													</div>

													<div class="col-md-2">
														<label class="radio-inline">
															<input type="radio" name="type_wedge" value="2" <?=($type_wedge=='2')?'checked':''?>> Split
														</label>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Lantern Ring</label>
														<div class="col-md-8">
															<input type="text" id="lantern_ring" name="lantern_ring" class="form-control" placeholder="Lantern Ring" value="<?=$rel['lantern_ring']?>" >
														</div>
													</div>	
												</div>
												
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Chain / Chain Wheel / Safety Cable</label>
														<div class="col-md-8">
															<input type="text" id="chain_wheel_cable" name="chain_wheel_cable" class="form-control" placeholder="Chain / Chain Wheel / Safety Cable" value="<?=$rel['chain_wheel_cable']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Handwheel / Gear Box / Actuator Type</label>
														<div class="col-md-8">
															<input type="text" id="handwheel_gearbox_actuatortype" name="handwheel_gearbox_actuatortype" class="form-control" placeholder="Handwheel / Gear Box / Actuator Type" value="<?=$rel['handwheel_gearbox_actuatortype']?>" >
														</div>
													</div>	
												</div>
												
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Alternate Stem Packing Material</label>
														<div class="col-md-8">
															<input type="text" id="alternate_stem_packing_materials" name="alternate_stem_packing_materials" class="form-control" placeholder="Alternate Stem Packing Material" value="<?=$rel['alternate_stem_packing_materials']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Bonnet Bolting Material</label>
														<div class="col-md-8">
															<input type="text" id="bonnet_bolting_material" name="bonnet_bolting_material" class="form-control" placeholder="Bonnet Bolting Material" value="<?=$rel['bonnet_bolting_material']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="form-group">
													<label class="col-md-2 control-label">Stuffing Box Surface Finish</label>
													<div class="col-md-2">
														<label class="radio-inline">
															<input type="radio" name="stuffing_box_surface_finish" value="0" <?=($stuffing_box_surface_finish=='0')?'checked':''?>> 4.5 Ra (175 Micron)
														</label>
													</div>

													<div class="col-md-2">
														<label class="radio-inline">
															<input type="radio" name="stuffing_box_surface_finish" value="1" <?=($stuffing_box_surface_finish=='1')?'checked':''?>> Or Smoother
														</label>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Valve Paint Color</label>
														<div class="col-md-8">
															<input type="text" id="valve_paint_color" name="valve_paint_color" class="form-control" placeholder="Valve Paint Color" value="<?=$rel['valve_paint_color']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Additional Requirements</label>
														<div class="col-md-8">
															<input type="text" id="additional_req" name="additional_req" class="form-control" placeholder="Additional Requirements" value="<?=$rel['additional_req']?>" >
														</div>
													</div>
												</div>
											</div>


											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Ref. Approved GAD</label>
														<div class="col-md-8">
															<input type="text" id="ref_approve_gad" name="ref_approve_gad" class="form-control" placeholder="Ref. Approved GAD" value="<?=$rel['ref_approve_gad']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Ref. Approved QAP</label>
														<div class="col-md-8">
															<input type="text" id="raf_approve_qap" name="raf_approve_qap" class="form-control" placeholder="Ref. Approved QAP" value="<?=$rel['raf_approve_qap']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Reference WO</label>
														<div class="col-md-8">
															<input type="text" id="reference_wo" name="reference_wo" class="form-control" placeholder="Reference WO" value="<?=$rel['reference_wo']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Prepared By</label>
														<div class="col-md-8">
															<input type="text" id="prepared_by" name="prepared_by" class="form-control" placeholder="Prepared By" value="<?=$rel['prepared_by']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Prepared Name</label>
														<div class="col-md-8">
															<input type="text" id="prepared_name" name="prepared_name" class="form-control" placeholder="Prepared Name" value="<?=$rel['prepared_name']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Consulted By</label>
														<div class="col-md-8">
															<input type="text" id="consulted_by1" name="consulted_by1" class="form-control" placeholder="Consulted By" value="<?=$rel['consulted_by1']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Consulted Name</label>
														<div class="col-md-8">
															<input type="text" id="consulted_name1" name="consulted_name1" class="form-control" placeholder="Consulted Name" value="<?=$rel['consulted_name1']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Consulted By</label>
														<div class="col-md-8">
															<input type="text" id="consulted_by2" name="consulted_by2" class="form-control" placeholder="Consulted By" value="<?=$rel['consulted_by2']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Consulted Name</label>
														<div class="col-md-8">
															<input type="text" id="consulted_name2" name="consulted_name2" class="form-control" placeholder="Consulted Name" value="<?=$rel['consulted_name2']?>" >
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Reviewed & Approved By</label>
														<div class="col-md-8">
															<input type="text" id="review_approve_by" name="review_approve_by" class="form-control" placeholder="Reviewed & Approved By" value="<?=$rel['review_approve_by']?>" >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Reviewed & Approved Name</label>
														<div class="col-md-8">
															<input type="text" id="reviewed_approve_name" name="reviewed_approve_name" class="form-control" placeholder="Reviewed & Approved Name" value="<?=$rel['reviewed_approve_name']?>" >
														</div>
													</div>
												</div>
											</div>
											<input type="hidden" name="mode" id="mode" value="<?=$mode?>">
											<input type="hidden" name="order_review_id" id="order_review_id" value="<?=$rel['order_review_id']?>">
											<input type="hidden" name="sales_order_id" id="sales_order_id" value="<?=$sales_order_id?>">
											<input type="hidden" name="sales_ordertrn_id" id="sales_ordertrn_id" value="<?=$sales_ordertrn_id?>">
										</section>
									</div>
								</form>
								<!--</form>-->
							</div>
						</section>
					</div>
				</div>
			</section>
		</section>
		<?php include_once($include.'/footer.php');?>
	</section> 
	<?php include_once($include.'/include_js_file.php');?> 
	<script src="<?=ROOT.CRM_ROOT?>js/app/order_review_form.js?<?=time()?>"></script>
	<script>
		$('.default-date-picker').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});

	</script>
	<script type="text/javascript">
		$(document).ready(function () {
			var form = $("#order_review_add");


			form.validate({
				errorPlacement: function errorPlacement(error, element) {
					element.after(error);
				}
			});
			form.children("div").steps({
				headerTag: "h3",
				bodyTag: "section",
				transitionEffect: "slideLeft",
				onInit: function (event, currentIndex) {
					$('.default-date-picker').datepicker({
						format: 'dd-mm-yyyy',
						autoclose: true
					});	
				},
				onStepChanging: function (event, currentIndex, newIndex) {
					if(newIndex=="1"){
						
					}
					if(newIndex=="2"){
						
					}			
					$('.default-date-picker').datepicker({
						format: 'dd-mm-yyyy',
						autoclose: true
					});		
					return form.valid();
				},
				onFinishing: function (event, currentIndex) {
					$('.default-date-picker').datepicker({
						format: 'dd-mm-yyyy',
						autoclose: true
					});
					return form.valid();
				},
				onFinished: function (event, currentIndex) {
					$('.default-date-picker').datepicker({
						format: 'dd-mm-yyyy',
						autoclose: true
					});
					order_review_submit();					
					return form.valid();
				}
			});
		});
	</script> 

	
	
</body>
</html>