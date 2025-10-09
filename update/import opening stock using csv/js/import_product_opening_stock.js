//var datatable;
$(document).ready(function() {
	$("#import_customer").validate({
		rules: {
			excel_file: {
				required: true			
			}
		},
		messages: {
			excel_file: {
				required: "Select CSV File"			
			}
		}
	});
});

$("#import_customer").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	
	if (!$("#import_customer").valid()) {
		return false;
	}
	
	form.submitted = true;	
	Loading();	
	$(this).attr("disabled","disabled");		
	var token	=  $("#token").val();	
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+'app/import_product_opening_stock/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{	
			console.log(response);
			var data = JSON.parse(response);
			var response=data.res;
			Unloading();
			if(response == '1') {
				$('#msg').html('<span style="color:green">Data Cheked Successfully</span>');
				$('#check_button').hide();
				$('#mode').val('import_data');
				$('#import_button').show();
			}
			else if(response == '-1')
			{
				toastr.info("SELECT WRONG FILE", "INFO")
				$('#import_customer').trigger('reset');
				Unloading();				
			}
			else if(response == '0')
			{
				$('#msg').html('<span style="color:red"> Coloums Does Not Match Please Check With demo File</span>');
				$('#import_customer').trigger('reset');
				Unloading();				
			}
			else if(response == '3')
			{
				$('#msg').html('<span style="color:red"> Coloum Name Does Not Match Please Check With demo File</span>');
				$('#import_customer').trigger('reset');
				Unloading();				
			}
			else if(response == '4')
			{
				toastr.success("CUSTOMER IMPORT SUCCESSFULLY", "SUCCESS");
				window.location=root_domain+'customer_list';		
				$('#import_customer').trigger('reset');
				Unloading();				
			}
			else if(response == '5')
			{
				$('#import_customer').trigger('reset');
				 $('#check_button').show();
				$('#mode').val('check_data');
				$('#import_button').hide();
				show_importedcust_data();
				Unloading();				
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function show_importedcust_data(total)
{
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+'app/import_product_opening_stock/',
	data: { mode : "show_importedcustdata"},
	success: function(responce){
				console.log(responce);
				Unloading();
				 $('#imported_data_section').show();
				$('#temp_custdata').html(responce);
				 
			}
	});
				
}