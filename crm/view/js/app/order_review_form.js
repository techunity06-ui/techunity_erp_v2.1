$("#order_review_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	$.ajax({
		cache:false,
		url: root_domain+crm_domain+'app/order_review_form/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{

			
			//return false;
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				Unloading();
				toastr.success("ADDED SUCCESSFULLY", "SUCCESS");
				//window.location=root_domain+purchase_domain+'qc_done_list';
				//alert(arr.back);
				window.location=root_domain+crm_domain+arr.back;
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO")
				Unloading();				
			}
			else if(arr.msg == '2')
			{	
				toastr.success("UPDATED SUCCESSFULLY", "SUCCESS");		
				Unloading();
				window.location=root_domain+crm_domain+arr.back;
			}
			//Unloading();
			$('#qc_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function order_review_submit(){
	$("#order_review_add").submit();
}