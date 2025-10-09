$(document).ready(function() {
	load_field_datatable();
	
	// validate vendor add form on keyup and submit
	$("#field_add").validate({
		rules: {
			field_name: {
				required: true,
			},
			// Amish Soni Start 13-01-2021
			table_name: {
				required: true,
			},
			replace_with_select: {
				required: function(){
					return $("#table_name").val() != 'other';
				}
			},
			replace_with: {
				required: function(){
					return $("#table_name").val() == 'other';
				}
			},
			// Amish Soni End 13-01-2021
			module_id: {
				required: true,
			}
		},
		messages: {
			field_name: {
				required: "Enter Field Name"
			},
			// Amish Soni Start 13-01-2021
			table_name: {
				required: "Select Table"
			},
			replace_with_select: {
				required: "Select Field to Replace"
			},
			replace_with: {
				required: "Enter value to Replace"
			},
			// Amish Soni End 13-01-2021
			module_id: {
				required: "Select Module"
			}
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEditField").validate({
		rules: {
			edit_field_name: {
				required: true
			},
			// Amish Soni Start 13-01-2021
			edit_table_name: {
				required: true,
			},
			edit_replace_with_select: {
				required: function(){
					return $("#edit_table_name").val() != 'other';
				}
			},
			edit_replace_with: {
				required: function(){
					return $("#edit_table_name").val() == 'other';
				}
			},
			// Amish Soni End 13-01-2021
			edit_module_id: {
				required: true,
			},		
		},
		messages: {
			edit_field_name: {
				required: "Enter Field Name"
			},
			// Amish Soni Start 13-01-2021
			edit_table_name: {
				required: "Select Table"
			},
			edit_replace_with_select: {
				required: "Select Field to Replace"
			},
			edit_replace_with: {
				required: "Enter value to Replace"
			},
			// Amish Soni End 13-01-2021
			edit_module_id: {
				required: "Select Module"
			},
		}
	});		
	
});
$("#field_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#field_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		field_name: $("#field_name").val(),
		// Amish Soni Start 13-01-2021
		table_name: $("#table_name").val(),
		replace_with_select: $("#replace_with_select").val(),
		replace_with_text: $("#replace_with_text").val(),
		// Amish Soni End 13-01-2021
		module_id: $("#module_id").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/merge_field_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			if(response.trim() == '1') {
				toastr.success("FIELD ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_field_datatable();
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
			$('.replaceBox').hide();
			$('#table_name, #replace_with_select, #module_id').select2('val', '');
			$('#field_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditField").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditField").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		field_name: $("#edit_field_name").val(),
		module_id: $("#edit_module_id").val(),
		// Amish Soni Start 13-01-2021
		table_name: $("#edit_table_name").val(),
		replace_with_select: $("#edit_replace_with_select").val(),
		replace_with_text: $("#edit_replace_with_text").val(),
		// Amish Soni End 13-01-2021
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/merge_field_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("FIELD UPDATED SUCCESSFULLY", "SUCCESS");
				load_field_datatable();
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
function delete_field(id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/merge_field_mst/',
			data: { mode : "delete", eid : id },
			success: function(response)
			{		
				if(response.trim() == "1") {
					toastr.success("FIELD DELETE SUCCESSFULLY", "SUCCESS");
					load_field_datatable();
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
		url: root_domain+'app/merge_field_mst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			//console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(id);		
			
			$("#edit_field_name").val(obj.field_name);
			// Amish Soni Start 13-01-2021
			var getTableName = obj.table_name;
			if(getTableName) {
				$("#edit_replace_with_select").select2('val', '');
				$("#edit_table_name").select2('val', getTableName);
				if(getTableName == 'other') {
					$('.replace_select').hide();
					$('#edit_replace_with_text').show();
					$("#edit_replace_with_text").val(obj.replace_with);
				} else {
					$('#updateBtn').hide();
					getColumns(getTableName, 'edit');
					setTimeout(function() {
						$('.replace_select').show();
						$('#edit_replace_with_text').hide();
						$("#edit_replace_with_select").select2('val', obj.replace_with);
						$('#updateBtn').show();
					}, 800);
					
				}
			} else {
				$('.edit_replaceBox').hide();
			}
			// Amish Soni Start 13-01-2021
			$("#edit_module_id").select2('val', obj.module_id);

			Unloading();
		}
	});	
}
function load_field_datatable(){
	datatable = $("#field-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO FIELD ADDED YET !",
		},
		"aLengthMenu": [[10, 30, 50, -1], [10, 30, 50, "All"]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+'app/merge_field_mst/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
// Amish Soni Start 12-01-2021
function getColumns(table_name, mode = '')
{
	var checkMode = (mode == 'edit') ? 'edit_' : '';
	$('.'+checkMode+'replaceBox, #'+checkMode+'replace_with_text, .replace_select').hide();
	if(table_name == 'other') {
		$('.'+checkMode+'replaceBox, #'+checkMode+'replace_with_text').show();
		$('#'+checkMode+'replace_with_select').select2("val", "");

		return true;
	}

	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+'app/merge_field_mst/',
		data: { mode : "get_columns", table_name : table_name },
		success: function(response)
		{
			$('.'+checkMode+'replaceBox, .replace_select').show();
			$('#'+checkMode+'replace_with_text').val('');
			$('#'+checkMode+'replace_with_select').html(response);
		}
	});	
	Unloading();
}
// Amish Soni End 12-01-2021

// Amish Soni Start 13-01-2021
$('#ModalEditAccount').on('hidden.bs.modal', function (e) {
	$('#edit_table_name, #edit_replace_with_select, #edit_module_id').select2('val', '');
})
// Amish Soni End 13-01-2021