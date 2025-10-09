$(document).ready(function() {
	load_make_datatable();
	
	// validate vendor add form on keyup and submit
	$("#make_add").validate({
		rules: {
			material_parameter_name: {
				required: true,
			}
		},
		messages: {
			material_parameter_name: {
				required: "Enter Make Name"
			}
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEditMake").validate({
		rules: {
			edit_material_parameter_name: {
				required: true
			}		
		},
		messages: {
			edit_material_parameter_name: {
				required: "Enter Make"
			}
		}
	});		
	
});
$("#make_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#make_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		material_parameter_name: $("#material_parameter_name").val(),
		branch_id: $("#abranch_id").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/material_parameter_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("MAKE ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_make_datatable();
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
			$('#make_add').trigger('reset');
			$('#abranch_id').select2("val",1000);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditMake").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditMake").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		material_parameter_name: $("#edit_material_parameter_name").val(),
		branch_id: $("#e_branch_id").val(), 
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/material_parameter_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("MAKE UPDATED SUCCESSFULLY", "SUCCESS");
				load_make_datatable();
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
			$("#ModalEditAccount").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function delete_make(id) 
{
	var r= confirm(" Are you sure want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/material_parameter_mst/',
			data: { mode : "delete", eid : id },
			success: function(response)
			{		
				if(response.trim() == "1") {
					toastr.success("MAKE DELETE SUCCESSFULLY", "SUCCESS");
					load_make_datatable();
					Unloading();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
}
function edit_test(id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/material_parameter_mst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(id);				
			$("#edit_material_parameter_name").val(obj.material_parameter_name);
			$("#e_branch_id").select2("val", obj.branch_id);
			Unloading();
		}
	});	
}
function load_make_datatable(){
	var branch_id = $('#branch_id').val();

	datatable = $("#make-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO MAKE ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+administration_domain+'app/material_parameter_mst/',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{ "name": "mode", "value": "fetch" }, 
				{"name": "branch_id", "value": branch_id }
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