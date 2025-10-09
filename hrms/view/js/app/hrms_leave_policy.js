var datatable;
$(document).ready(function () {
	datatable = $("#dynamic-table").dataTable({
		"bStateSave": true,
		"bAutoWidth": false,
		"bFilter": true,
		"bSort": true,
		"bProcessing": true,
		"bServerSide": true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='" + root_domain + "img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO LEAVE POLICY ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + hrms_domain + 'app/hrms_leave_policy/',
		"fnServerParams": function (aoData) {
			aoData.push({ "name": "mode", "value": "fetch" });
		},
		"fnDrawCallback": function (oSettings) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder', 'Search');
	$('.dataTables_length select').addClass('form-control');
	// validate the comment form when it is submitted        

	// validate vendor add form on keyup and submit
	$("#leave_policy_add").validate({
		rules: {
			leave_type_id: {
				required: true
			},
			annual_allocation: {
				required: true
			},
			status: {
				required: true
			}
		},
		messages: {
			leave_type_id: {
				required: "Select Leave Policy"
			},
			annual_allocation: {
				required: "Enter Annual Allocation"
			},
			status: {
				required: "Select Status"
			}
		}
	});
	// validate vendor edit form on keyup and submit
	$("#FormEditLeavePolicy").validate({
		rules: {
			edit_leave_type_id: {
				required: true
			},
			edit_annual_allocation: {
				required: true
			},
			edit_status: {
				required: true
			}
		},
		messages: {
			edit_leave_type_id: {
				required: "Select Leave Policy"
			},
			edit_annual_allocation: {
				required: "Enter Annual Allocation"
			},
			edit_status: {
				required: "Select Status"
			}
		}
	});

});
$("#leave_policy_add").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#leave_policy_add").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var token = $("#token").val();
	var form_data = {
		leave_type_id: $("#leave_type_id").val(),
		annual_allocation: $("#annual_allocation").val(),
		status: $("#status").val(),
		model: $("#model").val(),
		token: token,
		mode: $("#mode").val(),
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/hrms_leave_policy/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			response = obj.res;
			if (response.trim() == '1') {
				toastr.success("LEAVE POLICY ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				datatable.fnReloadAjax();
			}
			else if (response.trim() == '2') {
				toastr.success("LEAVE POLICY ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-example-modal-hrms-leave-policy").modal("hide");
				$('#leave_type_id').append('<option value=' + obj.leave_type_id + '>' + obj.leave_type_id + '</option>');
				$("#leave_type_id").trigger('change')
				$('#leave_type_id').select2("val", obj.leave_type_id);
				$('#leave_policy_add').trigger('reset');
				Unloading();
			}
			else if (response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (response.trim() == '-1') {
				$("#bs-example-modal-hrms-leave-policy").modal("hide");
				toastr.info("ALREADY EXISTS", "INFO")
				$("#bs-example-modal-hrms-leave-policy").modal("hide");
				$('#leave_policy_add').trigger('reset');
				Unloading();
			}
			$('#leave_policy_add').trigger('reset');
			$('#leave_type_id').select2("val", leave_type_id);
			$("#status").select2("val", status);
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
//var editReq = null;
$("#FormEditLeavePolicy").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#FormEditLeavePolicy").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var form_data = {
		eid: $("#edit_id").val(),
		leave_type_id: $("#edit_leave_type_id").val(),
		annual_allocation: $("#edit_annual_allocation").val(),
		status: $("#edit_status").val(),
		token: $("#edit_token").val(),
		mode: 'edit',
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/hrms_leave_policy/',
		type: "POST",
		data: form_data,
		success: function (response) {
			if (response.trim() == '1') {
				toastr.success("LEAVE POLICY UPDATED SUCCESSFULLY", "SUCCESS");
				datatable.fnReloadAjax();
				Unloading();
			}
			else if (response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (response.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();
			}
			$("#ModalEditLeavePolicy").modal("hide");
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
function delete_leave_policy(id) {
	var r = confirm(" Are you want to delete ?");
	if (r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_leave_policy/',
			data: { mode: "delete", token: $("#token").val(), eid: id },
			success: function (response) {
				if (response.trim() == "1") {
					toastr.success("LEAVE POLICY DELETE SUCCESSFULLY", "SUCCESS");
					datatable.fnReloadAjax();
					Unloading();
				}
				else if (response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}
			}
		});
	}
}
function edit_leave_policy(id) {
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_leave_policy/',
		data: { mode: "preedit", id: id },
		success: function (response) {
			console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditLeavePolicy").modal("show");
			$("#edit_id").val(id);
			$("#edit_leave_type_id").select2("val", obj.leave_type_id);
			$("#edit_annual_allocation").val(obj.annual_allocation);
			$("#edit_status").select2("val", obj.status);
			Unloading();
		}
	});
}
function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_leave_policy/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("LEAVE POLICY CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			datatable.fnReloadAjax();
		}
	});
	Unloading();
}