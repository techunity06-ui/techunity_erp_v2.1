$(document).ready(function() {
	load_module_datatable();
	
	// validate vendor add form on keyup and submit
	$("#module_add").validate({
		rules: {
			module_name: {
				required: true,
			}
		},
		messages: {
			module_name: {
				required: "Enter Module Name"
			}
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEditMake").validate({
		rules: {
			edit_make_name: {
				required: true
			}		
		},
		messages: {
			edit_make_name: {
				required: "Enter Make"
			}
		}
	});		
	
});
$("#module_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#module_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		module_name: $("#module_name").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/module_type/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("MODULE ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_module_datatable();
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
			$('#module_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditModule").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditModule").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		module_name: $("#edit_module_name").val(), 
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/module_type/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("MODULE UPDATED SUCCESSFULLY", "SUCCESS");
				load_module_datatable();
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
function delete_module(id) 
{
	var r= confirm(" Are you sure want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/module_type/',
			data: { mode : "delete", eid : id },
			success: function(response)
			{		
				if(response.trim() == "1") {
					toastr.success("MODULE DELETE SUCCESSFULLY", "SUCCESS");
					load_module_datatable();
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
	$("#FormEditModule").valid();
	editReq = $.ajax({
		type: "POST",
		url: root_domain+'app/module_type/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(id);				
			$("#edit_module_name").val(obj.module_name);
			Unloading();
		}
	});	
}
function load_module_datatable(){

	datatable = $("#mod-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO MODULE ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+'app/module_type/',
		"fnServerParams": function ( aoData ) {
			aoData.push( 
				{ "name": "mode", "value": "fetch" }
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