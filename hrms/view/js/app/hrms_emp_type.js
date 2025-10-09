var datatable;
$(document).ready(function () {
	show_list_data();  

	// validate vendor add form on keyup and submit
	$("#emp_type_add").validate({
		rules: {
			employee_type_name: {
				required: true,
				minlength: 2
			}
		},
		messages: {
			employee_type_name: {
				required: "Enter Employee Type Name",
				minlength: "Your Employee Type Name must consist of at least 2 characters"
			}
		}
	});
	// validate vendor edit form on keyup and submit
	$("#FormEditEmpType").validate({
		rules: {
			employee_type_name: {
				required: true,
				minlength: 2
			}
		},
		messages: {
			employee_type_name: {
				required: "Enter Employee Type Name",
				minlength: "Your Employee Type Name must consist of at least 2 characters"
			}
		}
	});

});
$("#emp_type_add").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#emp_type_add").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var token = $("#token").val();
	var form_data = {
		employee_type_name: $("#employee_type_name").val(),
		model: $("#model").val(),
		token: token,
		mode: $("#mode").val(),
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/hrms_emp_type/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var data = JSON.parse(response);
			var response=data.res;
			if (response.trim() == '1') {
				toastr.success("EMPLOYEE TYPE ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				show_list_data();
			}
			else if (response.trim() == '2') {
				toastr.success("Employee Type ADDED SUCCESSFULLY", "SUCCESS");
				$('#emp_type_add').trigger('reset');
				Unloading();
			}
			else if (response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (response.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				$('#emp_type_add').trigger('reset');
				Unloading();
			}
			$('#emp_type_add').trigger('reset');
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
//var editReq = null;
$("#FormEditEmpType").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#FormEditEmpType").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var form_data = {
		eid: $("#edit_id").val(),
		employee_type_name: $("#edit_employee_type_name").val(),
		token: $("#edit_token").val(),
		mode: 'edit',
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/hrms_emp_type/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var data = JSON.parse(response);
			var response=data.res;
			if (response.trim() == '1') {
				toastr.success("EMPLOYEE TYPE UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				show_list_data();
			}
			else if (response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (response.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();
			}
			$("#ModalEditEmpType").modal("hide");
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});

function show_list_data() {
	
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
			"sAjaxSource": root_domain + hrms_domain + 'app/hrms_emp_type/',
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

function delete_catalog(id) {
	var r = confirm(" Are you want to delete ?");
	if (r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_emp_type/',
			data: { mode: "delete", token: $("#token").val(), eid: id },
			success: function (response) {
				if (response.trim() == "1") {
					toastr.success("EMPLOYEE TYPE DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					show_list_data();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
			}
		});
	}
}
function edit_test(id) {
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_emp_type/',
		data: { mode: "preedit", id: id },
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			$("#ModalEditEmpType").modal("show");
			$("#edit_id").val(id);
			$("#edit_employee_type_name").val(obj.employee_type_name);
			$("#edit_status").select2('val',obj.status);
			Unloading();
			show_list_data();
		}
	});
}
function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_emp_type/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("EMPLOYEE TYPE STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			show_list_data();
		}
	});
	Unloading();
}
