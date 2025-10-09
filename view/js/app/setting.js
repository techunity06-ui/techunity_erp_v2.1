//var datatable;
$(document).ready(function() {

// validate vendor add form on keyup and submit
$("#a_add").validate({
	rules: {
		cmp_unique_id: {
			required: true
		},
		company_name: {
			required: true
		},
		address: {
			required: true,
			minlength: 15
		}
	},
	messages: {
		cmp_unique_id: {
			required: "Enter Company ID"
		},
		company_name: {
			required: "Enter Company Name"
		},
		address: {
			required: "Enter Address",
			minlength: "Your Description must consist of at least 15 characters"
		}
		
	}

});

	// Amish Soni Start 12-01-2021
	$("#crm_settings").validate({
		rules: {
			crm_auto_mail: {
				required: true			
			},
			//Amish Soni Start 16-03-2021
			quotation_print_content: {
				required: true
			}
			//Amish Soni End 16-03-2021
		},
		messages: {
			crm_auto_mail: {
				required: "Please Select Option"
			},
			//Amish Soni Start 16-03-2021
			quotation_print_content: {
				required: "Please Enter text"
			}
			//Amish Soni End 16-03-2021
		}
	});
	// Amish Soni End 12-01-2021
});

$("#a_add").on('submit',function(e) {
								 
	for (instance in CKEDITOR.instances) 
	{
    	CKEDITOR.instances[instance].updateElement();
	}	
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#a_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data=new FormData(this);
	$.ajax({
		cache:false,
		url: root_domain+'app/setting/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,	
		success: function(response)
		{
			//console.log(response);			
			if(response.trim() == 'update') {
				Unloading();
				toastr.success("UPDATE SUCCESSFULLY", "SUCCESS");		
				location.reload();
			}
			else if(response == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(response == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();				
			}
			else if(response == '-2')
			{
				toastr.warning("COMPANY ID ALREADY EXISTS", "ERROR");
				Unloading();
			}
			$('#a_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

// Amish Soni Start 12-01-2021
$("#crm_settings").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();

	if (!$("#crm_settings").valid()) {
		return false;
	}

	// Amish Soni Start 16-03-2021
	var quotation_print_content_editor = CKEDITOR.instances.quotation_print_content;
	var quotation_print_content_data = quotation_print_content_editor.getData();

	if(quotation_print_content_data == ''){
		toastr.warning("PLEASE ADD QUOTATION PRINT FIRST PAGE CONTENT", "WARNING");
		quotation_print_content_editor.focus();
		return false;
	}

	for (instance in CKEDITOR.instances)
	{
		CKEDITOR.instances[instance].updateElement();
	}
	// Amish Soni End 16-03-2021

	form.submitted = true;
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data = new FormData(this);

	$.ajax({
		cache:false,
		url: root_domain+'app/setting/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,	
		success: function(response)
		{
			//console.log(response);			
			if(response.trim() == 'update') {
				Unloading();
				window.location=root_domain+'setting/'+$("#eid").val();
				toastr.success("UPDATE SUCCESSFULLY", "SUCCESS");		
			}
			else if(response == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(response == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();				
			}
			$('#crm_settings').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
})
// Amish Soni End 12-01-2021
$("#company_configuration").on('submit',function(e) {
								 
	for (instance in CKEDITOR.instances) 
	{
    	CKEDITOR.instances[instance].updateElement();
	}	
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#company_configuration").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data=new FormData(this);
	$.ajax({
		cache:false,
		url: root_domain+'app/setting/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,	
		success: function(response)
		{
			//console.log(response);			
			if(response.trim() == 'update') {
				Unloading();
				toastr.success("UPDATE SUCCESSFULLY", "SUCCESS");		
				location.reload();
			}
			else if(response == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(response == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();				
			}
			else if(response == '-2')
			{
				toastr.warning("COMPANY ID ALREADY EXISTS", "ERROR");
				Unloading();
			}
			$('#company_configuration').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});