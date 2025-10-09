//var datatable;
$(document).ready(function() {
	load_job_work_data();
});


function load_job_work_data(){
	var sales_order_id = $('#sales_order_id').val();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/job_work_order/',
		data: { mode : "load_job_work_data", sales_order_id:sales_order_id},
		success: function(resp){
			$('#print1').html(resp);
		}		
	});	
}