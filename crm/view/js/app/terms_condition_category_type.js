var datatable;
$(document).ready(function() {	
	load_table_terms_condition_category();
	// validate vendor add form on keyup and submit
	$("#terms_condition_category_type_add").validate({
		rules: {
			terms_condition_category_name:{
				required:true
			},
			status: {
				required: true
			}
		},
		messages: {
			terms_condition_category_name:{
				required: "Enter Terms & Condition Category Name"
			},
			status: {
				required: "Select Status"
			}
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEditTermsConditionCategory").validate({
		rules: {
			edit_terms_condition_category_name: {
				required: true
			},
			edit_status: {
				required: true
			}
		},
		messages: {
			edit_terms_condition_category_name: {
				required: "Enter Terms & Condition Category Name"			
			},
			edit_status: {
				required: "Select Status"
			}
		}
	});		
});
$("#terms_condition_category_type_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#terms_condition_category_type_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var terms_condition_category_name=$("#terms_condition_category_name").val();
	
	var form_data = {
		terms_condition_category_name: terms_condition_category_name,
		status: $("#status").val(),
		branch_id: $("#abranch_id").val(),
		mode:$("#mode").val(),
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/terms_condition_category_type/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {				
				toastr.success("TERMS & CONDITION CATEGORY TYPE ADDED SUCCESSFULLY", "SUCCESS");
				$('#terms_condition_category_name').val('');
				load_table_terms_condition_category();
				Unloading();
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				$('#terms_condition_category_name').val('');
				Unloading();				
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditTermsConditionCategory").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditTermsConditionCategory").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		terms_condition_category_name: $('#edit_terms_condition_category_name').val(),
		status: $("#edit_status").val(),
		branch_id: $("#e_branch_id").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/terms_condition_category_type/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				
				toastr.success("TERMS & CONDITION CATEGORY TYPE UPDATED SUCCESSFULLY", "SUCCESS");
				load_table_terms_condition_category();
				Unloading();						
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$("#ModalEditTermsConditionCategory").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function delete_reload()
{
	datatable.fnReloadAjax();
}
function delete_terms_condition_category_type(id) 
{
	var r= confirm(" Are you want to delete ?");

		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain + 'app/terms_condition_category_type/',
				data: { mode : "delete", eid : id },
				success: function(response)
				{
					if(response.trim() == "1") {
						toastr.success("TERMS & CONDITION CATEGORY TYPE DELETED SUCCESSFULLY", "SUCCESS");
						load_table_terms_condition_category();
						Unloading();
					}
					else if(response.trim() == "0") {
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	
}
function edit_terms_condition_category_type(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/terms_condition_category_type/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			$("#ModalEditTermsConditionCategory").modal("show");
			$("#edit_id").val(obj.id);
			$("#edit_terms_condition_category_name").val(obj.terms_condition_category_name);
			$("#edit_status").select2("val", obj.status);
			$("#e_branch_id").select2("val", obj.branch_id);
			Unloading();
		}
	});	
}

function load_table_terms_condition_category(cat)
{
	var branch_id = $('#branch_id').val();

	var datatable = $("#dynamic-table").dataTable({
			"bStateSave": true,
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bDestroy": true,
			"bServerSide" : true,
			"bSearchable":true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO DATA ADDED YET !",
			},
			"iDisplayLength": 10,
			"aLengthMenu": [[10, 20, 50, 100, -1], [10, 20, 50, 100,"All"]],
			"sAjaxSource": root_domain + crm_domain +'app/terms_condition_category_type/',
			"fnServerParams": function ( aoData ) {
				aoData.push( { "name": "mode", "value": "fetch" },
							 { "name": "cat", "value": cat },
							 { "name": "branch_id", "value": branch_id }
						);
			},
			"fnDrawCallback": function( oSettings ) {
				$('.ttip, [data-toggle="tooltip"]').tooltip();
			}
		}).fnSetFilteringDelay();

		//Search input style
		$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
		$('.dataTables_length select').addClass('form-control');
}