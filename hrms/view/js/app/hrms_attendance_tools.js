var datatable;
$(document).ready(function() {
	load_attendance_tools_tbl();
	
	// validate add form on keyup and submit
	$("#attendance_tools_add").validate({
		
		ignore:[],
		
		rules: {
			attendance_date: {
				required: true
			},
			zone_id: {
				required: true
			},
			branch_id: {
				required: true
			},
			department_id: {
				required: true,
			},
			approval_status_id: {
				required: true
			},
			status: {
				required: true
			},
			"employee_ids[]": "required"
		}
	}); 
});

$("#attendance_tools_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();

	if (!$("#attendance_tools_add").valid()) {
		return false;
	}
	
	form.submitted = true;	
	
	
	Loading();	
	$(this).attr("disabled","disabled");		
	
	var form_data = new FormData(this);
	var token = $("#token").val();	
	
	$.ajax({
		cache:false,
		url: root_domain + hrms_domain + 'app/hrms_attendance_tools/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(resnse)
		{		
			var data = JSON.parse(resnse);
			var responsevalue=data.res;
			if(responsevalue.trim() == '1') {
				toastr.success("HRMS ATTENDANCE TOOL ADDED SUCCESSFULLY", "SUCCESS")
				$('#attendance_tools_add').trigger('reset');		
				Unloading();
				window.location=root_domain + hrms_domain + 'hrms_attendance_tools';
			}
			else if(responsevalue.trim() == '2') {
				toastr.success("HRMS ATTENDANCE TOOL UPDATED SUCCESSFULLY", "SUCCESS")
				$('#attendance_tools_add').trigger('reset');					
				Unloading();
				window.location=root_domain + hrms_domain + 'hrms_attendance_tools';
			}
			else if(responsevalue.trim() == '0') {
				toastr.error("SOMETHING WRONG", "ERROR")
				$('#attendance_tools_add').trigger('reset');	
				Unloading();
			} else if(responsevalue.trim() == '1') {
				toastr.error("ENTER VALID DATA", "ERROR")
				$('#attendance_tools_add').trigger('reset');	
				Unloading();
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function load_attendance_tools_tbl() {
	
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
			"sAjaxSource": root_domain + hrms_domain + 'app/hrms_attendance_tools/',
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

function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_attendance_tools/',
		data: { mode : "change_status", eid : id,p_status:p_status },
		success: function(response)
		{
			toastr.success("HRMS ATTENDANCE TOOL STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			load_attendance_tools_tbl();
		}
	});
	Unloading();
}

function delete_record(id) {
	var r = confirm(" Are you want to delete ?");
	if (r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_attendance_tools/',
			data: { mode: "delete", token: $("#token").val(), eid: id },
			success: function (response) {
				if (response.trim() == "1") {
					toastr.success("RECORD DELETED SUCCESSFULLY", "SUCCESS");
					// datatable.fnReloadAjax();
					Unloading();
					load_attendance_tools_tbl();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
			}
		});
		Unloading();
	}
}

function changeZone(val) {
	$('#branch_id').select2('val', '');
	$('#employee_ids').select2('val', '');
	$('#employee_ids').html('');

	if(val != '') {
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_attendance_tools/',
			data: { mode : "load_branch", val : val},
			success: function(response){
				$('#branch_id').html(response);	
			}
		});
	}
}

function changeBranch(val) {
	$('#employee_ids').select2('val', '');
	$('#employee_ids').html('');
	
	if(val != '') {
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_attendance_tools/',
			data: { mode : "load_emp", val : val},
			success: function(response){
				$('#employee_ids').html(response);	
			}
		});
	}
}