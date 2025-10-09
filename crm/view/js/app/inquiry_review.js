$("#inquiry_reviewed_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#inquiry_reviewed_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	var form_data=new FormData(this);
	$.ajax({
		cache:false,
		url: root_domain+crm_domain+'app/inquiry_review/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,	
		success: function(response)
		{
			console.log(response);			
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
			$('#inquiry_reviewed_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});	
});