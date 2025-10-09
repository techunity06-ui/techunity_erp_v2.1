var datatable;
$(document).ready(function () {
	show_list_data();  

	// validate vendor add form on keyup and submit
	$("#payroll_emp_exemption_sub_category_add").validate({
		rules: {
			category_name: {
				required: true,
				minlength: 2
			},
			parent_id: {
				required: true
			}
		},
		messages: {
			category_name: {
				required: "Enter Tax Exemption Category Name",
				minlength: "Your Tax Exemption Name must consist of at least 2 characters"
			},
			parent_id: {
				required: "Select Parent Category",
			}
		}
	});
	// validate vendor edit form on keyup and submit
	$("#FormEditEmpExemptionSubCategory").validate({
		rules: {
			category_name: {
				required: true,
				minlength: 2
			},
			parent_id: {
				required: true
			}
		},
		messages: {
			category_name: {
				required: "Enter Tax Exemption Name",
				minlength: "Your Tax Exemption Name must consist of at least 2 characters"
			},
			parent_id: {
				required: "Select Parent Category",
			}
		}
	});

});
$("#payroll_emp_exemption_sub_category_add").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#payroll_emp_exemption_sub_category_add").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var token = $("#token").val();
	var form_data = {
		parent_id: $("#parent_id").val(),
		category_name: $("#category_name").val(),
		max_exemption_amount: $("#max_exemption_amount").val(),
		model: $("#model").val(),
		token: token,
		mode: $("#mode").val(),
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/payroll_emp_exemption_subcategory/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var data = JSON.parse(response);
			var response=data.res;
			if (response.trim() == '1') {
				toastr.success("EMPLOYEE TAX EXEMPTION SUB CATEGORY ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				show_list_data();
			}
			else if (response.trim() == '2') {
				toastr.success("EMPLOYEE TAX EXEMPTION SUB CATEGORY ADDED SUCCESSFULLY", "SUCCESS");
				$('#payroll_emp_exemption_sub_category_add').trigger('reset');
				Unloading();
			}
			else if (response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if (response.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				$('#payroll_emp_exemption_sub_category_add').trigger('reset');
				Unloading();
			}
			$('#payroll_emp_exemption_sub_category_add').trigger('reset');
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

});
//var editReq = null;
$("#FormEditEmpExemptionSubCategory").on('submit', function (e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#FormEditEmpExemptionSubCategory").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled", "disabled");
	var form_data = {
		eid: $("#edit_id").val(),
		parent_id: $("#edit_parent_id").val(),
		category_name: $("#edit_category_name").val(),
		max_exemption_amount: $("#edit_max_exemption_amount").val(),
		token: $("#edit_token").val(),
		mode: 'edit',
		is_ajax: 1
	};

	$.ajax({
		cache: false,
		url: root_domain + hrms_domain + 'app/payroll_emp_exemption_subcategory/',
		type: "POST",
		data: form_data,
		success: function (response) {
			var data = JSON.parse(response);
			var response=data.res;
			if (response.trim() == '1') {
				toastr.success("EMPLOYEE TAX EXEMPTION SUB CATEGORY UPDATED SUCCESSFULLY", "SUCCESS");
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
			$("#ModalEditEmpExemptionSubCategory").modal("hide");
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
			"sAjaxSource": root_domain + hrms_domain + 'app/payroll_emp_exemption_subcategory/',
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
			url: root_domain + hrms_domain + 'app/payroll_emp_exemption_subcategory/',
			data: { mode: "delete", token: $("#token").val(), eid: id },
			success: function (response) {
				if (response.trim() == "1") {
					toastr.success("EMPLOYEE TAX EXEMPTION SUB CATEGORY DELETE SUCCESSFULLY", "SUCCESS");
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
		url: root_domain + hrms_domain + 'app/payroll_emp_exemption_subcategory/',
		data: { mode: "preedit", id: id },
		success: function (response) {
			var obj = jQuery.parseJSON(response);
			$("#ModalEditEmpExemptionSubCategory").modal("show");
			$("#edit_id").val(id);
			$("#edit_parent_id").select2('val',obj.parent_id);
			$("#edit_category_name").val(obj.category_name);
			$("#edit_max_exemption_amount").val(obj.max_exemption_amount);
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
		url: root_domain + hrms_domain + 'app/payroll_emp_exemption_subcategory/',
		data: { mode : "change_status", eid : id , p_status:p_status },
		success: function(response)
		{
			toastr.success("EMPLOYEE TAX EXEMPTION SUB CATEGORY STATUS CHANGED SUCCESSFULLY", "SUCCESS");
			Unloading();
			show_list_data();
		}
	});
	Unloading();
}
