$(document).ready(function() {
	load_process_datatable();
	// validate vendor add form on keyup and submit
	$("#process_add").validate({
		rules: {
			process_name: {
				required: true
			},
			process_type:{
				required: true
			}
			,
			dashbord_priority:{
				required: true
			}
		},
		messages: {
			process_name: {
				required: "Enter Process Name"			
			},
			process_type:{
				required: "Select Process Type"
			},
			dashbord_priority:{
				required: "Enter Dashbord Priority"
			}
		}
	}); 

	// validate vendor edit form on keyup and submit
	$("#FormEditprocess").validate({
		rules: {
			edit_process_name: {
				required: true
			},
			edit_process_type:{
				required: true
			}
			,
			edit_dashbord_priority:{
				required: true
			}

		},
		messages: {
			edit_process_name: {
				required: "Enter Process Name"			
			},
			edit_process_type:{
				required: "Select Process Type"
			}
			,
			edit_dashbord_priority:{
				required: "Enter Dashbord Priority"
			}
		}
	});		
	
});

$("#process_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#process_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		process_type: $("#process_type").val(),
		process_name: $("#process_name").val(),
		process_model: $("#process_model").val(),
		dashbord_priority: $("#dashbord_priority").val(),
		branch_id: $("#abranch_id").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/process_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			var resp = JSON.parse(response);
			var msg= resp.msg;
			if(msg.trim() == '1') {
				toastr.success("PROCESS LIST ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_process_datatable();
				$('#process_type').select2("val","");
			}
			else if(msg.trim() == '2') {
				toastr.success("PROCESS LIST ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_process_modal").modal("hide");
				$('#process_id').append('<option value='+resp.process_id+'>'+resp.process_name+'</option>'); 
				$('#process_id').select2("val",resp.process_id);
				$("#process_id").trigger('change'); 
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
			$('#process_add').trigger('reset'); 	
			$("#abranch_id").select2("val","1000");
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditprocess").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditprocess").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		process_name: $("#edit_process_name").val(), 
		edit_process_type: $("#edit_process_type").val(),
		edit_dashbord_priority: $("#edit_dashbord_priority").val(),
		branch_id: $("#e_branch_id").val(),  
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/process_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("PROCESS LIST UPDATED SUCCESSFULLY", "SUCCESS");
				load_process_datatable();
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
			$("#ModalEditprocess").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
}); 
function delete_process(process_id,is_deletable) 
{
	if(is_deletable == 0){
		Swal.fire({
		  title: 'Process is in use.',
		  text: "You can't be delete this process!",
		  icon: 'info',
		})
	}else{
		var r= confirm(" Are you want to delete ?");
	
		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+administration_domain+'app/process_mst/',
				data: { mode : "delete", process_id : process_id },
				success: function(response)
				{
					if(response.trim() == "1") {
						toastr.success("PROCESS LIST DELETE SUCCESSFULLY", "SUCCESS"); 	
						load_process_datatable();
						Unloading();
					}
					else if(response.trim() == "0") { 
						toastr.warning("SOMETHING WRONG", "WARNING");
					}							
				}
			});	
		}
	}
	
}
function edit_process(process_id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/process_mst/',
		data: { mode : "preedit", process_id : process_id },
		success: function(response)
		{
			console.log(response);
			var obj = jQuery.parseJSON(response);
			
			$("#ModalEditprocess").modal("show");
			$("#edit_process_type").val(process_id);				
			$("#edit_id").val(process_id);				
			$("#edit_process_type").html(obj.process_type_list);				
			$("#edit_process_name").val(obj.process_name);
			$("#edit_process_type").val(obj.process_type);
			$("#edit_dashbord_priority").val(obj.dashbord_priority);
			$("#e_branch_id").select2("val", obj.branch_id);
			$("#FormEditprocess").valid()
			Unloading();
		}
	});	
}
function load_process_datatable(){
	var branch_id = $('#branch_id').val();

	datatable = $("#process-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+administration_domain+'app/process_mst/',
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

function exportCsv() {
	var branch_id = $('#branch_id').val();

	var url = root_domain +'generate_export?mode=administrator_master_process&branch_id=' + encodeURIComponent(branch_id);
	window.location.href = url;
}