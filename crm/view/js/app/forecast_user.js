$(document).ready(function() {
	load_forecast_datatable(); 
	
	// validate vendor add form on keyup and submit
	$("#forecast_user_add").validate({
		rules: {
			forecast_date: {
				required: true
			},
			branch_id: {
				required: true
			},
			financial_year_id: {
				required: true
			},
			forecast_type: {
				required: true
			}
		},
		messages: {
			forecast_date: {
				required: "Select Forecast date"
			},
			branch_id: {
				required: "Select Branch"
			},
			financial_year_id: {
				required: "Select Financial Year"
			},
			forecast_type: {
				required: "Select Forecast Period"
			}
		}
	}); 
	
});
$("#forecast_user_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#forecast_user_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading();	
	// $(this).attr("disabled","disabled");	
	$("#submit_btn").prop("disabled",true);		
	
	var form_data = new FormData(this);
	form_data.append('financial_year_id', $("#financial_year_id").val());
	form_data.append('forecast_type', $("#forecast_type").val());
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain + 'app/forecast_user/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);
			var resp=JSON.parse(response);
			var msg=resp.msg;
			if(msg.trim() == '1') {				
				toastr.success("FORECAST ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + crm_domain +'forecast_user_list';
			}
			else if(msg.trim() == '2') {				
				toastr.success("FORECAST UPDATED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + crm_domain + 'forecast_user_list';
			}
			else if(msg.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			else if(msg.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO");		
			}
			$("#submit_btn").prop("disabled",false);	
			Unloading();
		}
	});
	
});
function delete_forecast(forecast_id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/forecast_user/',
			data: { mode : "delete", forecast_id:forecast_id },
			success: function(resnse)
			{
				if(resnse.trim() == "1") {
					toastr.success("FORECAST DELETED SUCCESSFULLY", "SUCCESS");
					load_forecast_datatable();
				}
				else if(resnse.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
				Unloading();
			}
		});	
	}
	
}

function load_forecast_datatable(){
	var branch_id= $('#branch_id').val();
	var forecast_type= $('#forecast_type').val();
	var f_user_id= $('#f_user_id').val();
	$("#forecast-datatable").dataTable({
		"bStateSave": true,
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
		"aLengthMenu": [[-1, 10, 20, 50, 100], ['All', 10, 20, 50, 100]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + crm_domain +'app/forecast_user/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },
				{ "name": "branch_id", "value": branch_id },
				{ "name": "f_user_id", "value": f_user_id },
				{ "name": "forecast_type", "value": forecast_type });
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
function get_branchwise_user(branch_id){
	var user_id = $('#fore_user_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/forecast_user/',
		data: { mode:"get_branchwise_user", branch_id:branch_id },
		success: function(response)
		{
			var resp=JSON.parse(response);
			$('#f_user_id').html(resp.html_resp);
			$('#f_user_id').select2('val',user_id);
		}
	});	
}
function show_data() {
	var eid = $('#eid').val();
	var forecast_type = $('#forecast_type').val();
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/forecast_user/',
		data: { mode : "show_data", forecast_user_id:eid, forecast_type:forecast_type },
		success: function(resp){
			//console.log(resp);
			$('.show_forecast_trndata').html(resp);
			Unloading();
		}		 
	}); 
}
function add_field(){
	if(!$("#branch_id").val()){		
		toastr.warning("Choose Branch", "ERROR");
		$("#branch_id").select2('focus');
		return false;
	}
	else if(!$("#f_user_id").val()){
		toastr.warning("Choose user", "ERROR");
		$("#f_user_id").focus();
		return false;
	}
	else if(!$("#target_amount").val()){
		toastr.warning("Enter Amount", "ERROR");
		$("#target_amount").focus();
		return false;
	}
	else if($("#target_amount").val() <= 0){
		toastr.warning("Amount must be greater than 0", "ERROR");
		$("#target_amount").focus();
		return false;
	}
	else if(!$("#target_qty").val()){
		toastr.warning("Enter Quantity", "ERROR");
		$("#target_qty").focus();
		return false;
	}
	else if($("#target_qty").val() <= 0){
		toastr.warning("Quantity must be greater than 0", "ERROR");
		$("#target_qty").focus();
		return false;
	}
	
	for (instance in CKEDITOR.instances) {
		CKEDITOR.instances[instance].updateElement();
	}
	
	var form_data = { 
		mode : "add_field",
		edit_id:$("#edit_id").val(),
		eid:$("#eid").val(),
		branch_id:$("#branch_id").val(), 
		f_user_id:$("#f_user_id").val(), 
		f_product:$("#f_product").val(), 
		forecast_month:$("#forecast_month").val(), 
		target_qty:$("#target_qty").val(), 
		target_amount:$("#target_amount").val(), 
		financial_year_id:$("#financial_year_id").val(), 
		forecast_base:$("#forecast_base").val(),
		forecast_type:$("#forecast_type").val()
	};
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/forecast_user/',
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {				
				toastr.success("FORECAST ADDED SUCCESSFULLY", "SUCCESS");
			}
			else if(response.trim() == '2') {				
				toastr.success("FORECAST UPDATED SUCCESSFULLY", "SUCCESS");
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			else if(response.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO");		
			}
			$("#forecast_month").select2("val","");
			$("#f_product").select2("val","");
			$("#target_qty").val("");
			$("#target_amount").val("");
			$("#edit_id").val("");
			$('#forecast_trn_btn').html('Add');
			Unloading();
			show_data();
		}
	});
}
function edit_trn_datas(forecast_user_trn_id){
	var branch_id = $('#branch_id').val();
	get_branchwise_user(branch_id);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain + 'app/forecast_user/',
		data: { mode:"edit_trn_datas", forecast_user_trn_id:forecast_user_trn_id},
		success: function(response)
		{
			var resp = jQuery.parseJSON(response)
			$("#forecast_month").select2("val",resp.forecast_month);
			$("#f_product").select2("val",resp.f_product);
			$("#target_amount").val(resp.target_amount);
			$("#target_qty").val(resp.target_qty);
			$("#edit_id").val(forecast_user_trn_id);
			$('#forecast_trn_btn').html('Update');
			// Unloading();
		}
	});
}
function load_f_period(){
	var f_by_id = $("#financial_year_id").find(':selected').attr("data-financial-type");
	var forecast_type = $('#forecast_type').val();
	
	if(f_by_id && forecast_type){
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/forecast_user/',
			data: { mode:"load_f_period", f_by_id:f_by_id, forecast_type:forecast_type },
			success: function(response)
			{
				//console.log(response);
				var resp=JSON.parse(response);
				$('#forecast_month').html(resp.html_resp);
				$("#forecast_month").select2("val","");
			}
		});	
	}
}
function copy_forecast(forecast_user_id){
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/forecast_user/',
		data: { mode:"copy_forecast", forecast_user_id:forecast_user_id },
		success: function(response)
		{
			//console.log(response);
			show_data();
		}
	});	
}
function open_approve_modal(forecast_user_id,forecast_no){
	$('#preview_forecast_approve_hist_modal').modal('show');
	$('#apprv_po_ref_no').html(forecast_no);
	$('#ref_ord_id').val(forecast_user_id);
	load_forecast_hist_datatable();
	load_forecast_dtl();
}
function load_forecast_hist_datatable(){
	var forecast_user_id = $('#ref_ord_id').val();
	
	$("#forecast-history-datatable").dataTable({
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
		"sAjaxSource": root_domain + crm_domain +'app/forecast_user/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "load_forecast_hist_datatable" }, { "name": "forecast_user_id", "value": forecast_user_id }  );
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
function load_forecast_dtl(){
	var forecast_user_id = $('#ref_ord_id').val();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/forecast_user/',
		data: { mode : "load_forecast_dtl", forecast_user_id:forecast_user_id },
		success: function(resp){
			var resp=JSON.parse(resp);
			$('#mod_forecast_div_sec').html(resp.mod_forecast_div_sec);
			$('#mod_forecast_pro_div_sec').html(resp.mod_forecast_pro_div_sec);
		}		 
	});
}


function add_forecast_apprv_hist(){
	
	var form_data = {
		mode:"add_forecast_apprv_hist",
		approve_status:$('#forecast_approve_status').val(),
		approve_remark:$('#forecast_approve_remark').val(),
		forecast_user_id:$('#ref_ord_id').val()
	};
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/forecast_user/',
		data: form_data,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				$('#forecast_approve_status').select2("val","0");
				$('#forecast_approve_remark').val("");
				toastr.success("APROOVE SUCCESSFULLY", "SUCCESS");
				load_forecast_hist_datatable();
				$('#preview_forecast_approve_hist_modal').modal('hide');
				//load_order_confirm_datatable();
				load_forecast_datatable();
				Unloading();
			}else{
				$('#forecast_approve_status').select2("val","0");
				$('#forecast_approve_remark').val("");
				toastr.success("REJECT SUCCESSFULLY", "SUCCESS");
				load_forecast_hist_datatable();
				$('#preview_forecast_approve_hist_modal').modal('hide');
				//load_order_confirm_datatable();
				load_forecast_datatable();
				Unloading();
			}
		}
	});	
}