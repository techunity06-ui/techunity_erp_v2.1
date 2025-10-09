$(document).ready(function() {
	load_make_datatable();
	
	// validate vendor add form on keyup and submit
	$("#master_field").validate({
		rules: {
			master_field: {
				required: true,
			},
			master_field_db_name: {
				required: true,
			}
		},
		messages: {
			master_field: {
				required: "Enter  Field Name"
			},
			master_field_db_name: {
				required: "Enter  Field DB Name"
			}
			
		}
	}); 
	// validate vendor edit form on keyup and submit
	
	$("#FormEditMasterField").validate({
		rules: {
			edit_master_field: {
				required: true,
			},
			edit_master_field_db_name: {
				required: true,
			}
		},
		messages: {
			edit_master_field: {
				required: "Enter  Field Name"
			},
			edit_master_field_db_name: {
				required: "Enter  Field DB Name"
			}
			
		}
	}); 
	
	
	
});
$("#master_field").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#master_field").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		master_field: $("#master_fieldd").val(),
		master_field_db_name: $("#master_field_db_name").val(),
		priority: $("#priority").val(),
		branch_id: $("#abranch_id").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/master_field/',
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
			
			
			$('#master_field').trigger('reset');
			$('#abranch_id').select2("val",1000);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditMasterField").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditMasterField").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		master_field: $("#edit_master_field").val(),
		master_field_db_name: $("#edit_master_field_db_name").val(),
		priority: $("#edit_priority").val(),
		branch_id: $("#e_branch_id").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/master_field/',
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
function delete_master_field(id) 
{
	var r= confirm(" Are you sure want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/master_field/',
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
	
	//$("#FormEditMasterField").valid();
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/master_field/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(id);			
			$("#edit_master_field").val(obj.master_field);
			$("#edit_master_field_db_name").val(obj.master_field_db_name);
			$("#edit_priority").val(obj.priority);
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
		"sAjaxSource": root_domain+administration_domain+'app/master_field/',
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