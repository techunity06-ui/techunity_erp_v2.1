$(document).ready(function() {
	load_process_type_datatable();
	// validate vendor add form on keyup and submit
	$("#process_type_add").validate({
		rules: {
			process_type_name: {
				required: true,
			}
		},
		messages: {
			process_type_name: {
				required: "Enter Process Type Name"			
			}
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEditprocess_type").validate({
		rules: {
			edit_process_type_name: {
				required: true,
			}
		},
		messages: {
			edit_process_type_name: {
				required: "Enter Process Type Name"			
			}
		}
	});		
	
});
$("#process_type_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#process_type_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		process_type_name: $("#process_type_name").val(),
		process_type_model: $("#process_type_model").val(),
		branch_id: $("#abranch_id").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/process_type_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			var resp = JSON.parse(response);
			var msg= resp.msg;
			if(msg.trim() == '1') {
				toastr.success("PROCESS TYPE ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_process_type_datatable();
			}
			else if(msg.trim() == '2') {
				toastr.success("PROCESS TYPE ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_process_type_modal").modal("hide");
				$('#process_type_id').append('<option value='+resp.process_type_id+'>'+resp.process_type_name+'</option>'); 
				$('#process_type_id').select2("val",resp.process_type_id);
				$("#process_type_id").trigger('change'); 
				Unloading();
			}
			else if(msg.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(msg.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#process_type_add').trigger('reset'); 	
			$('#abranch_id').select2('val', '1000');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditprocess_type").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditprocess_type").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		process_type_name: $("#edit_process_type_name").val(),
		branch_id: $("#e_branch_id").val(), 
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/process_type_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("PROCESS TYPE UPDATED SUCCESSFULLY", "SUCCESS");
				load_process_type_datatable();
				Unloading();						
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$("#ModalEditprocess_type").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
}); 
function delete_process_type(process_type_id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/process_type_mst/',
			data: { mode : "delete", process_type_id : process_type_id },
			success: function(response)
			{
				var resp = JSON.parse(response);
				if(resp.msg == "-1") {
					swal("CURRENT RECORD ALREADY USED BELOW MODULES", ""+resp.table+"", "warning");
         		    load_process_type_datatable();
					Unloading();
				}else if(resp.msg == "1") {
					toastr.success("PROCESS TYPE DELETE SUCCESSFULLY", "SUCCESS"); 	
					load_process_type_datatable();
					Unloading();
				}else if(resp.msg == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function edit_process_type(process_type_id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/process_type_mst/',
		data: { mode : "preedit", process_type_id : process_type_id },
		success: function(response)
		{
			console.log(response);
			var obj = jQuery.parseJSON(response);
			
			$("#ModalEditprocess_type").modal("show");
			$("#edit_id").val(process_type_id);				
			$("#edit_process_type_name").val(obj.process_type_name);
			$("#e_branch_id").select2("val", obj.branch_id);
			$("#FormEditprocess_type").valid();
			Unloading();
		}
	});	
}
function load_process_type_datatable(){
	var branch_id = $('#branch_id').val();

	datatable = $("#process_type-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bprocess_typeing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sprocess_typeing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+administration_domain+'app/process_type_mst/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },
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