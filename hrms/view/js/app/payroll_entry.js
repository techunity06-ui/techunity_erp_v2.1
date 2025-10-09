$(document).ready(function() {
	load_datatable();

	// validate vendor add form on keyup and submit
	$("#payroll_entry_add").validate({
		rules: {
			payroll_posting_date: {
				required: true			
			},
			payroll_frequency:{
				required : true	
			},
			payroll_start_date:{
				required : true	
			},
			payroll_end_date:{
				required : true	
			},
			status:{
				required : true	
			}
		},
		messages: {
			payroll_posting_date: {
				required: "Enter Payroll Posting Date"		
			},
			payroll_frequency:{
				required: "Select Payroll Frequency"
			},
			payroll_start_date: {
				required: "Enter Assignment Start Date"		
			},
			payroll_end_date:{
				required : "Enter Assignment End Date"
			},
			status: {
				required: "Select Status"
			}
		}
	}); 
});
$("#payroll_entry_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#payroll_entry_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#save').prop("disabled",true);
	
	var form_data=new FormData(this);

	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/payroll_entry/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("PAYROLL ENTRY ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + hrms_domain + 'payroll_entry_list';
			}else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}else if(arr.msg == '-1'){
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#save').prop("disabled",false);
			$('#payroll_entry_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});		
});

function delete_payroll_entry(id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/payroll_entry/',
			data: { mode : "delete",  eid : id },
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);
				if(arr.msg == "1") {
					toastr.success("PAYROLL ENTRY DELETE SUCCESSFULLY", "SUCCESS");
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
		"sAjaxSource": root_domain + hrms_domain + 'app/payroll_entry/',
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
		url: root_domain + hrms_domain + 'app/payroll_entry/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("PAYROLL SALARY STRUCTURE ASSIGNMENT CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			load_datatable();
		}
	});
	Unloading();
}