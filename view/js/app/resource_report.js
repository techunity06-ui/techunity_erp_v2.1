//var datatable;
$(document).ready(function() {
	fetch_resource_based_on_branch();

	var resource_id = $('#resource_id').val();
	if(resource_id!='' && resource_id!=null){
		$("#save").click();
	}
	// validate vendor add form on keyup and submit
	$("#resource_add").validate({
		rules: {
			resource_id:{
				required : true	
			}
		},
		messages: {
			resource_id:{
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
	
	form.submitted = true;	
	Loading(true);	
	$(this).attr("disabled","disabled");		
	
	var form_data=new FormData(this);
	$.ajax({
		cache:false,
		url: root_domain+'app/resource_report/',
		type: "POST",
		data: form_data,
		contentType: false,
		processData:false,
		success: function(response)
		{
			var arr = jQuery.parseJSON(response);
			if(arr.msg == '1') {
				Unloading();
				$('#resource_report_div').empty().append(arr.data);
				//window.location.reload();
			}
			else if(arr.msg == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			//$('#resource_add').trigger('reset');	
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});
	
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