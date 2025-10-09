$(document).ready(function() {
	load_datatable();
	show_data();	
	// validate vendor add form on keyup and submit
	$("#payroll_salary_component_add").validate({
		rules: {
			salary_component_name: {
				required: true			
			},
			salary_abbr_value:{
				required : true	
			},
			salary_component_type:{
				required : true	
			},
			status:{
				required : true	
			}
		},
		messages: {
			salary_component_name: {
				required: "Enter Salary Component Name"		
			},
			salary_abbr_value:{
				required: "Enter Salary Component Abbr Value"
			},
			salary_component_type: {
				required: "Select Salary Component Type"		
			},
			status: {
				required: "Select Status"
			}
		}
	}); 
});
$("#payroll_salary_component_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#payroll_salary_component_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#save').prop("disabled",true);
	
	var form_data=new FormData(this);

	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/payroll_salary_component/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("PAYROLL SALARY COMPONENT ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + hrms_domain + 'payroll_salary_component_list';
			}else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}else if(arr.msg == '-1'){
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#save').prop("disabled",false);
			$('#payroll_salary_component_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});		
});

function delete_payroll_salary_component(id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_salary_component/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);
				if(arr.msg == "1") {
					toastr.success("PAYROLL SALARY COMPONENT DELETE SUCCESSFULLY", "SUCCESS");
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
		"sAjaxSource": root_domain + hrms_domain + 'app/payroll_salary_component/',
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
		url: root_domain + hrms_domain + 'app/payroll_salary_component/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("PAYROLL SALARY COMPONENT CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			load_datatable();
		}
	});
	Unloading();
}

function show_data()
{
	var psc_id = $('#eid').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/payroll_salary_component/',
		data: { mode : "load_tempoutward", psc_id:psc_id },
		success: function(resp){
			$('#payroll_accountsdata').html(resp);				
			Unloading();
		}	
	});
}

function add_field()
{
	if($("#payroll_account_company_id").val()==="")
	{		
		toastr.warning("Select Company", "ERROR")
		return false;
	}
	if($("#payroll_account_id").val()==="")
	{		
		toastr.warning("Select Account", "ERROR")
		return false;
	}
	Loading();	
	$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_salary_component/',
			data: { 
				mode : "fieldadd", 
				eid : $("#eid").val(), 
				edit_id:$("#edit_id").val(),
				payroll_account_company_id:$("#payroll_account_company_id").val(),
				payroll_account_id:$("#payroll_account_id").val()
			},
			success: function(response)
			{
				$("#payroll_account_company_id").select2("val","");
				$("#payroll_account_id").select2("val","");
				$('#addrow').val('Add');
				Unloading();
				show_data();
			}
		});
}

function edit_data(id)
{
	Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_salary_component/',
			data: { mode : "preedit",  id : id},
			success: function(response)
			{
				var data = jQuery.parseJSON(response);
				$("#payroll_account_company_id").select2('val',data.payroll_account_company_id);
				$("#payroll_account_id").select2('val',data.payroll_account_id);
				$("#edit_id").val(id);
				$('#addrow').val('Update');
				Unloading();
			}
		});
}

function delete_data(id)
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading();
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_salary_component/',
			data: { mode : "delete_data",  eid : id},
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					show_data();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}