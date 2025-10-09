$(document).ready(function() {
	load_make_datatable();
	
	// validate vendor add form on keyup and submit
	$("#item_master_field_value").validate({
		rules: {
			item_master_field_id : {
				required: true,
			},
			item_master_fieldd_value: {
				required: true,
			}
		},
		messages: {
			item_master_field_id : {
				required: "Choose Master Field"
			},
			item_master_fieldd_value: {
				required: "Enter Item Field Value"
			}
			
		}
	}); 
	// validate vendor edit form on keyup and submit
	
	$("#FormEditItemMasterField").validate({
		rules: {
			edit_item_master_field_id: {
				required : true,
			},
			edit_item_master_fieldd_value: {
				required: true,
			}
		},
		messages: {
			edit_item_master_field_id: {
				required : "Choose Master Field",
			},
			edit_item_master_fieldd_value: {
				required: "Enter Item Field Value"
			}
			
		}
	}); 
	
	
	
});
$("#item_master_field_value").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#item_master_field_value").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		item_master_field_id: $("#item_master_field_id").val(),
		item_master_field_value: $("#item_master_fieldd_value").val(),
		branch_id: $("#abranch_id").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/item_master_field_data/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			
			if(response.trim() == '1') {
				toastr.success("ADDED SUCCESSFULLY", "SUCCESS")
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
			
			
			$('#item_master_field_value').trigger('reset');
			$("#item_master_field_id").select2("val","");
			$('#abranch_id').select2("val",1000);

		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditItemMasterFieldValue").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditItemMasterFieldValue").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		item_master_field_id: $("#edit_item_master_field_id").val(),
		item_master_field_value: $("#edit_item_master_fieldd_value").val(),
		branch_id: $("#e_branch_id").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/item_master_field_data/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");
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
function delete_item_master_field(id) 
{
	var r= confirm(" Are you sure want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/item_master_field_data/',
			data: { mode : "delete", eid : id },
			success: function(response)
			{	
				console.log(response);
				if(response.trim() == "1") {
					toastr.success("ITEM VALUE DELETE SUCCESSFULLY", "SUCCESS");
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
	
	//$("#FormEditItemMasterField").valid();
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/item_master_field_data/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(id);			
			$("#edit_item_master_field_id").val(obj.item_master_field_id);
			$("#edit_item_master_fieldd_value").val(obj.item_master_field_value);
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
		"sAjaxSource": root_domain+administration_domain+'app/item_master_field_data/',
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