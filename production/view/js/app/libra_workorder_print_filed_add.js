//var datatable;
$(document).ready(function() {
	 
});

$("#workorder_data_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();
	if (!$("#workorder_data_add").valid()) {
		return false;
	}
	
	form.submitted = true;
	Loading(true);
	$(this).attr("disabled","disabled");		
	$('#save').attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	//console.log(form_data);
	$.ajax({
		cache:false,
		url: root_domain+production_domain+'app/libra_workorder_print_filed_add/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			//console.log(response);	
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("WORKORDER DATA ADDED SUCCESSFULLY", "SUCCESS");
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			$('#save').removeAttr("disabled");

		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
