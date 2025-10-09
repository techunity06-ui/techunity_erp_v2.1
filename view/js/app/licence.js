//var datatable;
$(document).ready(function(){
	//alert("fd");
$("#licence_add").validate({
	rules: {
		cust_key: {
			required: true			
		}
	},
	messages: {
		cust_key: {
			required: "Enter Customer Key"
		}
	}
}); 
});
$("#licence_add").on('submit',function(e) {
	//alert("df");
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#licence_add").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+'app/licence/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);
			if(arr.msg == 'valid') {
				Unloading();
				//toastr.success("ORDER ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+arr.back;
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(arr.msg == 'licence')
			{
				toastr.info("Wrong Key", "INFO");
				window.location=root_domain+arr.back;
				Unloading();
			}
			else if(arr.msg== 'update')
			{	
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location=root_domain+arr.back;
				
			}
			$('#licence_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
