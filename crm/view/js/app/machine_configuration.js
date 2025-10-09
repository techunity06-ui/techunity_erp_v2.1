$(document).ready(function() {
	load_machine_configuration_datatable();
	
	// validate vendor add form on keyup and submit
	$("#machine_configuration_add").validate({
		rules: {
			product_name: {
				required: true
			},
			process_name: {
				required: true
			},
			short_count: {
				required: true
			}
		},
		messages: {
			product_name: {
				required: "Enter Product Name"			
			},
			process_name: {
				required: "Enter Process Name"			
			},
			short_count: {
				required: "Enter Short Count Number"			
			}
		}
	}); 	
	
});
$("#machine_configuration_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#machine_configuration_add").valid()) {
		return false;
	}		
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$("#submit_btn").attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/machine_configuration/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {				
				toastr.success("MACHINE CONFIGURATION ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + 'machine_configuration_list';
			}
			else if(response.trim() == '2') {
				toastr.success("MACHINE CONFIGURATION UPDATED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + 'machine_configuration_list';
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			else if(response.trim() == '-1'){
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			Unloading();
			$('#machine_configuration_add').trigger('reset');
			$("#submit_btn").removeAttr("disabled");
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function delete_data(id) 
{
	var r= confirm(" Are you want to delete ?");
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + 'app/machine_configuration/',
			data: { mode : "delete", eid : id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("MACHINE CONFIGURATION DELETE SUCCESSFULLY", "SUCCESS");
					load_machine_configuration_datatable();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}
function load_machine_configuration_datatable(){
	$("#machine_configuration-datatable").dataTable({
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
		"sAjaxSource": root_domain + 'app/machine_configuration/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}