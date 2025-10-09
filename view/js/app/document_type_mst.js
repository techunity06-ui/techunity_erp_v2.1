$(document).ready(function() {
	load_document_type_datatable();
	// validate vendor add form on keyup and submit
	$("#document_type_add").validate({
		rules: {
			document_type_name: {
				required: true,
			}
		},
		messages: {
			document_type_name: {
				required: "Enter Document Type Name"			
			}
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEditdocument_type").validate({
		rules: {
			edit_document_type_name: {
				required: true,
			}
		},
		messages: {
			edit_document_type_name: {
				required: "Enter Document Type Name"			
			}
		}
	});		
	
});
$("#document_type_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#document_type_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		document_type_name: $("#document_type_name").val(),
		document_type_model: $("#document_type_model").val(),
		branch_id: $("#abranch_id").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/document_type_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			var resp = JSON.parse(response);
			var msg= resp.msg;
			if(msg.trim() == '1') {
				toastr.success("DOCUMENT TYPE ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_document_type_datatable();
			}
			else if(msg.trim() == '2') {
				toastr.success("DOCUMENT TYPE ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_document_type_modal").modal("hide");
				$('#document_type_id').append('<option value='+resp.document_type_id+'>'+resp.document_type_name+'</option>'); 
				$('#document_type_id').select2("val",resp.document_type_id);
				$("#document_type_id").trigger('change'); 
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
			$('#document_type_add').trigger('reset'); 	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditdocument_type").on('submit',function(e) {	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditdocument_type").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		document_type_name: $("#edit_document_type_name").val(),
		branch_id: $("#e_branch_id").val(), 
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/document_type_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			
			if(response.trim() == '1') {
				toastr.success("DOCUMENT TYPE UPDATED SUCCESSFULLY", "SUCCESS");
				load_document_type_datatable();
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
			$("#ModalEditdocument_type").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
}); 
function delete_document_type(document_type_id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/document_type_mst/',
			data: { mode : "delete", document_type_id : document_type_id },
			success: function(response)
			{
				var resp = JSON.parse(response);
				if(resp.msg == "-1") {
					swal("CURRENT RECORD ALREADY USED BELOW MODULES", ""+resp.table+"", "warning");
         		    load_document_type_datatable();
					Unloading();
				}else if(resp.msg == "1") {
					toastr.success("DOCUMENT TYPE DELETE SUCCESSFULLY", "SUCCESS"); 	
					load_document_type_datatable();
					Unloading();
				}else if(resp.msg == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function edit_document_type(document_type_id)
{ 
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+'app/document_type_mst/',
		data: { mode : "preedit", document_type_id : document_type_id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			
			$("#ModalEditdocument_type").modal("show");
			$("#edit_id").val(document_type_id);				
			$("#edit_document_type_name").val(obj.document_type_name);
			$("#e_branch_id").select2("val", obj.branch_id);
			Unloading();
		}
	});	
}
function load_document_type_datatable(){
	var branch_id = $('#branch_id').val();

	datatable = $("#document_type-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bdocument_typeing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sdocument_typeing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+'app/document_type_mst/',
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