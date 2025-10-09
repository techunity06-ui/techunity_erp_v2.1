$(document).ready(function() {
	load_machine_configuration_datatable();
	
	// validate vendor add form on keyup and submit
	$("#machine_configuration_add").validate({
		rules: {
			approve_date: {
				required: true
			},
			approve_status: {
				required: true
			}
		},
		messages: {
			approve_date: {
				required: "Enter Approve Date"			
			},
			approve_status: {
				required: "Enter Approve Status"			
			}
		}
	}); 	
	
});
$("#machine_configuration_add").on('submit',function(e) {

	var ext = $('#upload_machine_file').val().split('.').pop().toLowerCase();
    if($.inArray(ext, ['gif','png','jpg','jpeg']) === -1) {
        toastr.warning("Only image type jpg/png/jpeg/gif is allowed", "ERROR");
        $("#upload_machine_file").focus();
        return false;
    }

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
		url: root_domain + 'app/store_accept_list/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {				
				toastr.success(" ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + 'store_accept_list';
			}
			else if(response.trim() == '2') {
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + 'store_accept_list';
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
	var r= confirm(" Are you sure want to delete ?");
	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain + 'app/store_accept_list/',
			data: { mode : "delete", eid : id },
			success: function(response)
			{
				if(response.trim() == "1") {
					toastr.success("DELETE SUCCESSFULLY", "SUCCESS");
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
	var start_date = $("#start_date").val();
	var end_date = $("#end_date").val();
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
		"sAjaxSource": root_domain + 'app/store_accept_list/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "fetch" },
				{ "name": "start_date", "value": start_date },
				{ "name": "end_date", "value": end_date });
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();
	
	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}
