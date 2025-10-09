$(document).ready(function() {
	load_annexure_datatable();
	
	// validate vendor add form on keyup and submit
	$("#annexure_add").validate({
		rules: {
			a_name: {
				required: true
			}
		},
		messages: {
			a_name: {
				required: "Enter Annexure Name"			
			}
		}
	}); 	
	
});
$("#annexure_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#annexure_add").valid()) {
		return false;
	}		
							 
	for (instance in CKEDITOR.instances) {
    	CKEDITOR.instances[instance].updateElement();
	}
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$("#submit_btn").attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	
	$.ajax({
		cache:false,
		url: root_domain + crm_domain +'app/annexure/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {				
				toastr.success("ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + crm_domain +'annexure_detail_list';
			}
			else if(response.trim() == '2') {
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + crm_domain +'annexure_detail_list';
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			else if(response.trim() == '-1'){
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			Unloading();
			$('#annexure_add').trigger('reset');
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
			url: root_domain + crm_domain +'app/annexure/',
			data: { mode : "delete", eid : id },
			success: function(response)
			{
				console.log(response);
				if(response.trim() == "1") {
					toastr.success("DATA DELETE SUCCESSFULLY", "SUCCESS");
					load_annexure_datatable();
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
				}	
				Unloading();						
			}
		});	
	}
}
function load_annexure_datatable(){
	var branch_id = $("#branch_id").val();

	$("#annexure-datatable").dataTable({
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
		"sAjaxSource": root_domain + crm_domain +'app/annexure/',
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