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
			"sEmptyTable": "NO DEPARTMENT ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain + hrms_domain + 'app/hrms_department/',
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
	$("#department_add").validate({
		rules: {
			department_name: {
				required: true,
				minlength: 5
			}
		},
		messages: {
			department_name: {
				required: "Enter Department Name",
				minlength: "Your Department Name must consist of at least 5 characters"
			}
		}
	});
	// validate vendor edit form on keyup and submit
	$("#FormEditDepartment").validate({
		rules: {
			edit_department_name: {
				required: true,
				minlength: 5
			}
		},
		messages: {
			edit_department_name: {
				required: "Enter Department Name",
				minlength: "Your Department Name must consist of at least 5 characters"
			}
		}
	});

});
$("#department_add").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#department_add").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var token = $("#token").val();
	var form_data = {
		department_name: $("#department_name").val(),
		status: $("#status").val(),
		model: $("#model").val(),
		token: token,
		mode: $("#mode").val(),
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/hrms_department/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			response = obj.res;
			if (response.trim() == '1') {
				toastr.success("DEPARTMENT ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				datatable.fnReloadAjax();
			}
			else if (response.trim() == '2') {
				toastr.success("DEPARTMENT ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-example-modal-department").modal("hide");
				$('#department_add').trigger('reset');
				Unloading();
			}
			else if (response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (response.trim() == '-1') {
				$("#bs-example-modal-department").modal("hide");
				toastr.info("ALREADY EXISTS", "INFO")
				$("#bs-example-modal-department").modal("hide");
				$('#department_add').trigger('reset');
				Unloading();
			}
			$('#department_add').trigger('reset');
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
//var editReq = null;
$("#FormEditDepartment").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#FormEditDepartment").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var form_data = {
		eid: $("#edit_id").val(),
		department_name: $("#edit_department_name").val(),
		status: $("#edit_status").val(),
		token: $("#edit_token").val(),
		mode: 'edit',
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/hrms_department/',
		type: "POST",
		data: form_data,
		success: function (response) {
			if (response.trim() == '1') {
				toastr.success("DEPARTMENT UPDATED SUCCESSFULLY", "SUCCESS");
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
			$("#ModalEditDepartment").modal("hide");
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
function delete_catalog(id) {
	var r = confirm(" Are you want to delete ?");
	if (r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + hrms_domain + 'app/hrms_department/',
			data: { mode: "delete", token: $("#token").val(), eid: id },
			success: function (response) {
				if (response.trim() == "1") {
					toastr.success("DEPARTMENT DELETE SUCCESSFULLY", "SUCCESS");
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
function edit_test(id) {
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_department/',
		data: { mode: "preedit", id: id },
		success: function (response) {
			console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditDepartment").modal("show");
			$("#edit_id").val(id);
			$("#edit_department_name").val(obj.department_name);
			$("#edit_status").select2('val',obj.status);
			Unloading();
		}
	});
}
function changeStatus(id,p_status)
{
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_department/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("DEPARTMENT CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			datatable.fnReloadAjax();
		}
	});
	Unloading();
}
