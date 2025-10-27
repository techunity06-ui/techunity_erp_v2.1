<link href="assets/morris.js-0.4.3/morris.css" rel="stylesheet" />
<?php //error_reporting(E_ALL) ?>
<?php include("dashboard_common_functions.php"); ?>
<?php include("common_functions/common_production_functions.php"); ?>
<style type="text/css">
	.head-text {
		font-family: "Segoe UI",Arial,sans-serif;
		font-weight: 400;
		text-align: center;
		margin: 10px 0;
		font-size: 25px;
		box-sizing: inherit;
		margin-block-start: -0.5em;
		margin-block-end: 0.0em;
		margin-inline-start: 8px;
		margin-inline-end: 0px;
		color: #fff!important;
		border-radius: 4px;
		position: relative;
		background-color: #337ab7!important;
	}
	.count , .count2
	{
		margin:0px !important;
		padding:0px !important

	}
	.cc_count
	{
		margin-left:5%;
	}
	
	.panel-heading
	{
		text-align:center;
		font-weight:bold;
		FONT-SIZE:16px;
	}
	
	.border_line
	{
		border-bottom:dotted blue 2px;
	}
	
	.link_dash
	{
		border-bottom:dotted blue thin;
	}
	
</style>
<?php
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	WD_TEAM_PENDING_TASK_SLUG_READ,
	WD_PENDING_TASK_SLUG_READ,
	WD_COMPALINT_SLUG_READ,
	WD_EMPLOYEE_SLUG_READ,
	WD_MRP_SLUG_READ,
	/* START JAYESH*/ WD_DESIGN_DEPARTMENT_SLUG_READ,/* END JAYESH*/
	WD_SPARE_PARTS_SLUG_READ,
	WD_PENDING_JOB_CARD_SLUG_READ,
	WD_PURCHASE_SLUG_READ,
	WD_QC_PENDING_SLUG_READ,
	WD_USER_INQUIRY_SLUG_READ,
	WD_INHOUSE_PENDING_PROCESS_SLUG_READ,
	WD_OUTSIDE_PENDING_PROCESS_SLUG_READ,
	WD_VENDOR_UNADJUSTED_AMOUNT,
	WD_CUSTOMER_UNADJUSTED_AMOUNT,
	CRM_SLUG_VIEW,
	SCHEDULING_SLUG_VIEW,
	MRP_SLUG_VIEW,
	PURCHASE_SLUG_VIEW,
	PRODUCTION_SLUG_VIEW,
	RESOURCE_SLUG_VIEW,
	INVENTORY_SLUG_VIEW,
	QC_SLUG_VIEW,
	SERVICE_SLUG_VIEW,
	FINANCE_SLUG_VIEW,
	HRMS_SLUG_VIEW,
	DESIGN_DEPARTMENT_SLUG_VIEW,
	MAINTENANCE_SLUG_VIEW,
	DISTRIBUTOR_PORTAL_SLUG_VIEW,
	VENDOR_PORTAL_SLUG_VIEW,
	SUPPORT_TICKET_SLUG_VIEW,
	DASHBOARD_PENDING_TASK_LIST_INQUIRY_ADD,
	DASHBOARD_PENDING_TASK_LIST,
	DASHBOARD_PENDING_TASK_LIST_GENERAL,
	DASHBOARD_PENDING_TASK_LIST_QUOTATION,
	DASHBOARD_PENDING_TASK_LIST_REVISE_QUOTATION,
	DASHBOARD_POST_CRM_GENERAL_FOLLOWUP,
	DASHBOARD_PENDING_TASK_LIST_QUOTATION_LIST,
	DASHBOARD_PENDING_TASK_LIST_QUOTATION_FOLLOWUP,
	DASHBOARD_PENDING_TASK_LIST_SALES_ORDER_LIST,
	DASHBOARD_PENDING_TASK_LIST_DISPATCH_LIST,
	DASHBOARD_PENDING_TASK_LIST_APPOINTMENT_LIST,
	DASHBOARD_PERSONAL_PENDING_TASK_LIST_INQUIRY_ADD,
	DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE,
	DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_GENERAL,
	DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_QUOTATION,
	DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_REVISE,
	DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_QUOTATION_LIST,
	DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_QUOTATION_FOLLOWUP,
	DASHBOARD_PERSONAL_PENDING_TASK_LIST_SALES_ORDER_LIST,
	DASHBOARD_PERSONAL_PENDING_TASK_LIST_DISPATCH_LIST,
	DASHBOARD_PERSONAL_PENDING_TASK_LIST_APPOINTMENT_LIST,
	DASHBOARD_GET_SALES_ORDER_DETAILS,
	DASHBOARD_GET_STOCK_DETAILS,
	DASHBOARD_GET_STOCK_PENDING_REQUEST,
	DASHBOARD_GET_REJECT_QC_REQUEST_LIST,
	DASHBOARD_GET_FORECAST_LIST,
	DASHBOARD_INDENT_LIST,
	DASHBOARD_PO_QUOTATION_LIST,
	DASHBOARD_PO_REQUEST_LIST,
	DASHBOARD_OVERDUE_PO_PRO_LIST,
	/*START JAYESH ADD GIR*/DASHBOARD_PO_GIR_LIST,/*END JAYESH ADD GIR*/
	DASHBOARD_PURCHASE_BILL_PENDING_LIST,
	DASHBOARD_DEBIT_NOTE_PENDING_LIST,
	DASHBOARD_PURCHASE_DISAPPROVED_VIEW,
	DASHBOARD_JOB_CARD_LIST,
	DASHBOARD_PENDING_JOB_WORK_LIST,
	DASHBOARD_PENDING_JOB_CARD,
	DASHBOARD_PURCHASE_QC_PENDING_LIST,
	DASHBOARD_PARTS_QC_PENDING_LIST,
	DASHBOARD_COMPLAIN_TYPE,
	DASHBOARD_COMPLAIN_TYPE_COMPLIANT_ASSIGNED,
	DASHBOARD_COMPLAIN_TYPE_EMPLOYEE_STARTED,
	DASHBOARD_COMPLAIN_TYPE_EMPLOYEE_NOT_STARTED,
	DASHBOARD_COMPLAIN_TYPE_CLOSED,
	DASHBOARD_COMPLAIN_TYPE_NOT_DONE,
	DASHBOARD_COMPLAIN_LIST,
	DASHBOARD_EMPLOYEE_PRESENT_LIST,
	DASHBOARD_EMPLOYEE_ABSENT_LIST,
	DASHBOARD_EMPLOYEE_EXPENSE_PENDING_LIST,
	DASHBOARD_SPARE_LIST_PENDING,
	DASHBOARD_RETURN_OLD_SPARE,
	DASHBOARD_CUSTOMER_UNADJUSTED_AMOUNT,
	DASHBOARD_PENDING_ORDER_INVOICE,
	DASHBOARD_PENDING_SPARE_INVOICE,
	DASHBOARD_PENDING_SERVICE_CHARGE_INVOICE,
	DASHBOARD_PENDING_FOC_SPARE_INVOICE,
	DASHBOARD_PENDING_INVOICE_APPROVAL,
	DASHBOARD_VENDOR_UNADJUSTED_AMOUNT,
	DASHBOARD_PO_REQUEST_LIST_APPROVE,
	/* START JAYESH*/
	WD_DESIGN_DEPARTMENT_SLUG_READ,
	DESIGN_DEPARTMENT_SLUG_VIEW,
	DASHBOARD_DESIGN_DEPARTMENT_GET_SALES_ORDER_DETAILS,
	/* END JAYESH*/
	/* START MAULIK*/
	PURCHASE_ORDER_APPROVAL,
	DASHBOARD_SERVICE_NOTES_LIST,
	PURCHASE_ORDER_FINANCE_APPROVAL,
	DASHBOARD_PO_SHORTCLOSE_APPROVAL,
	DASHBOARD_PO_SHORTCLOSE_DISAPPROVAL,
	WD_DISPATCH_VIEW,
	DASHBOARD_DISPATCH,
	DASHBOARD_DISPATCH_PENDING,
	DASHBOARD_FINAL_DISPATCH
	
	/* END MAULIK*/


]);
    //p($bulkAccessArray);
?>
<?php 
//  START :: added by Sanat : 20-09-2021
$company_config = getCompanyConfiguration($dbcon);
$is_store_approval = $company_config['store_approval'];

$production_on_dashboard = $company_config['production_on_dashboard'];
//  END :: added by Sanat : 20-09-2021	
?>


<section class="panel">
	<div class="panel-body ">
		<!--div1 code start-->
			<?php if(in_array(CRM_SLUG_VIEW,$bulkAccessArray)) { ?>
				<div class="col-md-12" onclick="sdiv1_load_data();">
					<div class="panel panel-primary">
						<div class="panel-heading">CRM</div>
					</div>
				</div>
				<div class="col-md-12" id="sdiv1"></div>
			<?php } ?>


		<!--div1 code end-->

		<!--div10 code start-->
		<?php 
		$companyConfiguration_dash=getCompanyConfiguration($dbcon);
		$enable_post_crm_dash = $companyConfiguration_dash['enable_post_crm'];

		if($enable_post_crm_dash == 1) { ?>

			<div class="col-md-12" onclick="sdiv10_load_data();">
					<div class="panel panel-primary">
						<div class="panel-heading">POST CRM</div>
					</div>
				</div>
				<div class="col-md-12" id="sdiv10"></div>
		<?php } ?>

		<!--div10 code end-->

		<!--div2 code start-->
		<?php
		$design_department_query="SELECT design_department from tbl_company_configuration";
		$design_department_row=mysqli_fetch_assoc($dbcon->query($design_department_query));
		$design_department_flag = $design_department_row['design_department'];
		if($design_department_flag == 1) { 
			if(in_array(DESIGN_DEPARTMENT_SLUG_VIEW,$bulkAccessArray)) { ?>
				<div class="col-md-12" onclick="sdiv2_load_data();">
					<div class="panel panel-primary">
						<div class="panel-heading">DESIGN DEPARTMENT </div>
					</div>
				</div>
				<div class="col-md-12" id="sdiv2"></div>
		<?php
			}
		} ?>
		<!--div2 code end-->

		<!-- div3 code start-->
		<?php if(in_array(MRP_SLUG_VIEW,$bulkAccessArray)) { ?>
				<div class="col-md-12" onclick="sdiv3_load_data();">
					<div class="panel panel-primary">
						<div class="panel-heading">MRP</div>
					</div>
				</div>
				<div class="col-md-12" id="sdiv3"></div>

		<?php } ?>
		<!-- div3 code end-->

		<!-- div4 code start-->
		<?php if(in_array(PURCHASE_SLUG_VIEW,$bulkAccessArray)) { ?>
				<div class="col-md-12" onclick="sdiv4_load_data();">
					<div class="panel panel-primary">
						<div class="panel-heading">PURCHASE</div>
					</div>
				</div>
				<div class="col-md-12" id="sdiv4"></div>

		<?php } ?>
		<!-- div4 code end-->

		<!-- div5 code start-->
		<?php if(in_array(PRODUCTION_SLUG_VIEW,$bulkAccessArray)) { ?>
				<div class="col-md-12" onclick="sdiv5_load_data();">
					<div class="panel panel-primary">
						<div class="panel-heading">PRODUCTION</div>
					</div>
				</div>
				<div class="col-md-12" id="sdiv5"></div>

		<?php } ?>
		<!-- div5 code end-->

		<!-- div6 code start-->
		<?php if(in_array(WD_INHOUSE_PENDING_PROCESS_SLUG_READ,$bulkAccessArray)) { ?>
				<div class="col-md-12" onclick="sdiv6_load_data();">
					<div class="panel panel-primary">
						<div class="panel-heading">INHOUSE PENDING PROCESS</div>
					</div>
				</div>
				<div class="col-md-12" id="sdiv6"></div>

		<?php } ?>
		<!-- div6 code end-->

		<!-- div7 code start-->
		<?php if(in_array(INVENTORY_SLUG_VIEW,$bulkAccessArray)) { ?>
				<div class="col-md-12" onclick="sdiv7_load_data();">
					<div class="panel panel-primary">
						<div class="panel-heading">INVENTORY</div>
					</div>
				</div>
				<div class="col-md-12" id="sdiv7"></div>

		<?php } ?>
		<!-- div7 code end-->

		<!-- div8 code start-->
		<?php if(in_array(QC_SLUG_VIEW,$bulkAccessArray)) { ?>
				<div class="col-md-12" onclick="sdiv8_load_data();">
					<div class="panel panel-primary">
						<div class="panel-heading">QC PENDING</div>
					</div>
				</div>
				<div class="col-md-12" id="sdiv8"></div>

		<?php } ?>
		<!-- div8 code end-->
	
		<!-- div9 code start-->
		<?php if(in_array(FINANCE_SLUG_VIEW,$bulkAccessArray)) { ?>
				<div class="col-md-12" onclick="sdiv9_load_data();">
					<div class="panel panel-primary">
						<div class="panel-heading">FINANCE</div>
					</div>
				</div>
				<div class="col-md-12" id="sdiv9"></div>

		<?php } ?>
		<!-- div9 code end-->

		<!-- div10 code start-->
		<?php if(in_array(SERVICE_SLUG_VIEW,$bulkAccessArray)) { ?>
				<div class="col-md-12" onclick="sdiv11_load_data();">
					<div class="panel panel-primary">
						<div class="panel-heading">SERVICE</div>
					</div>
				</div>
				<div class="col-md-12" id="sdiv11"></div>

		<?php } ?>
		<!-- div9 code end-->
		
		

	</div>
</section>

		<script type="text/javascript">
			<?php
	if($company_config['enable_old_dashbord']==1){ ?>
		$(document).ready(function() {
			//alert("ds");
			sdiv1_load_data();
			sdiv2_load_data();
			sdiv3_load_data();
			sdiv4_load_data();
			sdiv5_load_data();
			sdiv6_load_data();
			sdiv7_load_data();
			sdiv8_load_data();
			sdiv9_load_data();
			sdiv10_load_data();
			sdiv11_load_data();
			
		});
		<?php	} 
		?>
		</script>

	


<script type="text/javascript">

function load_value() {
		var c_year = $('#c_year').val();
		$.ajax({
			type: "POST",
			url: root_domain + 'app/dashboard_new/',
			data: {
				mode: "getyear",
				c_year: c_year
			},
			success: function(response) {
				//console.log(response);

				var data = JSON.parse(response);
				$('#bussiness_registered').html(data.c_register);
				$('#bussiness_assign').html(data.cassign);
				$('#bussiness_e_start').html(data.emp_start);
				$('#bussiness_e_notstart').html(data.cassign);
				$('#bussiness').html(data.cdone);
				$('#turnover').html(data.cndone);
				$('#all_comp_cnt').html(data.all_comp_cnt);
				
				$('#new_spare').html(data.new_spare);
				$('#old_spare').html(data.old_spare);
			}
		});
		Unloading();
	}

	function load_employee() {
		$.ajax({
			type: "POST",
			url: root_domain + 'app/dashboard_new/',
			data: {
				mode: "getemployee"
			},
			success: function(response) {
				console.log(response);
				//alert(response);
				var data = JSON.parse(response);
				$('#e_present').html(data.present);
				$('#e_absent').html(data.absent);
			}
		});
		Unloading();
	}

	function sdiv1_load_data(){
		Loading()
		//alert("hi");
		$.ajax({
			type: "POST",
			url: root_domain+'app/dashboard_new/',
			data: { mode : "sdiv1"},
			success: function(response){
			//console.log(response);
			$("#sdiv1").html(response);
			//var data = JSON.parse(response);
			//alert(data.purchse_order_pending);
			Unloading();
			}
		});
	}
	function sdiv2_load_data(){
		Loading()
	//	alert("hi");
		$.ajax({
			type: "POST",
			url: root_domain+'app/dashboard_new/',
			data: { mode : "sdiv2"},
			success: function(response){
			//console.log(response);
			$("#sdiv2").html(response);
			//var data = JSON.parse(response);
			//alert(data.purchse_order_pending);
		Unloading();
			}
		});
	}
	function sdiv3_load_data(){
	//	alert("hi");
		Loading()
		$.ajax({
			type: "POST",
			url: root_domain+'app/dashboard_new/',
			data: { mode : "sdiv3"},
			success: function(response){
			//console.log(response);
			$("#sdiv3").html(response);
			//var data = JSON.parse(response);
			//alert(data.purchse_order_pending);
		Unloading();
			}
		});
	}
	function sdiv4_load_data(){
		Loading()
	//	alert("hi");
		$.ajax({
			type: "POST",
			url: root_domain+'app/dashboard_new/',
			data: { mode : "sdiv4"},
			success: function(response){
			console.log(response);
			$("#sdiv4").html(response);
			//var data = JSON.parse(response);
			//alert(data.purchse_order_pending);
		Unloading();
			load_purchase_order_data();
			}
		});
	}
	function sdiv5_load_data(){
	//	alert("hi");
		Loading()
		$.ajax({
			type: "POST",
			url: root_domain+'app/dashboard_new/',
			data: { mode : "sdiv5"},
			success: function(response){
			//console.log(response);
			$("#sdiv5").html(response);
		Unloading();
			load_job_work_data();
			//var data = JSON.parse(response);
			//alert(data.purchse_order_pending);
			}
		});
	}
	function sdiv6_load_data(){
		Loading()
	//	alert("hi");
		$.ajax({
			type: "POST",
			url: root_domain+'app/dashboard_new/',
			data: { mode : "sdiv6"},
			success: function(response){
			//console.log(response);
			$("#sdiv6").html(response);
			//var data = JSON.parse(response);
			//alert(data.purchse_order_pending);
		Unloading();
			}
		});
	}
	function sdiv7_load_data(){
		Loading()
	//	alert("hi");
		$.ajax({
			type: "POST",
			url: root_domain+'app/dashboard_new/',
			data: { mode : "sdiv7"},
			success: function(response){
			//console.log(response);
			$("#sdiv7").html(response);
			//var data = JSON.parse(response);
			//alert(data.purchse_order_pending);

		Unloading();
			}
		});
	}
	function sdiv8_load_data(){
		Loading()
	//	alert("hi");
		$.ajax({
			type: "POST",
			url: root_domain+'app/dashboard_new/',
			data: { mode : "sdiv8"},
			success: function(response){
			//console.log(response);
			$("#sdiv8").html(response);
			//var data = JSON.parse(response);
			//alert(data.purchse_order_pending);
		Unloading();
			}
		});
	}
	function sdiv9_load_data(){
		Loading()
	//	alert("hi");
		$.ajax({
			type: "POST",
			url: root_domain+'app/dashboard_new/',
			data: { mode : "sdiv9"},
			success: function(response){
			//console.log(response);
			$("#sdiv9").html(response);
			//var data = JSON.parse(response);
			//alert(data.purchse_order_pending);
		Unloading();
			}
		});
	}

	function sdiv10_load_data(){
		Loading()
	//	alert("hi");
		$.ajax({
			type: "POST",
			url: root_domain+'app/dashboard_new/',
			data: { mode : "sdiv10"},
			success: function(response){
			//console.log(response);
			$("#sdiv10").html(response);
			//var data = JSON.parse(response);
			//alert(data.purchse_order_pending);
		Unloading();
			}
		});
		
	}

	function sdiv11_load_data(){
		Loading()
	//	alert("hi");
		$.ajax({
			type: "POST",
			url: root_domain+'app/dashboard_new/',
			data: { mode : "sdiv11"},
			success: function(response){
			//console.log(response);
			$("#sdiv11").html(response);
			//var data = JSON.parse(response);
			//alert(data.purchse_order_pending);
		Unloading();
			}
		});
		Loading(true);
			load_value();
			load_employee();
			Unloading();
	}

	function load_purchase_order_data(){
		Loading()
		var c_year=$('#c_year').val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/dashboard_new/',
		data: { mode : "get_purchase_order_data", c_year : c_year},
		success: function(response){
		//console.log(response);
		
		var data = JSON.parse(response);
		$('#purchse_quotation_list').html(data.purchse_quotation_list);
		$('#purchse_order_pending').html(data.purchse_order_pending);
		$('#purchse_order_pending_approval').html(data.purchse_order_pending_approval);
		$('#po_aprooval_finance').html(data.po_aprooval_finance);
		$('#purchse_overdue_pending').html(data.po_overdue_pending);
		$('#over_due_inward').html(data.overdue_inward);
		$('#today_inward').html(data.today_inward);
		$('#inward_followup').html(data.inward_followup);
		$('#debit_note_pending').html(data.debit_note_pending);
		$('#total_inward_pending').html(data.total_inward_pending);
		/* START JAYESH gir_counter */
		$('#purchase_gate_inward_receipt').html(data.gir_counter);

		$('#po_disapproved').html(data.po_disapproved);
		$('#po_shortclose_approval').html(data.po_shortclose_approval);
		$('#po_shortclose_disapproval').html(data.po_shortclose_disapproval);

		$('#purchase_bill_pending').html(data.purchase_bill_pending);
		$('#services_purchase_bill_pending').html(data.service_purchase_bill_pending);
		$('#jobwork_purchase_bill_pending').html(data.jobwork_purchase_bill_pending);
		Unloading();	
	}
});
	
	}




function load_job_work_data(){
	Loading()
	var c_year=$('#c_year').val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/dashboard_new/',
		data: { mode : "get_job_work_data", c_year : c_year},
		success: function(response){
		//console.log(response);
		
			var data = JSON.parse(response);
			$('#pending_job_card_new').html(data.pending_job_card_new);
			$('#pending_job_work_count').html(data.pending_job_work_count);	
			$('#request_jobwork_count').html(data.request_jobwork_count);	
			$('#pending_jobowork_chalan_count').html(data.pending_jobowork_chalan_count);	
			$('#pending_job_card').html(data.pending_job_card);

			$('#reprocess_pending_jobowork_chalan_count').html(data.reprocess_pending_jobowork_chalan_count);	
			$('#reprocess_pending_jobwork_grn').html(data.reprocess_pending_jobwork_grn);
			
			Unloading();	
		}
	});
	
}

function crm_task_data_load(){
	var user_id=$("#crm_tree_user").val();
	$.ajax({
		type: "POST",
		url: root_domain+'app/dashboard/',
		data: { mode : "crm_dashbord_data_load",user_id:user_id},
		success: function(response){
		//console.log(response);
		//var data = JSON.parse(response);
		$('#crm_table_data').html(response);
		$(".select2").select2({
			width: '100%'
		});
		//$('#e_absent').html(data.absent);
	}
});
}

</script>