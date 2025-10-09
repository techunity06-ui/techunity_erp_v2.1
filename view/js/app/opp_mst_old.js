$(document).ready(function() {
	load_opp_datatable();
	// validate vendor add form on keyup and submit
	$("#opp_add").validate({
		rules: {
			opp_stage: {
				required: true
			},
			opp_probability: {
				required: true
			},
			opp_priority: {
				required: true
			}
		},
		messages: {
			opp_stage: {
				required: "choose Opportunity Stage"			
			},
			opp_probability: {
				required: "Enter Probability"			
			},
			opp_priority: {
				required: "Enter Priority"			
			}
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEditExp").validate({
		rules: {
			edit_opp_stage: {
				required: true
			},
			edit_opp_probability: {
				required: true
			},
			edit_opp_priority: {
				required: true
			}
		},
		messages: {
			edit_opp_stage: {
				required: "choose Opportunity Stage"			
			},
			edit_opp_probability: {
				required: "Enter Probability"			
			},
			edit_opp_priority: {
				required: "Enter Priority"			
			}
		}
	});		
	
});
$("#opp_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#opp_add").valid()) {
		return false;
	}
	form.submitted = true;
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		opp_stage: $("#opp_stage").val(),
		opp_probability: $("#opp_probability").val(),
		opp_priority: $("#opp_priority").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/opp_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			var resp = JSON.parse(response);
			var msg= resp.msg;
			if(msg.trim() == '1') {
				toastr.success("OPPORTUNITY ADDED SUCCESSFULLY", "SUCCESS");
				Unloading();
				load_opp_datatable();
			}
			else if(msg.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(msg.trim() == '-1') {
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#opp_add').trigger('reset'); 	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditExp").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditExp").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		opp_stage: $("#edit_opp_stage").val(),
		opp_probability: $("#edit_opp_probability").val(),
		opp_priority: $("#edit_opp_priority").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/opp_mst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("Opportunity UPDATED SUCCESSFULLY", "SUCCESS");
				load_opp_datatable();
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
			$("#ModalEditExp").modal("hide");					
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
}); 
function delete_opp(opp_id) 
{
	var r= confirm(" Are you want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/opp_mst/',
			data: { mode : "delete", opp_id : opp_id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("OPPORTUNITY DELETE SUCCESSFULLY", "SUCCESS"); 	
					load_opp_datatable();
					Unloading();
				}
				else if(response.trim() == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}
function edit_opp(opp_id)
{
	Loading(true);
	editReq = $.ajax({
		type: "POST",
		url: root_domain+'app/opp_mst/',
		data: { mode : "preedit", opp_id : opp_id },
		success: function(response)
		{
			//console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditExp").modal("show");
			$("#edit_id").val(opp_id);
			$("#edit_opp_stage").val(obj.opp_stage);
			$("#edit_opp_probability").val(obj.opp_probability);
			$("#edit_opp_priority").val(obj.opp_priority);
			Unloading();
		}
	});	
}
function load_opp_datatable(){
	datatable = $("#opp-table").dataTable({
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
		"sAjaxSource": root_domain+'app/opp_mst/',
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

function deactive_opp(opp_id) 
{
	var r= confirm(" Are you want to Deactivate ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/opp_mst/',
			data: { mode : "de_active", opp_id : opp_id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("OPPORTUNITY DEACTIVATED SUCCESSFULLY", "SUCCESS"); 	
					load_opp_datatable();
					Unloading();
				}
				else if(response.trim() == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}

function active_opp(opp_id) 
{
	var r= confirm(" Are you want to Deactivate ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+'app/opp_mst/',
			data: { mode : "opp_active", opp_id : opp_id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("OPPORTUNITY ACTIVATED SUCCESSFULLY", "SUCCESS"); 	
					load_opp_datatable();
					Unloading();
				}
				else if(response.trim() == "0") { 
					toastr.warning("SOMETHING WRONG", "WARNING");
				}							
			}
		});	
	}
	
}