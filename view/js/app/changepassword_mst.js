
$(document).ready(function() {	
	
// validate vendor add form on keyup and submit
$("#changepassword_form").validate({
	rules: {
		old_pass: {
			required: true,
			minlength:5
		},
		new_pass: {
			required: true,
			minlength:5
		},
		confirm_pass: {
			required: true,
			minlength:5,
			equalTo:"#new_pass"
		}		
	},
	messages: {
		old_pass: {
			required: "Enter Old Password",
			minlength:"Minimum 5 Character"
		},
		new_pass: {
			required: "Enter New Password",
			minlength:"Minimum 5 Character"
		},
		confirm_pass: {
			required: "Enter New Password",
			minlength:"Minimum 5 Character",
			equalTo:"Same as New Password"
		}		
	}
}); 
});
$("#changepassword_form").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#changepassword_form").valid()) {
		return false;
	}		
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	
	$.ajax({
		cache:false,
		url: root_domain+'app/changepasswordmst/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			console.log(response);	
			response=response.trim();
			if(response == '1') {
				Unloading();				
				toastr.success("PASSWORD CHANGE SUCCESSFULLY", "SUCCESS")
				window.location=root_domain+'logout';				
			}
			else if(response == '2') {
				toastr.warning("OLD PASSWORD IS WRONG", "ERROR")
				Unloading();
			}
			else if(response == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(response == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			$('#changepassword_form').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
