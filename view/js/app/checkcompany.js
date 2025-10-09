//var datatable;
$(document).ready(function() {
		
$("#company_name").validate({
	rules: {
		company_name: {
			required: true			
		},
		password:{
			 minlength:5
		},
		c_password:{
			 minlength:5,
			equalTo:password
		}
	},
	messages: {
		company_name: {
			required: "Enter Company Name"
		},
		password:{
			 minlength:"Password Must be More than 5 character"
		},
		c_password:{
			 minlength:"Password Must be More than 5 character",
			equalTo:"Password Not Match"
		}
	
	}
}); 
});
