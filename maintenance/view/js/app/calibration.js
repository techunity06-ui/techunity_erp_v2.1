$(document).ready(function() {
	// load_maintenance_datatable();
	//show_data();
	// product_load();
	// validate vendor add form on keyup and submit
	$("#calibration_add").validate({
		rules: {
			calibration_req_no: {
				required: true
			},
			calibration_req_date: {
				required: true
			},
			cust_id: {
				required: true
			},
			bill_no: {
				required: true
			},
			bill_date: {
				required: true
			},
			amount: {
				number:true,
				required:true
			}
		},
		messages: {
			calibration_req_no: {
				required: "Enter Maintenance No"
			},
			calibration_req_date: {
				required: "Enter Maintenance Date"
			},
			cust_id: {
				required: "Choose Customer"
			},
			bill_no: {
				required: "Enter Bill no"
			},
			bill_date: {
				required: "Enter Bill date"
			},
			amount: {
				number:"Enter Only number",
				required: "Enter Price"
			}
		}
	}); 
}); 

$("#calibration_add").on('submit',function(e) {
	// var maintenance_id = $('#eid').val();
	// var inq_product_required = $('#inq_product_required').val();
	// var product = check_product(inq_id);
	// if(inq_product_required == '1'){
	// 	if(product === false){		
	// 		toastr.warning("Add Product Please!!", "ERROR");
	// 		$("#product_id").select2('focus');
	// 		return false;
	// 	}
	// }
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#calibration_add").valid()) {
		return false;
	} 
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	$('#save').prop('disabled', true);
	var form_data=new FormData(this);	
	
	//Hide Form Submit Alert
	setFormSubmitting();
	
	$.ajax({
		cache:false,
		url: root_domain + maintenance_domain + 'app/calibration/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);			
			if(arr.msg == '1') {
				toastr.success("CALIBRATION ADDED SUCCESSFULLY", "SUCCESS");
				window.location=root_domain + maintenance_domain + 'maintenance_list';
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
			}
			else if(arr.msg == '-1') {
				toastr.info("ALREADY EXISTS", "INFO");
			}
			else if(arr.msg == 'update') {	
				toastr.success("CALIBRATION UPDATED SUCCESSFULLY", "SUCCESS");		
				window.location = root_domain + maintenance_domain + 'maintenance_list';	
			}
			Unloading();
			$('#calibration_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

function get_series_no(){
	
	$.ajax({
		type: "POST",
		url: root_domain + maintenance_domain + 'app/calibration/',
		data: { mode : "get_series_no"},
		success: function(resp){
			var no = jQuery.parseJSON(resp);
				$('#calibration_req_no').val(no.series_no);
			}		
		});	
}