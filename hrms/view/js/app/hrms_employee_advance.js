var datatable;
$(document).ready(function() {
	load_employee_advance_tbl();
	
	// validate add form on keyup and submit
	$("#employee_advance_add").validate({
		
		ignore:[],
		
		rules: {
			series_id: {
				required: true
			},
			employee_id: {
				required: true
			},
			advance_account_id: {
				required: true
			},
			mode_payment_id: {
				required: true,
			},
			posting_date: {
				required: true
			},
			purpose: {
				required: true
			},
			advance_amount: {
				required: true,
				number: true
			},
			pending_amount: {
				required: true,
				number: true
			},
			status: {
				required: true
			}
		}
	}); 
});

$("#employee_advance_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();

	if (!$("#employee_advance_add").valid()) {
		return false;
	}
	
	form.submitted = true;	
	
	
	Loading();	
	$(this).attr("disabled","disabled");		
	
	var form_data = new FormData(this);
	var token = $("#token").val();	
	
	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/hrms_employee_advance/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(resnse)
		{		
			var data = JSON.parse(resnse);
			var responsevalue=data.res;
			if(responsevalue.trim() == '1') {
				toastr.success("HRMS EMPLOYEE ADVANCE ADDED SUCCESSFULLY", "SUCCESS")
				$('#employee_advance_add').trigger('reset');		
				Unloading();
				window.location=root_domain + hrms_domain + 'hrms_employee_advance';
			}
			else if(responsevalue.trim() == '2') {
				toastr.success("HRMS EMPLOYEE ADVANCE UPDATED SUCCESSFULLY", "SUCCESS")
				$('#employee_advance_add').trigger('reset');					
				Unloading();
				window.location=root_domain + hrms_domain + 'hrms_employee_advance';
			}
			else if(responsevalue.trim() == '0') {
				toastr.error("SOMETHING WRONG", "ERROR")
				$('#employee_advance_add').trigger('reset');	
				Unloading();
			} else if(responsevalue.trim() == '1') {
				toastr.error("ENTER VALID DATA", "ERROR")
				$('#employee_advance_add').trigger('reset');	
				Unloading();
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function load_employee_advance_tbl() {
	
	var datatable = $("#dynamic-table").dataTable({
			"bStateSave" : true,
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
			"aLengthMenu": [[20, 50, 100, -1], [20, 50, 100,"All"]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain + hrms_domain + 'app/hrms_employee_advance/',
			"fnServerParams": function ( aoData ) {
				aoData.push({ "name": "mode", "value": "fetch" });
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}
function delete_record(id) {
	var r = confirm(" Are you want to delete ?");
	if (r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_employee_advance/',
			data: { mode: "delete", token: $("#token").val(), eid: id },
			success: function (response) {
				if (response.trim() == "1") {
					toastr.success("RECORD DELETED SUCCESSFULLY", "SUCCESS");
					// datatable.fnReloadAjax();
					Unloading();
					load_employee_advance_tbl();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
			}
		});
		Unloading();
	}
}
function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_employee_advance/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("HRMS EMPLOYEE ADVANCE STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			load_employee_advance_tbl();
		}
	});
	Unloading();
}