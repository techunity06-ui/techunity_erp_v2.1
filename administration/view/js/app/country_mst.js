$(document).ready(function() {
	load_country_datatable();
	
	// validate vendor add form on keyup and submit
	$("#country_add").validate({
		rules: {
			country_name: {
				required: true,
				minlength: 3
			},
			country_code : {
				maxlength: 4
			}
		},
		messages: {
			country_name: {
				required: "Enter Country Name",
				minlength: "Your Country Name must consist of at least 3 characters"
			},
			country_code : {
				maxlength: "You can only enter 4 characters"
			}
		}
	}); 
	// validate vendor edit form on keyup and submit
	$("#FormEditCountry").validate({
		rules: {
			edit_country_name: {
				required: true,
				minlength: 3
			},
			edit_country_code : {
				maxlength: 4
			}		
		},
		messages: {
			edit_country_name: {
				required: "Enter Country Name",
				minlength: "Your Country Name must consist of at least 3 characters"
			},
			edit_country_code : {
				maxlength: "You can only enter 4 characters"
			}
		}
	});		
	
});
$("#country_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#country_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data = {
		country_name: $("#country_name").val(),
		country_initital: $("#country_initital").val(),
		country_code: $("#country_code").val(),
		branch_id: $("#abranch_id").val(),
		mode:'Add',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/countrymst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				toastr.success("COUNTRY ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				load_country_datatable();
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
			$('#country_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditCountry").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditCountry").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		country_initital: $("#edit_country_initital").val(),
		country_name: $("#edit_country_name").val(),
		country_code: $("#edit_country_code").val(),
		branch_id: $("#e_branch_id").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/countrymst/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			if(response.trim() == '1') {
				toastr.success("COUNTRY UPDATED SUCCESSFULLY", "SUCCESS");
				load_country_datatable();
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
function delete_country(id) 
{
	var r= confirm(" Are you sure want to delete ?");
	
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/countrymst/',
			data: { mode : "delete", eid : id },
			success: function(response)
			{		
				if(response.trim() == "1") {
					toastr.success("COUNTRY DELETE SUCCESSFULLY", "SUCCESS");
					load_country_datatable();
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
		url: root_domain+administration_domain+'app/countrymst/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			//console.log(response);
			var obj = jQuery.parseJSON(response);
			$("#ModalEditAccount").modal("show");
			$("#edit_id").val(id);				
			$("#edit_country_initital").val(obj.country_initital);
			$("#edit_country_name").val(obj.country_name);
			$("#edit_country_code").val(obj.country_code);
			$("#e_branch_id").select2("val", obj.branch_id);
			$("#FormEditCountry").valid()
			Unloading();
		}
	});	
}
function load_country_datatable(){
	var branch_id = $('#branch_id').val();

	datatable = $("#country-table").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO COUNTRY ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+administration_domain+'app/countrymst/',
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