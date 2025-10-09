$(document).ready(function() {
	load_opp_datatable();
	// validate vendor add form on keyup and submit
	$("#opp_add").validate({
		rules: {
			opp_stage: {
				required: true
			},
			opp_probability: {
				required: true,
				number: true,
		        min: 0,
		       	max: 100,
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
				required: true,
				number: true,
		        min: 0,
		       	max: 100,
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
	
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#opp_add").valid()) {
		return false;
	}
	var form = this;
	form.submitted = true;
	Loading(true);	

	

	$(form).prop("disabled", true);

    var form_data = new FormData();

    // Append form fields to FormData manually
    form_data.append('opp_stage', $("#opp_stage").val());
    form_data.append('opp_probability', $("#opp_probability").val());
    form_data.append('opp_priority', $("#opp_priority").val());
    form_data.append('opp_color', $("#opp_color").val());
    form_data.append('branch_id', $("#abranch_id").val());
    form_data.append('whatsapp_status', $("#whatsapp_status").val());

	if ($("#whatsapp_status").val() == 1) {
		var enable_whatsapp = 0;
		if ($("#enable_whatsapp1").is(":checked")) {
			enable_whatsapp = 1;
		}
		form_data.append('opp_template', $("#opp_template").val());
    	form_data.append('enable_whatsapp', enable_whatsapp);

		var fileInput = $("#opp_file");
		var file = fileInput.prop("files")[0];
		form_data.append('opp_file', file);
	} 

    form_data.append('mode', 'Add');
    form_data.append('is_ajax', 1);

	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/opp_mst/',
		type: "POST",
		data: form_data,
		processData: false,
        contentType: false,
		success: function(response)
		{
			var resp = JSON.parse(response);
			var msg= resp.msg;
			if(msg.trim() == '1') {
				if ($("#whatsapp_status").val() == 1) {
					fileInput.val('');
				}
				toastr.success("SALES STAGE ADDED SUCCESSFULLY", "SUCCESS");
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

	var form_data = new FormData();

    // Append form fields to FormData manually
    form_data.append('eid', $("#edit_id").val());
    form_data.append('opp_stage', $("#edit_opp_stage").val());
    form_data.append('opp_probability', $("#edit_opp_probability").val());
    form_data.append('opp_priority', $("#edit_opp_priority").val());
    form_data.append('opp_color', $("#edit_opp_color").val());
    form_data.append('branch_id', $("#e_branch_id").val());
    form_data.append('whatsapp_status', $("#whatsapp_status").val());
    
    form_data.append('mode', 'edit');
    form_data.append('is_ajax', 1);

	if ($("#whatsapp_status").val() == 1) {
		var enable_whatsapp = 0;
		if ($("#edit_enable_whatsapp1").is(":checked")) {
			enable_whatsapp = 1;
		}
		form_data.append('enable_whatsapp', enable_whatsapp);
		var fileInput = $("#edit_opp_file");
		var file = fileInput.prop("files")[0];
		form_data.append('opp_file', file);
		form_data.append('opp_template', $("#edit_opp_template").val());
	}
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/opp_mst/',
		type: "POST",
		data: form_data,
		processData: false,
        contentType: false,
		success: function(response)
		{
			if(response.trim() == '1') {
				if ($("#whatsapp_status").val() == 1) {
					fileInput.val('');
				}
				toastr.success("SALES STAGE UPDATED SUCCESSFULLY", "SUCCESS");
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
			url: root_domain + crm_domain +'app/opp_mst/',
			data: { mode : "delete", opp_id : opp_id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("SALES STAGE DELETE SUCCESSFULLY", "SUCCESS"); 	
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
		url: root_domain + crm_domain +'app/opp_mst/',
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
            $("#edit_opp_color").val(obj.opp_color);
            $("#e_branch_id").select2("val", obj.branch_id);
            $("#edit_opp_template").val(obj.opp_template);
            // $("#edit_opp_file").val(obj.opp_file);
            // $("#edit_opp_file").select2(obj.opp_file);
            $("#e_branch_id").select2("val", obj.branch_id);
			if (obj.enable_whatsapp == 1) {
				$("#edit_enable_whatsapp1").attr('checked', true).change();
				$("#edit_enable_whatsapp").closest("label").removeClass("active");
				$("#edit_enable_whatsapp1").closest("label").addClass("active");
			} else {
				$("#edit_enable_whatsapp1").attr('checked', false).change();
				$("#edit_enable_whatsapp").closest("label").addClass("active");
				$("#edit_enable_whatsapp1").closest("label").removeClass("active");
			}
			Unloading();
		}
	});	
}
function load_opp_datatable(){
	var branch_id = $('#branch_id').val();

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
		"sAjaxSource": root_domain + crm_domain +'app/opp_mst/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },
						 { "name": "branch_id", "value": branch_id }
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

function deactive_opp(opp_id) 
{
	var r= confirm(" Are you want to Deactivate ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + crm_domain +'app/opp_mst/',
			data: { mode : "de_active", opp_id : opp_id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("SALES STAGE DEACTIVATED SUCCESSFULLY", "SUCCESS"); 	
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
			url: root_domain + crm_domain +'app/opp_mst/',
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