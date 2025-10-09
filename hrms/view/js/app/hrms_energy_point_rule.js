$(document).ready(function() {
	load_datatable();
	// validate vendor add form on keyup and submit
	$("#hrms_energy_point_rule_add").validate({
		rules: {
			energy_rule_name: {
				required: true			
			},
			reference_document_type_id:{
				required : true	
			},
			energy_points:{
				required : true	
			},
			status:{
				required : true	
			}
		},
		messages: {
			energy_rule_name: {
				required: "Enter Energy Rule Name"		
			},
			reference_document_type_id:{
				required: "Select Reference Document Type"
			},
			energy_points: {
				required: "Enter Energy Points"		
			},
			status: {
				required: "Select Status"
			}
		}
	}); 
});
$("#hrms_energy_point_rule_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#hrms_energy_point_rule_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#save').prop("disabled",true);
	
	var form_data=new FormData(this);

	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/hrms_energy_point_rule/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("HRMS ENERGY POINT RULE ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + hrms_domain + 'hrms_energy_point_rule_list';
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

function delete_hrms_energy_point_rule(id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_energy_point_rule/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);
				if(arr.msg == "1") {
					toastr.success("HRMS ENERGY POINT RULE DELETE SUCCESSFULLY", "SUCCESS");
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
		"sAjaxSource": root_domain + hrms_domain + 'app/hrms_energy_point_rule/',
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
		url: root_domain + hrms_domain + 'app/hrms_energy_point_rule/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("HRMS ENERGY POINT RULE CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			load_datatable();
		}
	});
	Unloading();
}

function load_user_fields(reference_id,control,val1,val2)
{	
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain +'app/hrms_energy_point_rule/',
		data: { mode : "load_user_fields",  id : reference_id, val1: val1},
		success: function(response){
			$('#energy_user_field_id').html(response);
			$('#energy_user_field_id').select2('val',val1);
		}
	});
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain +'app/hrms_energy_point_rule/',
		data: { mode : "load_multiplier_fields",  id : reference_id, val2: val2},
		success: function(response){
			$('#energy_multiplier_field_id').html(response);
			$('#energy_multiplier_field_id').select2('val',val2);
		}
	});
}