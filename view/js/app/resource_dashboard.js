//var datatable;
$(document).ready(function() {
	show_data();
	//fetch_employee_based_on_branch();
	fetch_resource_based_on_branch();
});

function show_data() {
	var resource_id = $('#resource_id').val();
	var branch_id = $('#branch_id').val();
	var date = $('#start_date').val();
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/resource_dashboard/',
		data: { mode : "generate_report", resource_id:resource_id, date:date, branch_id : branch_id},
		success: function(data){
			if(resource_id!='' && resource_id!=null){
				$resource_name = $( "#resource_id option:selected" ).text();
				$('.entered_data_info').removeClass('hide');
				$('#resource_name_label').html($resource_name);
				$('#date_label').html(date);
				var resource_report_url = root_domain+'resource_report/'+resource_id;
				$("#check_work_report").attr("href", resource_report_url);
			}else{
				$('.entered_data_info').addClass('hide');
				$("#check_work_report").attr("href", 'javascript:void(0)');
			}
			//alert(data);
			$('#table_data').html(data);		 
			Unloading();
		}		 
	}); 
}

function fetch_resource_based_on_branch() {
	var branch_id = $('#branch_id').val();
	if(branch_id!=''){
		var resource_id = $('#resource_id').val();
		Loading();
		$.ajax({
		type: "POST",
		url: root_domain+'app/resource_dashboard/',
		data: { mode : 'fetch_resource_based_on_branch', branch_id : branch_id, resource_id : resource_id},
		success: function(data){
				var arr = jQuery.parseJSON(data);
				$('#resource_id').empty().append(arr.vendor_id);
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