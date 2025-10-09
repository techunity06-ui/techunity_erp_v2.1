//var datatable;
$(document).ready(function() {
	fetch_resource_based_on_branch();
	// validate vendor add form on keyup and submit
	$("#resource_add").validate({
		rules: {
			resource_id:{
				required : true	
			},
			work_order_id:{
				required : true	
			},
			process_id:{
				required : true	
			},
			transfer_qty:{
				required : true	
			},
			new_resource_id:{
				required : true	
			}
		},
		messages: {
			resource_id:{
				required : "Select Resource Name"
			},
			work_order_id:{
				required : "Select Work Order No"
			},
			process_id:{
				required : "Select Process Name"
			},
			transfer_qty:{
				required : "Enter Transfer Qty"
			},
			new_resource_id:{
				required : "Select Resource Name"
			}
		}
	}); 
});

$("#resource_add").on('submit',function(e) {
	
	var form = this;
	e.preventDefault();
	e.stopPropagation();	
	if (!$("#resource_add").valid()) {
		return false;
	}
	for (instance in CKEDITOR.instances) 
	{
    	CKEDITOR.instances[instance].updateElement();
	}	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	$.ajax({
		cache:false,
		url: root_domain+'app/resource_transfer/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				toastr.success("RESOURCE TRANSFER SUCCESSFULLY", "SUCCESS");
				window.location.reload();
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(arr.msg == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();
			}
			else if(arr.msg== 'update')
			{	
				toastr.success("RESOURCE UPDATED SUCCESSFULLY", "SUCCESS");
				Unloading();
				window.location.reload();
				
			}
			$('#resource_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
});

 $(document).on('keydown', "input[type='number']", function(event){
    if (event.shiftKey == true) {
        event.preventDefault();
    }
    if ((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105) || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 || event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 190) {
    } else {
        event.preventDefault();
    }
    if($(this).val().indexOf('.') !== -1 && event.keyCode == 190)
        event.preventDefault();
});


function resourceselect(resourceid){
	//$("#work_order_id option:selected").prop("selected", false);
	$("#work_order_id").empty();
	$("#work_order_id").select2({
     	width: '100%'
    });

    //$("#process_id option:selected").prop("selected", false);
    $("#process_id").empty();
	$("#process_id").select2({
     	width: '100%'
    });

    $("#qty").val('0');
    $('.action_div').addClass('hide');

    var branch_id = $('#branch_id').val();

	if(resourceid!='' && branch_id!=''){
		Loading();
		$.ajax({
		type: "POST",
		url: root_domain+'app/resource_transfer/',
		data: { mode : "get_workorder_list", resourceid : resourceid, branch_id : branch_id},
		success: function(data){
				Unloading();
				var arr = jQuery.parseJSON(data);
				$('#work_order_id').empty().append(arr.work_order_id);
				$("#work_order_id").select2({
		         	width: '100%'
		        });	
				
			}		
		});	
	}
} 

function workorderselect(workorderid){

	$("#process_id").empty();
	$("#process_id").select2({
     	width: '100%'
    });
    $("#qty").val('0');
    $('.action_div').addClass('hide');
	if(workorderid!=''){
		var resource_id = $('#resource_id').val();
		Loading();
		$.ajax({
		type: "POST",
		url: root_domain+'app/resource_transfer/',
		data: { mode : "get_process_list", workorderid : workorderid , resource_id : resource_id},
		success: function(data){
				Unloading();
				var arr = jQuery.parseJSON(data);
				$('#process_id').empty().append(arr.process_id);
				$("#process_id").select2({
		         	width: '100%'
		        });	
				
			}		
		});
	}
}

function processselect(processid){
	var workorder_id = $("#work_order_id").val();
	var resource_id = $("#resource_id").val();
	if(resource_id=='' || resource_id==null) {
		toastr.warning("Please select resource name.", "WARNING");
		return false;
	}
	var request_id = $('#process_id option:selected').attr('data-requestid');
	var branch_id = $('#branch_id').val();
	$("#qty").val('0');
	if(processid!=''){
		
		//alert(processid);
		Loading();

		$.ajax({
		type: "POST",
		url: root_domain+'app/resource_transfer/',
		data: { mode : "get_request_info", processid:processid, resource_id: resource_id, request_id : request_id, branch_id : branch_id},
		success: function(data){
			/*//alert(data);	
			console.log(data);
			return false;*/
				Unloading();
				var arr = jQuery.parseJSON(data);
				if(arr.msg=='1'){
					$('#qty').val(arr.qty);
					$('#request_id').val(arr.request_id);
					$('#eid').val(arr.resource_allocate_id);
					$('.action_div').removeClass('hide');

					$('#new_resource_id').empty().append(arr.new_resource_id);
					$("#new_resource_id").select2({
			         	width: '100%'
			        });	
				}
				
			}		
		});
	}
}


$(document).on('keyup', '#transfer_qty', function(){
	var process_id = $("#process_id").val();
	
	if(process_id=='' || process_id==null ) {
		toastr.warning("Please select process name.", "WARNING");
		return false;
	}
	var trans_qty = $(this).val();
	var exist_qty = $('#qty').val();

	if(parseFloat(trans_qty) > parseFloat(exist_qty)){
		toastr.warning("Transfer qty should be less than existing qty.", "WARNING");
		$('#transfer_qty').attr('max', exist_qty);
		return false;
	}
});

function fetch_resource_based_on_branch() {
	var branch_id = $('#branch_id').val();
	if(branch_id!=''){
		Loading();
		$.ajax({
		type: "POST",
		url: root_domain+'app/resource_report/',
		data: { mode : 'fetch_resource_based_on_branch', branch_id : branch_id},
		success: function(data){
				var arr = jQuery.parseJSON(data);
				$('#resource_id').empty().append(arr.resource_id);
				$("#resource_id").select2({
		         	width: '100%'
		        });	
			}		
		});
		Unloading();
	}else{
		$('#resource_id').empty();
		$("#resource_id").select2({
         	width: '100%'
        });	
	}
}