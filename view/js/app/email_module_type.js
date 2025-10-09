var datatable;
$(document).ready(function() {
		datatable = $("#dynamic-table").dataTable({
			"bAutoWidth" : false,
			"bFilter" : true,
			"bSort" : true,
			"bProcessing": true,
			"bServerSide" : true,
			"oLanguage": {
					"sLengthMenu": "_MENU_",
					"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
					"sEmptyTable": "NO EMAIL TYPE ADDED YET !",
			},
			"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
			"iDisplayLength": 10,
			"sAjaxSource": root_domain+'app/email_module_type/',
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
	// validate the comment form when it is submitted        

// validate vendor add form on keyup and submit
$("#email_type_add").validate({
	rules: {
		email_template_name: {
			required: true
		},
		module_id: {
			required: true
		}	
	},
	messages: {
		email_template_name: {
			required: "Enter Email Type"
		},
		module_id: {
			required: "Select Module"			
		}
	}
}); 
// validate vendor edit form on keyup and submit
$("#FormEditEmail").validate({
	rules: {
		edit_email_template_name: {
			required: true
		},
		edit_module_id: {
			required: true			
		}
	},
	messages: {
		edit_email_template_name: {
			required: "Enter Email Type"
		},		
		edit_module_id: {
			required: "Select Module"
		}
	}
});		

});
$("#email_type_add").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#email_type_add").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var token=  $("#token").val();	
	var module_id= $("#module_id").val();	
	var form_data = {
		module_id: $("#module_id").val(),
		email_template_name: $("#email_template_name").val(),		
		model: $("#model").val(),		
		token:token,
		mode:$("#mode").val(),
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/email_module_type/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			var obj=jQuery.parseJSON(response);
			response=obj.res;
			if(response.trim() == '1') {
				toastr.success("EMAIL TYPE ADDED SUCCESSFULLY", "SUCCESS")
				Unloading();
				datatable.fnReloadAjax();
			}
			else if(response.trim() == '2') {
				toastr.success("EMAIL TYPE ADDED SUCCESSFULLY", "SUCCESS");
				$("#bs-example-modal-city").modal("hide");
				/*$('#cityid').append('<option value='+obj.cityid+'>'+obj.city_name+'</option>');
				$("#cityid").trigger('change')
				$('#cityid').select2("val",obj.cityid);*/
				$('#email_type_add').trigger('reset');
				Unloading();
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response.trim() == '-1')
			{
				$("#bs-example-modal-city").modal("hide");
				toastr.info("ALREADY EXISTS", "INFO")
				$("#bs-example-modal-city").modal("hide");
				$('#email_type_add').trigger('reset');
				Unloading();
			}
			$('#email_type_add').trigger('reset');	
			$('#module_id').select2("val",module_id);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
//var editReq = null;
$("#FormEditEmail").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#FormEditEmail").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = {
		eid :$("#edit_id").val(),
		module_id: $("#edit_module_id").val(),
		email_template_name: $("#edit_email_template_name").val(),		
		token:$("#edit_token").val(),
		mode:'edit',
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+'app/email_module_type/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			
			if(response.trim() == '1') {
				toastr.success("EMAIL TYPE UPDATED SUCCESSFULLY", "SUCCESS");
				datatable.fnReloadAjax();
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
function delete_catalog(id) 
{
	var r= confirm(" Are you want to delete ?");
		if(r) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain+'app/email_module_type/',
				data: { mode : "delete", token :  $("#token").val(), eid : id },
				success: function(response)
				{
					
					if(response.trim() == "1") {
						toastr.success("EMAIL TYPE DELETE SUCCESSFULLY", "SUCCESS");
						datatable.fnReloadAjax();
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
			url: root_domain+'app/email_module_type/',
			data: { mode : "preedit", id : id },
			success: function(response)
			{
				var obj = jQuery.parseJSON(response);
				$("#ModalEditAccount").modal("show");
				$("#edit_id").val(id);								
				$("#edit_email_template_name").val(obj.email_template_name);
				$("#edit_module_id").select2("val",obj.module_id);				
				Unloading();
			}
		});	
}
