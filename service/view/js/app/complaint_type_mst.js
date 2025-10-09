$(document).ready(function() {
	load_complaint_type_datatable();
	// validate vendor add form on keyup and submit
	$("#complaint_type_add").validate({
		rules: {
			complaint_type_name: {
				required: true
			}
		},
		messages: {
			complaint_type_name: {
				required: "Enter Complaint Type Name"			
			}
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEditComType").validate({
		rules: {
			edit_complaint_type_name: {
				required: true
			}
		},
		messages: {
			edit_complaint_type_name: {
				required: "Enter Complaint Type Name"			
			}
		}
	});		
	
});
$("#complaint_type_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#complaint_type_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		complaint_type_name: $("#complaint_type_name").val(),
		complaint_type_model: $("#complaint_type_model").val(),
		branch_id: $("#abranch_id").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+service_domain+'app/complaint_type_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			var resp = JSON.parse(response);
			var msg= resp.msg;
			if(msg.trim() == '1') {
				toastr.success("COMPLAINT TYPE ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_complaint_type_datatable();
			}
			else if(msg.trim() == '2') {
				toastr.success("COMPLAINT TYPE ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_complaint_type").modal("hide");
				$('#complaint_type_id').append('<option value='+resp.complaint_type_id+'>'+resp.complaint_type_name+'</option>'); 
				$('#complaint_type_id').select2("val",resp.complaint_type_id);
				$("#complaint_type_id").trigger('change'); 
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
			$('#complaint_type_add').trigger('reset'); 	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
$("#FormEditComType").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditComType").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		complaint_type_name: $("#edit_complaint_type_name").val(), 
		branch_id: $("#e_branch_id").val(), 
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+service_domain+'app/complaint_type_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("COMPLAINT TYPE UPDATED SUCCESSFULLY", "SUCCESS");
				load_complaint_type_datatable();
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
			$("#ModalEditComType").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
}); 
function delete_complaint_type(complaint_type_id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+service_domain+'app/complaint_type_mst/',
			data: { mode : "delete", complaint_type_id : complaint_type_id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("COMPLAINT TYPE DELETE SUCCESSFULLY", "SUCCESS"); 	
					load_complaint_type_datatable();
					Unloading();
				}
				else if(response.trim() == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function edit_complaint_type(complaint_type_id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+service_domain+'app/complaint_type_mst/',
		data: { mode : "preedit", complaint_type_id : complaint_type_id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			
			$("#ModalEditComType").modal("show");
			$("#edit_id").val(complaint_type_id);				
			$("#edit_complaint_type_name").val(obj.complaint_type_name);
			$("#e_branch_id").select2("val", obj.branch_id);
			Unloading();
		}
	});	
}
function load_complaint_type_datatable(){
	var branch_id = $('#branch_id').val();

	datatable = $("#complaint_type-table").dataTable({
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
		"sAjaxSource": root_domain+service_domain+'app/complaint_type_mst/',
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