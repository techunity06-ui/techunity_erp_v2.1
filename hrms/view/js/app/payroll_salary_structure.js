$(document).ready(function() {
	load_datatable();
	show_earnings_data();
	show_deductions_data();		
	// validate vendor add form on keyup and submit
	$("#payroll_salary_structure_add").validate({
		rules: {
			salary_structure_name: {
				required: true			
			},
			salary_structure_status:{
				required : true	
			},
			payroll_frequency:{
				required : true	
			},
			status:{
				required : true	
			}
		},
		messages: {
			salary_structure_name: {
				required: "Enter Salary Structure Name"		
			},
			salary_structure_status:{
				required: "Select Structure Status"
			},
			payroll_frequency: {
				required: "Select Salary Payroll Frequency"		
			},
			status: {
				required: "Select Status"
			}
		}
	}); 
});
$("#payroll_salary_structure_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#payroll_salary_structure_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#save').prop("disabled",true);
	
	var form_data=new FormData(this);

	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/payroll_salary_structure/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("PAYROLL SALARY STRUCTURE ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + hrms_domain + 'payroll_salary_structure_list';
			}else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}else if(arr.msg == '-1'){
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#save').prop("disabled",false);
			$('#payroll_salary_structure_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});		
});

function delete_payroll_salary_structure(id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_salary_structure/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);
				if(arr.msg == "1") {
					toastr.success("PAYROLL SALARY STRUCTURE DELETE SUCCESSFULLY", "SUCCESS");
					load_datatable();
					Unloading();
				}else if(arr.msg == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}

function reload_data()
{
	load_datatable();
}

function load_datatable()
{
	var data=$('input[name=report]:Checked').val();
	var date=$('#rep_date').val();
	
	datatable = $("#dynamic-table").dataTable({
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
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + hrms_domain + 'app/payroll_salary_structure/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },{ "name": "report", "value": data },{ "name": "date", "value": date });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/payroll_salary_structure/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("PAYROLL SALARY STRUCTURE CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			load_datatable();
		}
	});
	Unloading();
}

function getComponentEarning(){
	var comp_earn_id = $("#payroll_component_name_earnings").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/payroll_salary_structure/',
		data: { mode : "payroll_salary_structure_earnings",comp_earn_id:comp_earn_id},
		success: function(response){
			var data = jQuery.parseJSON(response);
			$("#payroll_component_abbr_earn").val(data.salary_abbr_value);
			$("#payroll_component_abbr_earnings").val(data.salary_abbr_value);
			$("#payroll_component_amount_earnings").val(data.salary_component_amount);
			if(data.statistical_component_flag == 'Yes'){
				$("#payroll_component_statistic_flag_earnings").prop( "checked", true );
			}else{
				$("#payroll_component_statistic_flag_earnings").prop( "checked", false );
			}
			if(data.salary_component_amount == '0.00'){
				$("#payroll_component_formula_earnings").val(data.salary_component_amount_formula);
			}else{
				$("#payroll_component_formula_earnings").val('');
			}				
			Unloading();
		}		
	});
}

function getComponentDeduction(){
	var comp_dedu_id = $("#payroll_component_name_deductions").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/payroll_salary_structure/',
		data: { mode : "payroll_salary_structure_deductions",comp_dedu_id:comp_dedu_id},
		success: function(response){
			var data = jQuery.parseJSON(response);
			$("#payroll_component_abbr_dedu").val(data.salary_abbr_value);
			$("#payroll_component_abbr_deductions").val(data.salary_abbr_value);
			$("#payroll_component_amount_deductions").val(data.salary_component_amount);
			if(data.statistical_component_flag == 'Yes'){
				$("#payroll_component_statistic_flag_deductions").prop( "checked", true );
			}else{
				$("#payroll_component_statistic_flag_deductions").prop( "checked", false );
			}
			if(data.salary_component_amount == '0.00'){
				$("#payroll_component_formula_deductions").val(data.salary_component_amount_formula);
			}else{
				$("#payroll_component_formula_deductions").val('');
			}				
			Unloading();
		}		
	});
}

function show_earnings_data()
{
	var psse_id = $('#eid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/payroll_salary_structure/',
		data: { mode : "load_earnings_ward", psse_id:psse_id },
		success: function(resp){
			$('#payroll_earnings_data').html(resp);				
			Unloading();
		}	
	});
}

function add_earnings_field()
{
	if($("#payroll_component_name_earnings").val()==="")
	{		
		toastr.warning("Select Component Name Earnings", "ERROR")
		return false;
	}
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_salary_structure/',
			data: { 
				mode : "field_earnings_add", 
				eid : $("#eid").val(), 
				edit_id:$("#edit_id").val(),
				payroll_component_name_earnings:$("#payroll_component_name_earnings").val(),
				payroll_component_abbr_earnings:$("#payroll_component_abbr_earnings").val(),
				payroll_component_amount_earnings:$("#payroll_component_amount_earnings").val(),
				payroll_component_statistic_flag_earnings:$("#payroll_component_statistic_flag_earnings").val(),
				payroll_component_formula_earnings:$("#payroll_component_formula_earnings").val()
			},
			success: function(response)
			{
				$("#payroll_component_name_earnings").select2("val","");
				$("#payroll_component_abbr_earnings").val();
				$("#payroll_component_amount_earnings").val();
				$("#payroll_component_statistic_flag_earnings").prop( "checked", false );
				$("#payroll_component_formula_earnings").val();
				$('#addearningsrow').val('Add');
				Unloading();
				show_earnings_data();
			}
		});
}

function edit_earnings_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_salary_structure/',
			data: { mode : "pre_earnings_edit",  id : id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#payroll_component_name_earnings").select2('val',data.payroll_component_name_earnings);
				$("#payroll_component_abbr_earnings").val(data.payroll_component_abbr_earnings);
				$("#payroll_component_amount_earnings").val(data.payroll_component_amount_earnings);
				if(data.payroll_component_statistic_flag_earnings == 'Yes'){
					$("#payroll_component_statistic_flag_earnings").prop( "checked", true );
				}else{
					$("#payroll_component_statistic_flag_earnings").prop( "checked", false );
				}
				$("#payroll_component_formula_earnings").val(data.payroll_component_formula_earnings);
				$("#edit_id").val(id);
				$('#addearningsrow').val('Update');
				Unloading();
			}
		});
}

function delete_earnings_data(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_salary_structure/',
			data: { mode : "delete_earnings_data",  eid : id},
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_earnings_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}

function show_deductions_data()
{
	var pssd_id = $('#eid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/payroll_salary_structure/',
		data: { mode : "load_deductions_ward", pssd_id:pssd_id },
		success: function(resp){
			$('#payroll_deductions_data').html(resp);				
			Unloading();
		}	
	});
}

function add_deductions_field()
{
	if($("#payroll_component_name_deductions").val()==="")
	{		
		toastr.warning("Select Component Name Deductions", "ERROR")
		return false;
	}
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_salary_structure/',
			data: { 
				mode : "field_deductions_add", 
				eid : $("#eid").val(), 
				edit_id:$("#edit_id").val(),
				payroll_component_name_deductions:$("#payroll_component_name_deductions").val(),
				payroll_component_abbr_deductions:$("#payroll_component_abbr_deductions").val(),
				payroll_component_amount_deductions:$("#payroll_component_amount_deductions").val(),
				payroll_component_statistic_flag_deductions:$("#payroll_component_statistic_flag_deductions").val(),
				payroll_component_formula_deductions:$("#payroll_component_formula_deductions").val()
			},
			success: function(response)
			{
				$("#payroll_component_name_deductions").select2("val","");
				$("#payroll_component_abbr_deductions").val();
				$("#payroll_component_amount_deductions").val();
				$("#payroll_component_statistic_flag_deductions").prop( "checked", false );
				$("#payroll_component_formula_deductions").val();
				$('#adddeductionsrow').val('Add');
				Unloading();
				show_earnings_data();
			}
		});
}

function edit_deductions_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_salary_structure/',
			data: { mode : "pre_deductions_edit",  id : id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#payroll_component_name_deductions").select2('val',data.payroll_component_name_deductions);
				$("#payroll_component_abbr_deductions").val(data.payroll_component_abbr_deductions);
				$("#payroll_component_amount_deductions").val(data.payroll_component_amount_deductions);
				if(data.payroll_component_statistic_flag_deductions == 'Yes'){
					$("#payroll_component_statistic_flag_deductions").prop( "checked", true );
				}else{
					$("#payroll_component_statistic_flag_deductions").prop( "checked", false );
				}
				$("#payroll_component_formula_deductions").val(data.payroll_component_formula_deductions);
				$("#edit_id").val(id);
				$('#adddeductionsrow').val('Update');
				Unloading();
			}
		});
}

function delete_deductions_data(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_salary_structure/',
			data: { mode : "delete_deductions_data",  eid : id},
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_deductions_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}