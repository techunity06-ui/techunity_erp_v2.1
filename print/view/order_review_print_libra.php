<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once("../../crm/include/crm_common_functions.php");
//include_once(COMMON_FUNCTION_PATH."common_production_functions.php");
$_SESSION['contents']=''; 
$form="Order Review";
$mode="Print";
$sales_order_id =$dbcon->real_escape_string($_REQUEST['id']);
$type='pdf';
if(strtolower($type) == 'pdf') {

	$query  = "select revi.*,so.sales_order_no,so.sales_order_date,led.l_name,pro.product_name from tbl_sales_order_review_data as revi
	left join tbl_sales_order as so on so.sales_order_id=revi.sales_order_id
	left join tbl_sales_ordertrn as strn on strn.sales_ordertrn_id = revi.sales_ordertrn_id
	left join product_mst as pro on pro.product_id = strn.product_id
	left join tbl_ledger as led on led.l_id = so.cust_id
	where revi.review_status=0 and revi.sales_order_id=".$sales_order_id;

	$result = $dbcon->query($query);
	$rel = brp_mysqli_fetch_array($result);

	$required_del_date='';
	if($rel['required_del_date']!="1970-01-01" && $rel['required_del_date']!="0000-00-00")
	{
		$required_del_date=date('d-m-Y',strtotime($rel['required_del_date']));
	}

	$delivery_due_date='';
	if($rel['delivery_due_date']!="1970-01-01" && $rel['delivery_due_date']!="0000-00-00")
	{
		$delivery_due_date=date('d-m-Y',strtotime($rel['delivery_due_date']));
	}

	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	
	
	$user_qry = "select user.user_name, user.user_mail, user.user_phone, user.user_type, led.common_email_id from users as user
	left join tbl_ledger as led on led.l_id=user.employee_id
	where user.user_id=".$_SESSION['user_id']." and user.company_id=".$rel['company_id'];
	$user_data = mysqli_fetch_assoc($dbcon->query($user_qry));
	/* Check Discount is On or off Start */
	
	$companyConfiguration=getCompanyConfiguration($dbcon);
	
	$header = '<table>
				<tr>
					<td rowspan="2" style="width:10%"></td>
					<td class="backtdcolor" style="text-align:center;width:70%"><h2>Libra Engineering Works</h2></td>
					<td class="backtdcolor" style="text-align:center;width:20%">Page 1 Of 1</td>
				</tr>
				<tr>
					<td class="backtdcolor" style="text-align:center"><h2>Order Review Form</h2></td>
					<td class="backtdcolor" style="text-align:center">F-P 07-04 1</td>
				</tr>
			</table>
			<table>
				<tr>
					<td class="backtdcolor" style="width:20%"><strong>Record No.</strong></td>
					<td >'.$rel['sales_order_no'].'</td>
					<td class="backtdcolor" style="width:20%"><strong>Review Date</strong></td>
					<td >'.date('d-m-Y',strtotime($rel['sales_order_date'])).'</td>
				</tr>
				<tr>
					<td class="backtdcolor"><strong>Customer Name</strong></td>
					<td>'.$rel['l_name'].'</td>
					<td class="backtdcolor"><strong>Project</strong></td>
					<td>'.$rel['project'].'</td>
				</tr>

				<tr>
					<td class="backtdcolor"><strong>Product Desc.</strong></td>
					<td>'.$rel['product_name'].'</td>
					<td class="backtdcolor"><strong>Datasheet#</strong></td>
					<td>'.$rel['datasheet'].'</td>
				</tr>
			</table>';

	$footer = '<table>
		<tr>
			<td class="backtdcolor" style="width:35%"><strong>Ref.WO No.</strong>(If Order Recieved)</td>
			<td style="width:15%;text-align:center">'.$rel['ref_wo_no'].'</td>
			<td class="backtdcolor" style="width:25%"><strong>Reviewed By</strong></td>
			<td style="width:25%">'.$rel['reviewed_by'].'</td>
		</tr>
		<tr>
			<td class="backtdcolor"><strong>WO Date</strong></td>
			<td style="text-align:center">'.date('d-m-Y',strtotime($rel['wo_date'])).'</td>
			<td class="backtdcolor"><strong>Approved By</strong></td>
			<td>'.$rel['approved_by'].'</td>
		</tr>
	</table>';

	$html ='<html>
	<head>					
	<title>'.$form.' - '.$rel['sales_order_no'].'</title>
	<style type="text/css">
	/*
	.page{
		width:8.27in;
		height:10.69in;
		}*/
		.nextpage
		{
			page-break-after: always;
		}
		table{
			border-collapse:collapse;
			width:100%;
		}

		table td{
			border:1px solid #000 !important;
			/*page-break-inside:avoid;*/
		}
		.quot_annex_content_div table tr,td{
			padding:2.5px;
			page-break-before:always;
		}
		.backtdcolor{
			background-color: #bfbfbf;
		}

		</style>
		</head>
		<body>

		<htmlpageheader name="otherpages" style="display:none">
		<div style="text-align:center">'.$header.'</div>
		</htmlpageheader>
		<!--<htmlpagefooter name="otherpages_footer" style="display:none">
		<div style="text-align:center">'.$footer.'</div>
		</htmlpagefooter>-->
		<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
		<div>
			<table style="border:none">
				<tr>
					<td colspan="8" class="backtdcolor" style="text-align:left"><strong>General Requirements</strong></td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">1.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Our Quotation Ref.</td>
					<td colspan="4" style="width:75%">'.$rel['our_quotation_ref'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">2.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Legal Requirements</td>
					<td colspan="4" style="width:75%">'.$rel['legal_requirements'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">3.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Operating Temperature Range</td>
					<td colspan="4" style="width:75%">'.$rel['operating_temperature'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">4.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Operating Pressure Range</td>
					<td colspan="4" style="width:75%">'.$rel['operating_pressure'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">5.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Fluid / Service / Application</td>
					<td colspan="4" style="width:75%">'.$rel['fluid_service_application'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">6.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Design / Mfg. Standard</td>
					<td colspan="4" style="width:75%">'.$rel['design_mfg_standard'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">7.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Testing Standard</td>
					<td colspan="4" style="width:75%">'.$rel['testing_standard'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">8.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">QSL#</td>
					<td colspan="4" style="width:75%">'.$rel['qsl'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">9.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Qty.</td>
					<td colspan="4" style="width:75%">'.$rel['qty'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">10.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Required Delivery Date</td>
					<td colspan="4" style="width:75%">'.$required_del_date.'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center" rowspan="9">11.</td>
					<td rowspan="9" style="width:5%;text-rotate: 90">Material Of Construction</td>
					<td style="white-space:nowrap;">Body / Bonnet / Cover</td>
					<td colspan="4" style="width:75%">'.$rel['body_bonnet_cover'].'</td>
				</tr>
				<tr>
					<td>Gate/Ball/Disc/Plug</td>
					<td colspan="4" style="width:75%">'.$rel['gate_ball_disc_plug'].'</td>
				</tr>
				<tr>
					<td>Seat Ring</td>
					<td colspan="4" style="width:75%">'.$rel['seat_ring'].'</td>
				</tr>
				<tr>
					<td>Stem</td>
					<td colspan="4" style="width:75%">'.$rel['steam'].'</td>
				</tr>
				<tr>
					<td>Stud Nut</td>
					<td colspan="4" style="width:75%">'.$rel['stud_nut'].'</td>
				</tr>
				<tr>
					<td>Back Seat Bush</td>
					<td colspan="4" style="width:75%">'.$rel['back_seat_bush'].'</td>
				</tr>
				<tr>
					<td>Gasket</td>
					<td colspan="4" style="width:75%">'.$rel['gasket'].'</td>
				</tr>
				<tr>
					<td>Packing Seals</td>
					<td colspan="4" style="width:75%">'.$rel['packing_seals'].'</td>
				</tr>
				<tr>
					<td style="height:20px"></td>
					<td colspan="4"></td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">12.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">End Connection</td>
					<td colspan="4" style="width:75%">'.$rel['end_connection'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">13.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Valve Operating Type</td>
					<td style="width:18.75%">';
					if($rel['valve_ot']==0){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					}
					$html.=' Handwheel</td>
					<td style="width:18.75%">';
					if($rel['valve_ot']==1){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					}
					$html.=' Lever</td>
					<td style="width:18.75%">';
					if($rel['valve_ot']==2){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					} 
					$html.=' Gear Box</td>
					<td style="width:18.75%">';
					if($rel['valve_ot']==3){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					} 
					$html.=' Actuator</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">14.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Inspection By</td>
					<td style="width:18.75%">';
					if($rel['inspection_by']==0){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					} 
					$html.=' Internal</td>
					<td style="width:18.75%">';
					if($rel['inspection_by']==1){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					} 
					$html.=' Client</td>
					<td style="width:18.75%">';
					if($rel['inspection_by']==2){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					} 
					$html.=' TPI</td>
					<td style="width:18.75%"> </td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">15.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Scope Of Inspection</td>
					<td colspan="4" style="width:75%">'.$rel['scope_of_inspaction'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">16.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Applicable NDE</td>
					<td colspan="4" style="width:75%">'.$rel['applicable_nde'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">17.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">After Sales Service Req.</td>
					<td colspan="4" style="width:75%">'.$rel['af_sales_service'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">18.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Coating / Painting Req.</td>
					<td colspan="4" style="width:75%">'.$rel['coating_painting_req'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">19.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Packing Req.</td>
					<td colspan="4" style="width:75%">'.$rel['packing_req'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">20.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Marking On Product</td>
					<td colspan="4" style="width:75%">'.$rel['making_product'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">21.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Marking On Packing</td>
					<td colspan="4" style="width:75%">'.$rel['making_packing'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">22.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">API Monogram Marking</td>
					<td colspan="4" style="width:75%">'.$rel['api_monogram_making'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">23.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Delivery Due Date</td>
					<td colspan="4" style="width:75%">'.$delivery_due_date.'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">24.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Customer Contact Detail</td>
					<td colspan="4" style="width:75%">'.$rel['customer_contact_detail'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">25.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Delivery Location</td>
					<td colspan="4" style="width:75%">'.$rel['delivery_location'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">26.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Documents to be Submit</td>
					<td colspan="4" style="width:75%">'.$rel['documents_submit'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">27.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Payment Terms</td>
					<td colspan="4" style="width:75%">'.$rel['payment_terms'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">28.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Insurance</td>
					<td colspan="4" style="width:75%">'.$rel['insurance'].'</td>
				</tr>

				<tr>
					<td style="width:5%;text-align:center;">29.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Freight</td>
					<td colspan="4" style="width:75%">'.$rel['freight'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">30.</td>
					<td colspan="2" style="white-space:nowrap;width:20%">Remarks</td>
					<td colspan="4" style="width:75%">'.$rel['remark'].'</td>
				</tr>
			</table>
		</div><center class="nextpage"></center>
		<div>
			<table style="border:none">
				<tr>
					<td colspan="3" class="backtdcolor" style="text-align:left"><strong>API 6D Specific Requirements</strong></td>
				</tr>

				<tr>
					<td style="width:5%;text-align:center;">1.</td>
					<td style="white-space:nowrap;width:20%">Bore Type & Bore Size</td>
					<td style="width:75%">'.$rel['bore_type_size'].'</td>
				</tr>

				<tr>
					<td style="width:5%;text-align:center;">2.</td>
					<td style="white-space:nowrap;width:20%">Face - Face & End - End Diamension</td>
					<td style="width:75%">'.$rel['face_end_dimension'].'</td>
				</tr>

				<tr>
					<td style="width:5%;text-align:center;">3.</td>
					<td style="white-space:nowrap;width:20%">Intermediate Design Pressure & Temp</td>
					<td style="width:75%">'.$rel['intermediate_design_pressure'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">4.</td>
					<td style="white-space:nowrap;width:20%">Service Compatibility</td>
					<td style="width:75%">'.$rel['service_compatibillity'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">5.</td>
					<td style="white-space:nowrap;width:20%">Valve Orientation</td>
					<td style="width:75%">'.$rel['valve_orentation'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">6.</td>
					<td style="white-space:nowrap;width:20%">Pressure Balance Hole</td>
					<td style="width:75%">'.$rel['pressure_balance_hole'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">7.</td>
					<td style="white-space:nowrap;width:20%">End Connectors Type</td>
					<td style="width:75%">'.$rel['end_connectors_type'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center;">8.</td>
					<td style="white-space:nowrap;width:20%">External Loads</td>
					<td style="width:75%">'.$rel['external_loads'].'</td>
				</tr>
				<tr>
					<td rowspan="17" style="width:5%;text-align:center;">9.</td>
					<td style="width:20%" style="border-bottom:none"><u>Valve Operational Data to be submit to customer</u></td>
					<td style="width:75%;border-bottom:none"></td>
				</tr>
				<tr>
					<td style="border-bottom:none;border-top:none">a. <u>Flow coefficient Cv or Kv.</u></td>
					<td style="width:75%;border-bottom:none;border-top:none">'.$rel['flow_coefficient_cvkv'].'</td>
				</tr>
				<tr>
					<td style="border-bottom:none;border-top:none">b. <u>Valve top works dimensions</u></td>
					<td style="width:75%;border-bottom:none;border-top:none">'.$rel['valve_topwork_diamention'].'</td>
				</tr>
				<tr>
					<td style="border-bottom:none;border-top:none">c. <u>Break-to-open torque or thrust (BTO).</u></td>
					<td style="width:75%;border-bottom:none;border-top:none">'.$rel['bto'].'</td>
				</tr>
				<tr>
					<td style="border-bottom:none;border-top:none">d. <u>Break-to-close torque or thrust (BTC).</u></td>
					<td style="width:75%;border-bottom:none;border-top:none">'.$rel['btc'].'</td>
				</tr>
				<tr>
					<td style="white-space:nowrap;border-bottom:none;border-top:none">e. <u>Run-to-open torque or thrust (RTO).</u></td>
					<td style="width:75%;border-bottom:none;border-top:none">'.$rel['rto'].'</td>
				</tr>
				<tr>
					<td style="white-space:nowrap;border-bottom:none;border-top:none">f. <u>Run-to-close (reseat) torque or thrust (RTC).</u></td>
					<td style="width:75%;border-bottom:none;border-top:none">'.$rel['rtc'].'</td>
				</tr>
				<tr>
					<td style="white-space:nowrap;border-bottom:none;border-top:none">g. <u>End-to-open torque or thrust (ETO).</u></td>
					<td style="width:75%;border-bottom:none;border-top:none">'.$rel['eto'].'</td>
				</tr>
				<tr>
					<td style="white-space:nowrap;border-bottom:none;border-top:none">h. <u>End-to-close (reseat) torque or thrust (ETC).</u></td>
					<td style="width:75%;border-bottom:none;border-top:none">'.$rel['etc'].'</td>
				</tr>
				<tr>
					<td style="white-space:nowrap;border-bottom:none;border-top:none">i. <u>Valve drive train MAST</u></td>
					<td style="width:75%;border-bottom:none;border-top:none">'.$rel['valve_drive_train_mast'].'</td>
				</tr>
				<tr>
					<td style="white-space:nowrap;border-bottom:none;border-top:none">j. <u>Valve Characteristics:</u></td>
					<td style="width:75%;border-bottom:none;border-top:none"></td>
				</tr>
				<tr>
					<td style="border-bottom:none;border-top:none">&nbsp;&nbsp;* <u>Length and direction of stroke to open and close for linear valves. Or</u></td>
					<td style="width:75%;border-bottom:none;border-top:none">'.$rel['length_direction_stroke_oc_linear_valve'].'</td>
				</tr>
				<tr>
					<td style="border-bottom:none;border-top:none">&nbsp;&nbsp;* <u>Angle and direction of rotation for part-turn or check valves. or</u></td>
					<td style="width:75%;border-bottom:none;border-top:none">'.$rel['angle_rotation_partturn_checkvalve'].'</td>
				</tr>
				<tr>
					<td style="border-bottom:none;border-top:none">&nbsp;&nbsp;* <u>Direction of rotation and number of turns for multi-turn valves.</u></td>
					<td style="width:75%;border-bottom:none;border-top:none">'.$rel['direction_rotation_number_multiturn_valve'].'</td>
				</tr>
				<tr>
					<td style="border-bottom:none;border-top:none">k. <u>Thrust necessary to enable the valve to maintain position. if applicable.</u></td>
					<td style="width:75%;border-bottom:none;border-top:none">'.$rel['enable_valve_maintain_position'].'</td>
				</tr>
				<tr>
					<td style="border-bottom:none;border-top:none">l. <u>Valve breakaway angle or breakaway percent of stroke.</u></td>
					<td style="width:75%;border-bottom:none;border-top:none">'.$rel['breakaway_anglepercent_stroke'].'</td>
				</tr>
				<tr>
					<td style="border-top:none">m. <u>Number of turns for manually operated.</u></td>
					<td style="width:75%;border-bottom:none;border-top:none">'.$rel['num_turns_manualy_opevalve'].'</td>
				</tr>
				<tr>
					<td style="text-align:center">10.</td>
					<td>Flange Bolting for Studded-outlet End Connectors</td>
					<td>'.$rel['flange_bolting_studded_outlet_endconnector'].'</td>
				</tr>
				<tr>
					<td style="text-align:center">11.</td>
					<td>Chemical Composition of pressure-containing & controlling materials</td>
					<td>'.$rel['chemcomp_prescontai_controlling_material'].'</td>
				</tr>
				<tr>
					<td style="text-align:center">12.</td>
					<td>Valve Seat Functionality</td>
					<td>'.$rel['valve_seat_functionality'].'</td>
				</tr>
				<tr>
					<td style="text-align:center">13.</td>
					<td>Extended Stem and Shaft Assemblies</td>
					<td>'.$rel['extended_steam_shaft_assemblies'].'</td>
				</tr>
				<tr>
					<td style="text-align:center">14.</td>
					<td>Bolting for Sour Service</td>
					<td>'.$rel['boulting_sour_service'].'</td>
				</tr>
				<tr>
					<td style="text-align:center">15.</td>
					<td>Locking Device</td>
					<td>'.$rel['locking_device'].'</td>
				</tr>
				<tr>
					<td style="text-align:center">16.</td>
					<td>Position Indicator</td>
					<td>'.$rel['position_indicator'].'</td>
				</tr>
				<tr>
					<td style="text-align:center">17.</td>
					<td>Drain</td>
					<td>'.$rel['drain'].'</td>
				</tr>
				<tr>
					<td style="text-align:center">18.</td>
					<td>Vent</td>
					<td>'.$rel['vent'].'</td>
				</tr>
				<tr>
					<td style="text-align:center">19.</td>
					<td>Drain / Vent Lines & Pressure of Lines</td>
					<td>'.$rel['drain_pressure_ventlines'].'</td>
				</tr>
			</table>
		</div><center class="nextpage"></center>
		<div>
			<table style="border:none">
				<tr>
					<td style="width:5%;text-align:center">20.</td>
					<td style="width:20%;white-space:nowrap">Sealant Injection & Pressure of lines</td>
					<td style="width:75%">'.$rel['sealant_injection'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">21.</td>
					<td style="width:20%;">Drain, Vent, and Injection Valves</td>
					<td style="width:75%">'.$rel['drain_vent_injection_valves'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">22.</td>
					<td style="width:20%;">Piggabillity</td>
					<td style="width:75%">'.$rel['paggabillity'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">23.</td>
					<td style="width:20%;">Welding Overlay Iron Dilution</td>
					<td style="width:75%">'.$rel['welding_overlay_iron_dilution'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">24.</td>
					<td style="width:20%;">Weld Repair of Forgings and Plate Material</td>
					<td style="width:75%">'.$rel['weld_repair_forgings'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">25.</td>
					<td style="width:20%;">Pressure Boundary Bolting-Hardness Testing</td>
					<td style="width:75%">'.$rel['pressure_boundary_bolting_hardness_testing'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">26.</td>
					<td style="width:20%;">In-service / Field Testing</td>
					<td style="width:75%">'.$rel['inservice_field_testing'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">27.</td>
					<td style="width:20%;">Anti-static Device Test</td>
					<td style="width:75%">'.$rel['anti_static_device_test'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">28.</td>
					<td style="width:20%;">Torque Test</td>
					<td style="width:75%">'.$rel['torque_test'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">29.</td>
					<td style="width:20%;">Fire Safe Test</td>
					<td style="width:75%">'.$rel['fire_safe_test'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">30.</td>
					<td style="width:20%;">Drive Train Strength Test</td>
					<td style="width:75%">'.$rel['drive_train_strength_test'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">31.</td>
					<td style="width:20%;">Supplymentary Test</td>
					<td style="width:75%">'.$rel['supplementry_test'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">32.</td>
					<td style="width:20%;">Cavity Relief Test</td>
					<td style="width:75%">'.$rel['cavity_relief_test'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">33.</td>
					<td style="width:20%;white-space:nowrap">Double Block & Bleed (DBB) Valves Test</td>
					<td style="width:75%">'.$rel['dbb_valve_test'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">34.</td>
					<td style="width:20%;">Double Isolation and Bleed DIB-1 (Both Seats Bidirectional) Test</td>
					<td style="width:75%">'.$rel['dib1_test'].'</td>
				</tr>
				
				<tr>
					<td style="width:5%;text-align:center">35.</td>
					<td style="width:20%;">Double Isolation and Bleed DIB-2 (One Undirectional and One Bidirectional Seat) Test</td>
					<td style="width:75%">'.$rel['dib2_seat_test'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">36.</td>
					<td style="width:20%;">Operations Testing--Valves Required for Double Isolation and Bleed (DIB-1 or DIB-2) Test</td>
					<td style="width:75%">'.$rel['dib1_dib2_test_valves'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">37.</td>
					<td style="width:20%;">Hardness Test</td>
					<td style="width:75%">'.$rel['hardness_test'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">38.</td>
					<td style="width:20%;">Charpy Impact Test</td>
					<td style="width:75%">'.$rel['charpy_impact_test'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">39.</td>
					<td style="width:20%;">HIC Test</td>
					<td style="width:75%">'.$rel['hic_test'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">40.</td>
					<td style="width:20%;">High Pressure Gas Test(Shell & Seat)</td>
					<td style="width:75%">'.$rel['high_pressure_gas_test'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">41.</td>
					<td style="width:20%;">Fugitive Emissions Test</td>
					<td style="width:75%">'.$rel['fugitive_emission_test'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">42.</td>
					<td style="width:20%;">Gauge/Drift Test</td>
					<td style="width:75%">'.$rel['gauge_drift_test'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">43.</td>
					<td style="width:20%;">Pressure Testing Valves With Hydrostatic End Load</td>
					<td style="width:75%">'.$rel['pressure_testing_valve_hydrostatic'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">44.</td>
					<td style="width:20%;">Special Flanges or Mechanical Joints</td>
					<td style="width:75%">'.$rel['special_flanges_mechanical_joints'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">45.</td>
					<td style="width:20%;">Third Party Witness</td>
					<td style="width:75%">'.$rel['thirdparty_witness'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">46.</td>
					<td style="width:20%;">Hydro Shell Testing of One-piece Bodies in non-assembled condition</td>
					<td style="width:75%">'.$rel['hydroshell_nonassembled_cond'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">47.</td>
					<td style="width:20%;font-size:13.5px">Corrosion Protection Measures for long term storage or unusual/harsh condition</td>
					<td style="width:75%">'.$rel['corrosion_protection_measures_longterm_storage'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">48.</td>
					<td style="width:20%;">External Coating or Painting of Corrosion-resistant Valves</td>
					<td style="width:75%">'.$rel['external_coating_painting_valves'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">49.</td>
					<td style="width:20%;">Corrosion-resistant Metalic Surfaces</td>
					<td style="width:75%">'.$rel['corrosion_resistant_metalic_surfaces'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">50.</td>
					<td style="width:20%;">Disassembly/Maintenance Tools Provision</td>
					<td style="width:75%">'.$rel['disassembly_maintainance_tool'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">51.</td>
					<td style="width:20%;">Support Rib or Legs</td>
					<td style="width:75%">'.$rel['support_rib_legs'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">52.</td>
					<td style="width:20%;">Valve Lifting</td>
					<td style="width:75%">'.$rel['valve_lifting'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">53.</td>
					<td style="width:20%;">Use of Assembly Lubricant</td>
					<td style="width:75%">'.$rel['use_assembly_lubricant'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">54.</td>
					<td style="width:20%;">Additional Requirements</td>
					<td style="width:75%">'.$rel['additional_requirements'].'</td>
				</tr>
			</table>
		</div><center class="nextpage"></center>
		<div>
			<table style="border:none">
				<tr>
					<td colspan="5" class="backtdcolor" style="text-align:left"><strong>API 600 Specific Requirements</strong></td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">1.</td>
					<td style="width:20%;">Auxiliary Connection & Openings</td>
					<td colspan="3" style="width:75%">'.$rel['auxilliary_connope'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">2.</td>
					<td style="width:20%;">Valve Orientation / Mount Location</td>
					<td colspan="2" style="width:37.5%">';
					if($rel['valve_orientation']==0){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					} 
					$html.=' Horizontal</td>
					<td style="width:37.5%">';
					if($rel['valve_orientation']==1){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					}
					$html.=' Vertical</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">3.</td>
					<td style="width:20%;white-space:nowrap">Hard facing of Body or Wedge Guides</td>
					<td colspan="3" style="width:75%">'.$rel['hard_facing_body_wedge_guides'].'</td>
				</tr>
				<tr>
					<td rowspan="2" style="width:5%;text-align:center">4.</td>
					<td rowspan="2" style="width:20%;white-space:nowrap">Bonnet GasketType</td>
					<td colspan="2" style="width:37.5%">';
					if($rel['bonnet_gaskettype']==0){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					}
					$html.=' Solid Metal</td>
					<td style="width:37.5%">';
					if($rel['bonnet_gaskettype']==1){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					}
					$html .=' SPW SS316 Metal Gasket With Graphite Filer & CS Inner Ring</td>
				</tr>
				<tr>
					<td colspan="2" style="width:37.5%">';
					if($rel['bonnet_gaskettype']==2){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					}
					$html .=' Metal Ring Joint</td>
					<td style="width:37.5%">';
					if($rel['bonnet_gaskettype']==3){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					}
					$html.=' SPW Metal Gasket With Filer</td>
				</tr>
				<tr>
					<td rowspan="2" style="width:5%;text-align:center">5.</td>
					<td rowspan="2" style="width:20%;white-space:nowrap">Body - Bonnet Joint Flange Facing</td>
					<td style="width:25%">';
					if($rel['bonnet_joint_flange_facing']==0){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					}
					$html.=' Flat Face</td>
					<td style="width:25%">';
					if($rel['bonnet_joint_flange_facing']==1){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					}
					$html.=' Raise Face</td>
					<td style="width:25%">';
					if($rel['bonnet_joint_flange_facing']==2){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					}
					$html.=' Tongue & Groove</td>
				</tr>

				<tr>
					<td style="width:25%">';
					if($rel['bonnet_joint_flange_facing']==3){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					}
					$html.=' Male - Female</td>
					<td style="width:25%">';
					if($rel['bonnet_joint_flange_facing']==4){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					}
					$html.=' Ring Joint</td>
					<td style="width:25%"></td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">6.</td>
					<td style="width:20%;white-space:nowrap">Tapped Openings</td>
					<td colspan="3" style="width:75%">'.$rel['tapped_openings'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">7.</td>
					<td style="width:20%;white-space:nowrap">Type of Wedge</td>
					<td style="width:25%">';
					if($rel['type_wedge']==0){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					}
					$html.=' Solid</td>
					<td style="width:25%">';
					if($rel['type_wedge']==1){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					}
					$html.=' Flexible</td>
					<td style="width:25%">';
					if($rel['type_wedge']==2){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					}
					$html.=' Split</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">8.</td>
					<td style="width:20%;white-space:nowrap">Latern Ring</td>
					<td colspan="3" style="width:75%">'.$rel['lantern_ring'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">9.</td>
					<td style="width:20%;white-space:nowrap">Chain / Chain Wheel / Safety Cable</td>
					<td colspan="3" style="width:75%">'.$rel['chain_wheel_cable'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">10.</td>
					<td style="width:20%;white-space:nowrap">Handwheel/Gear Box/Actuator Type</td>
					<td colspan="3" style="width:75%">'.$rel['handwheel_gearbox_actuatortype'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">11.</td>
					<td style="width:20%;white-space:nowrap">Alternate Stem Packing Material</td>
					<td colspan="3" style="width:75%">'.$rel['alternate_stem_packing_materials'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">12.</td>
					<td style="width:20%;white-space:nowrap">Bonnet Bolting Material</td>
					<td colspan="3" style="width:75%">'.$rel['bonnet_bolting_material'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">13.</td>
					<td style="width:20%;white-space:nowrap">Stuffing Box Surface Finish</td>
					<td colspan="2" style="width:37.5%">';
					if($rel['stuffing_box_surface_finish']==0){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					}
					
					$html.=' 4.5 Ra (175 Micron)</td>
					<td style="width:37.5%">';

					if($rel['stuffing_box_surface_finish']==1){
						$html .= '<img src="'.ROOT.LOGO.'/right.png" style="width:10px;height:10px">';
					}else{
						$html .= '<img src="'.ROOT.LOGO.'/blank.png" style="width:10px;height:10px">';
					}
					$html.=' Or Smoother</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">14.</td>
					<td style="width:20%;white-space:nowrap">Valve Paint Color</td>
					<td colspan="3" style="width:75%">'.$rel['valve_paint_color'].'</td>
				</tr>
				<tr>
					<td style="width:5%;text-align:center">15.</td>
					<td style="width:20%;white-space:nowrap">Additional Requirements</td>
					<td colspan="3" style="width:75%">'.$rel['additional_req'].'</td>
				</tr>
			</table><br>
			<table style="border:none;">
				<tr>
					<td class="backtdcolor" style="width:30%">Ref. Approved GAD#</td>
					<td style="width:70%">'.$rel['ref_approve_gad'].'</td>
				</tr>
				<tr>
					<td class="backtdcolor" style="width:30%">Ref. Approved QAP#</td>
					<td style="width:70%">'.$rel['raf_approve_qap'].'</td>
				</tr>
				<tr>
					<td class="backtdcolor" style="width:30%">Reference WO#</td>
					<td style="width:70%">'.$rel['reference_wo'].'</td>
				</tr>
			</table>
			<br>
			<table style="border:none">
				<tr>
					<td class="backtdcolor" style="width:10%">Prepared By</td>
					<td style="width:25%">'.$rel['prepared_by'].'</td>
					<td class="backtdcolor" style="width:10%;text-align:center">Name</td>
					<td style="width:25%">'.$rel['prepared_name'].'</td>
					<td class="backtdcolor" style="width:10%;text-align:center">Sign</td>
					<td style="width:20%"></td>
				</tr>
				<tr>
					<td class="backtdcolor" style="width:10%;white-space:nowrap">Consulted By</td>
					<td style="width:25%">'.$rel['consulted_by1'].'</td>
					<td class="backtdcolor" style="width:10%;text-align:center">Name</td>
					<td style="width:25%">'.$rel['consulted_name1'].'</td>
					<td class="backtdcolor" style="width:10%;text-align:center">Sign</td>
					<td style="width:20%"></td>
				</tr>
				<tr>
					<td class="backtdcolor" style="width:10%">Consulted By</td>
					<td style="width:25%">'.$rel['consulted_by2'].'</td>
					<td class="backtdcolor" style="width:10%;text-align:center">Name</td>
					<td style="width:25%">'.$rel['consulted_name2'].'</td>
					<td class="backtdcolor" style="width:10%;text-align:center">Sign</td>
					<td style="width:20%"></td>
				</tr>
				<tr>
					<td class="backtdcolor" style="width:10%">Reviewed By <br> Approved By</td>
					<td style="width:25%">'.$rel['review_approve_by'].'</td>
					<td class="backtdcolor" style="width:10%;text-align:center">Name</td>
					<td style="width:25%">'.$rel['reviewed_approve_name'].'</td>
					<td class="backtdcolor" style="width:10%;text-align:center">Sign</td>
					<td style="width:20%"></td>
				</tr>
			</table>
		</div>';
		
		$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
		/*echo $header.$html;exit;*/
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','calibri','10','10','40','10','1','1');

		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		//$mpdf->SetHTMLFooter($footer);

		//Show page number
		/*$mpdf->pagenumPrefix = ' ';
		$mpdf->pagenumSuffix = ' / ';
		$mpdf->nbpgPrefix = ' ';
		$mpdf->nbpgSuffix = ' pages';
		$mpdf->SetFooter('{PAGENO}{nbpg}');*/

		$mpdf->SetWatermarkText();
		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion=true;
		$mpdf->charset_in='UTF-8';
		$mpdf->WriteHTML($html);
		$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$purchaseorder_id.'.pdf','f');
		ob_clean();
		return 'inquiry_review_'.$inquiryid.'.pdf';
	}

	
?>