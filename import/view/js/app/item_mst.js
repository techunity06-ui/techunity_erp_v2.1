$(document).ready(function() {
	$("#product_importfile").validate({
		rules: {
			excel_file:{
				required:true
			}
		},
		messages: {
			excel_file: {
				required: "Select Product Csv file",
			}
		}
	});
});

$("#product_importfile").on('submit',function(e) {
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#product_importfile").valid()) {
		return false;
	}
	form.submitted = true;	
	Loading();	
	$(this).attr("disabled","disabled");		
	var form_data=new FormData(this);	
	$.ajax({
		cache:false,
		url: root_domain+import_domain+'app/item_mst/',
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
				$('#product_importfile').trigger('reset');
				Unloading();				
			}
			else if(response == '0')
			{
				$('#msg').html('<span style="color:red"> Coloums Does Not Match Please Check With demo File</span>');
				$('#product_importfile').trigger('reset');
				Unloading();				
			}
			else if(response == '3')
			{
				$('#msg').html('<span style="color:red"> Coloum Name Does Not Match Please Check With demo File</span>');
				$('#product_importfile').trigger('reset');
				Unloading();				
			}
			else if(response == '4')
			{
				$('#msg').html('<span style="color:green"> Data Import Successfully</span>');
				$('#product_importfile').trigger('reset');
				Unloading();				
			}
			else if(response == '5')
			{
				$('#product_importfile').trigger('reset');
				$('#check_button').show();
				$('#mode').val('check_data');
				$('#import_button').hide();
				show_importedproduct_data();
				Unloading();				
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});
function show_importedproduct_data(total)
{
	Loading();
	$.ajax({
	type: "POST",
	url: root_domain+import_domain+'app/item_mst/',
	data: { mode : "show_importedcustdata"},
	success: function(responce){
			console.log(responce);
			Unloading();
			$('#sampledata_show').show();
			$('#temp_productdata').html(responce);
		}
	});
				
}