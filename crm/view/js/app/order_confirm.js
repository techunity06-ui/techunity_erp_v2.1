$(document).ready(function() {
	load_order_confirm_datatable();
}); 

function load_order_confirm_datatable(){
	//var status=$('input[name=approved_status]:Checked').val();
	var date=$('#rep_date').val();
	var branch_id=$('#branch_id').val();
	var fil_type=$('#fil_type').val();
	
	$("#order-confirm-datatable").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !"
		},
		"aLengthMenu": [[ 10, 20, 50, 100, -1], [ 10, 20, 50, 100, "All"]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + crm_domain + 'app/order_confirm/',
		"fnServerParams": function ( aoData ) {
			aoData.push( {"name": "mode", "value": "fetch" },
			  {"name": "date", "value": date },
			  {"name": "fil_type", "value": fil_type },
			  {"name": "branch_id", "value": branch_id }
			   );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
function attch_po_dtl(quotation_id,quotation_no){
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/order_confirm/',
		data: { mode : "attch_po_dtl", quotation_id:quotation_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#po_dtl_modal').modal('show');
			$('#head_po_qt_no').html(quotation_no);
			$('#qt_po_ref_id').val(quotation_id);
			$('#qt_company_name').val(resp.qt_company_name);
			$('#qt_com_mno').val(resp.qt_com_mno);
                        $('#qt_po_amount').val(resp.g_total);
			$('#qt_com_gstno').val(resp.qt_com_gstno);
			$('#qt_com_addr').val(resp.qt_com_addr);
			$('#qt_add_country').select2("val",resp.qt_add_country);
			load_state(resp.qt_add_country,'qt_add_state',resp.qt_add_state);
			load_city(resp.qt_add_state,'qt_add_city',resp.qt_add_city);
			
			$('#qt_po_no').val(resp.qt_po_no);
			
			if(resp.qt_po_date){
				$("#qt_po_date").datepicker("setDate", resp.qt_po_date);
			}
			else{
				$("#qt_po_date").val("");
			}
			
			if(resp.qt_delivery_date){
				$("#qt_delivery_date").datepicker("setDate", resp.qt_delivery_date);
			}
			else{
				$("#qt_delivery_date").val("");
			}
			
			//$('#qt_po_amount').val(resp.qt_po_amount);
			$('#qt_po_attch').val("");
			if(resp.qt_po_attch){
				$('#qt_po_attch_view').show().attr("href",resp.qt_po_attch);
			}
			else{
				$('#qt_po_attch_view').hide();
			}
			Unloading();
		}		 
	});
	
}
function add_attch_po_dtl(){
	if(!$("#qt_company_name").val()){		
		toastr.warning("Enter Company Name.", "ERROR");
		$("#qt_company_name").focus();
		return false;
	}
	else if(!$("#qt_add_country").val()){		
		toastr.warning("Select Country", "ERROR");
		$("#qt_add_country").select2('focus');
		return false;
	}
	else if(!$("#qt_add_state").val()){		
		toastr.warning("Select State", "ERROR");
		$("#qt_add_state").select2('focus');
		return false;
	}
	else if(!$("#qt_add_city").val()){		
		toastr.warning("Select City", "ERROR");
		$("#qt_add_city").select2('focus');
		return false;
	}
	else if(!$("#qt_po_no").val()){		
		toastr.warning("Enter PO No.", "ERROR");
		$("#qt_po_no").focus();
		return false;
	}
	else if(!$("#qt_po_date").val()){		
		toastr.warning("Enter PO Date", "ERROR");
		$("#qt_po_date").focus();
		return false;
	}
	else if(!$("#qt_po_amount").val()){		
		toastr.warning("Enter PO Amount", "ERROR");
		$("#qt_po_amount").focus();
		return false;
	}
	else if(!$("#qt_po_attch").val()){		
		toastr.warning("Choose P.O. Attachment", "ERROR");
		$("#qt_po_attch").focus();
		return false;
	}
	else if(!$("#qt_delivery_date").val()){		
		toastr.warning("Enter Delivery Date", "ERROR");
		$("#qt_delivery_date").focus();
		return false;
	}
	
	$('#add_attch_po_dtl_btn').prop("disabled",true);
	Loading();
	var form_data = new FormData();
	form_data.append('mode', "add_attch_po_dtl");
	form_data.append('quotation_id', $("#qt_po_ref_id").val());
	form_data.append('qt_po_no', $("#qt_po_no").val());
	form_data.append('qt_po_date', $("#qt_po_date").val());
	form_data.append('qt_po_amount', $("#qt_po_amount").val());
	form_data.append('qt_delivery_date', $("#qt_delivery_date").val());
	form_data.append("qt_po_attch", document.getElementById('qt_po_attch').files[0]);
	form_data.append('qt_company_name', $("#qt_company_name").val());
	form_data.append('qt_com_mno', $("#qt_com_mno").val());
	form_data.append('qt_com_gstno', $("#qt_com_gstno").val());
	form_data.append('qt_com_addr', $("#qt_com_addr").val());
	form_data.append('qt_add_country', $("#qt_add_country").val());
	form_data.append('qt_add_state', $("#qt_add_state").val());
	form_data.append('qt_add_city', $("#qt_add_city").val());
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain+'app/order_confirm/',
		data: form_data,
		contentType: false,
		processData:false,
		success: function(resp){
			//console.log(resp);
			var resp=jQuery.parseJSON(resp);
			var response=resp.msg;
			if(response.trim() == "1") {
				toastr.success("DATA ADDED SUCCESSFULLY", "SUCCESS");
				$('#po_dtl_modal').modal('hide');
				$('#add_attch_po_dtl_btn').prop("disabled",false);
				load_order_confirm_datatable();
			}
			else if(response.trim() == "0") {
				toastr.warning("SOMETHING WRONG", "WARNING");
			}
			Unloading();
		}		 
	});
}
function attch_order_conf_dtl(quotation_id,quotation_no){

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/order_confirm/',
		data: { mode : "attch_order_conf_dtl", quotation_id:quotation_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#order_conf_dtl_modal').modal('show');
			$('#head_ord_qt_no').html(quotation_no);
			$('#qt_ord_ref_id').val(quotation_id);
		
			$('#qt_order_conf_attch').val("");
			if(resp.qt_order_conf_attch){
				$('#qt_order_conf_attch_view').show().attr("href",resp.qt_order_conf_attch);
			}
			else{
				$('#qt_order_conf_attch_view').hide();
			}
			Unloading();
		}		 
	});
}
function add_order_conf_dtl(){
	var ext = $('#qt_order_conf_attch').val().split('.').pop().toLowerCase();
    if($.inArray(ext, ['gif','png','jpg','jpeg']) === -1) {
        toastr.warning("Only image type jpg/png/jpeg/gif is allowed", "ERROR");
        $("#qt_order_conf_attch").focus();
        return false;
    }else if(!$("#qt_order_conf_attch").val()){		
		toastr.warning("Upload Order Confirmation Attachment", "ERROR");
		$("#qt_order_conf_attch").focus();
		return false;
	}
	
	$('#add_attch_order_conf_dtl_btn').prop("disabled",true);
	Loading();
	var form_data = new FormData();
	form_data.append('mode', "add_order_conf_dtl");
	form_data.append('quotation_id', $("#qt_ord_ref_id").val());
	form_data.append("qt_order_conf_attch", document.getElementById('qt_order_conf_attch').files[0]);
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain+'app/order_confirm/',
		data: form_data,
		contentType: false,
		processData:false,
		success: function(resp){
			//console.log(resp);
			var resp=jQuery.parseJSON(resp);
			var response=resp.msg;
			if(response.trim() == "1") {
				toastr.success("DATA ADDED SUCCESSFULLY", "SUCCESS");
				$('#order_conf_dtl_modal').modal('hide');
				$('#add_attch_order_conf_dtl_btn').prop("disabled",false);
				load_order_confirm_datatable();
			}
			else if(response.trim() == "0") {
				toastr.warning("SOMETHING WRONG", "WARNING");
			}
			Unloading();
		}
	});
}
function open_payment_dtl(quotation_id,quotation_no){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/order_confirm/',
		data: { mode : "open_payment_dtl", quotation_id:quotation_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#pay_dtl_modal').modal('show');
			$('#head_pay_qt_no').html(quotation_no);
			$('#qt_pay_ref_id').val(quotation_id);
			$('#due_amt').val(resp.due_amt);
			
			/*$('#referenceno').val("");
			$('#paid_amt').attr("max",resp.due_amt).val("");*/
			Unloading();
			view_payment_dtl();
		}		 
	});
}
function add_pay_dtl(){
	if(!$("#payment_mode_id").val()){		
		toastr.warning("Choose Payment Mode", "ERROR");
		$("#payment_mode_id").focus();
		return false;
	}
	else if(!$("#paid_amt").val() || $("#paid_amt").val()=='0'){		
		toastr.warning("Enter Paid Amount", "ERROR");
		$("#paid_amt").focus();
		return false;
	}
	
	$('#add_pay_dtl_btn').prop("disabled",true);
	Loading();
	var form_data = new FormData();
	form_data.append('mode', "add_pay_dtl");
	form_data.append('quotation_id', $("#qt_pay_ref_id").val());
	form_data.append('payment_mode_id', $("#payment_mode_id").val());
	form_data.append('referenceno', $("#referenceno").val());
	form_data.append('paid_amt', $("#paid_amt").val());
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain+'app/order_confirm/',
		data: form_data,
		contentType: false,
		processData:false,
		success: function(resp){
			//console.log(resp);
			var resp=jQuery.parseJSON(resp);
			var response=resp.msg;
			if(response.trim() == "1") {
				toastr.success("DATA ADDED SUCCESSFULLY", "SUCCESS");
				//$('#pay_dtl_modal').modal('hide');
				$('#add_pay_dtl_btn').prop("disabled",false);
				$("#payment_mode_id").val("");
				$("#referenceno").val("");
				$("#paid_amt").val("");
				
				view_payment_dtl();
				load_order_confirm_datatable();
			}
			else if(response.trim() == "0") {
				toastr.warning("SOMETHING WRONG", "WARNING");
			}
			Unloading();
		}
	});
}
function view_payment_dtl(){
	var quotation_id = $('#qt_pay_ref_id').val();
	
	$("#pay-dtl-modal-datatable").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[5, 10, 20, -1], [5, 10, 20,"All"]],
		"iDisplayLength": 5,
		"sAjaxSource": root_domain + crm_domain+'app/order_confirm/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "view_payment_dtl" }, { "name": "quotation_id", "value": quotation_id }  );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
	// validate the comment form when it is submitted 
	
	//Check Due Qt Payment
	upd_qt_due_amt();
}
function upd_qt_due_amt(){
	var quotation_id = $('#qt_pay_ref_id').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/order_confirm/',
		data: { mode : "open_payment_dtl", quotation_id:quotation_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#due_amt').val(resp.due_amt);
			if(resp.due_amt==0){
				$('#entry_pay_dtl_modal_div').hide();
			}
			else{
				$('#entry_pay_dtl_modal_div').show();
			}
			Unloading();
		}		 
	});
}
function open_approv_payment(quot_paytrn_id,quotation_no){
	$('#preview_approval_hist_modal').modal('show');
	$('#apprv_ref_no').html(quotation_no);
	$('#ref_quotation_id').val(quot_paytrn_id);
	load_pay_hist_datatable();
}
function add_apprv_hist(){
	
	var form_data = {
		mode:"add_apprv_hist",
		assign_user_ids:$('#assign_user_ids').val(),
		approve_status:$('#approve_status').val(),
		approve_remark:$('#approve_remark').val(),
		quot_paytrn_id:$('#ref_quotation_id').val()
	};
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/order_confirm/',
		data: form_data,
		success: function(response)
		{
			$('#assign_user_ids').select2("val","");
			$('#approve_status').select2("val","0");
			$('#approve_remark').val("");
			load_pay_hist_datatable();
			view_payment_dtl();
			load_order_confirm_datatable();
			Unloading();
		}
	});	
}
function load_pay_hist_datatable(){
	var quot_paytrn_id = $('#ref_quotation_id').val();
	
	$("#sales-order-history-datatable").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !"
		},
		"aLengthMenu": [[5, 10, 20, -1], [5, 10, 20,"All"]],
		"iDisplayLength": 5,
		"sAjaxSource": root_domain + crm_domain + 'app/order_confirm/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "load_pay_hist_datatable" }, { "name": "quot_paytrn_id", "value": quot_paytrn_id }  );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
	// validate the comment form when it is submitted  
}
function open_po_approv_payment(quotation_id,quotation_no){
	$('#preview_po_approval_hist_modal').modal('show');
	$('#apprv_po_ref_no').html(quotation_no);
	$('#ref_ord_id').val(quotation_id);
	load_po_hist_datatable();
	load_party_po_dtl();
}
function add_po_apprv_hist(){
	
	var form_data = {
		mode:"add_po_apprv_hist",
		approve_status:$('#po_approve_status').val(),
		approve_remark:$('#po_approve_remark').val(),
		quotation_id:$('#ref_ord_id').val()
	};
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/order_confirm/',
		data: form_data,
		success: function(response)
		{
			$('#po_approve_status').select2("val","0");
			$('#po_approve_remark').val("");
			load_po_hist_datatable();
			load_order_confirm_datatable();
			Unloading();
		}
	});	
}
function load_po_hist_datatable(){
	var quotation_id = $('#ref_ord_id').val();
	
	$("#order-po-history-datatable").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bDestroy": true,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[5, 10, 20, -1], [5, 10, 20,"All"]],
		"iDisplayLength": 5,
		"sAjaxSource": root_domain + crm_domain + 'app/order_confirm/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "load_po_hist_datatable" }, { "name": "quotation_id", "value": quotation_id }  );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
	// validate the comment form when it is submitted  
	
}
function load_state(parentid,control,val1)
{	
	$.ajax({
		type: "POST",
		url: root_domain+'app/customer/',
		data: { mode : "load_state",  id : parentid},
		success: function(responce){
			//console.log(responce);
			$('#'+control).html(responce);
			$("#"+control).select2("val",val1);
		}
	});
	
}
function load_city(parentid,control,val1)
{	
	//alert(parentid);
	$.ajax({
		type: "POST",
		url: root_domain+'app/vender/',
		data: { mode : "load_city",  id : parentid},
		success: function(responce){
			//console.log(responce);
			//alert(responce);
			$('#'+control).html(responce);
			$("#"+control).select2("val",val1);
		}
	});
	
}
function load_party_po_dtl(){
	var quotation_id = $('#ref_ord_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain+'app/order_confirm/',
		data: { mode : "load_party_po_dtl", quotation_id:quotation_id },
		success: function(resp){
			//console.log(resp);
			var resp=JSON.parse(resp);
			$('#mod_po_comp_div_sec').html(resp.mod_po_comp_div_sec);
		}		 
	});
}