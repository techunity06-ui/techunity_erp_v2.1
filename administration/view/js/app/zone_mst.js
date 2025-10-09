$(document).ready(function() {
	load_zone_datatable();
	// validate vendor add form on keyup and submit
	$("#zone_add").validate({
		rules: {
			zone_name: {
				required: true
			}
		},
		messages: {
			zone_name: {
				required: "Enter Zone Name"			
			}
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEditZone").validate({
		rules: {
			edit_zone_name: {
				required: true
			}
		},
		messages: {
			edit_zone_name: {
				required: "Enter Zone Name"			
			}
		}
	});		
	
});
$("#zone_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#zone_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		zone_name: $("#zone_name").val(),
		zone_model: $("#zone_model").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/zone_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			var resp = JSON.parse(response);
			var msg= resp.msg;
			if(msg.trim() == '1') {
				toastr.success("ZONE ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_zone_datatable();
			}
			else if(msg.trim() == '2') {
				toastr.success("ZONE ADDED SUCCESSFULLY", "SUCCESS");
				$("#add_zone_modal").modal("hide");
				$('#zone_id').append('<option value='+resp.zone_id+'>'+resp.zone_name+'</option>'); 
				$('#zone_id').select2("val",resp.zone_id);
				$("#zone_id").trigger('change'); 
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
			$('#zone_add').trigger('reset'); 	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditZone").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditZone").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		zone_name: $("#edit_zone_name").val(),  
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/zone_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("ZONE UPDATED SUCCESSFULLY", "SUCCESS");
				load_zone_datatable();
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
			$("#ModalEditZone").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
}); 
function delete_zone(zone_id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/zone_mst/',
			data: { mode : "delete", zone_id : zone_id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("ZONE DELETE SUCCESSFULLY", "SUCCESS"); 	
					load_zone_datatable();
					Unloading();
				}
				else if(response.trim() == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function edit_zone(zone_id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/zone_mst/',
		data: { mode : "preedit", zone_id : zone_id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			
			$("#ModalEditZone").modal("show");
			$("#edit_id").val(zone_id);				
			$("#edit_zone_name").val(obj.zone_name);
			Unloading();
		}
	});	
}
function load_zone_datatable(){
	datatable = $("#zone-table").dataTable({
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
		"sAjaxSource": root_domain+administration_domain+'app/zone_mst/',
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