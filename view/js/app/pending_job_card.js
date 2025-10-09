//var datatable;
$(document).ready(function() {

	show_data();
	
});

function show_data() {
	var product_id = $('#product_id').val();
	var vender_id = $('#vender_id').val();
	//alert(st_type);
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/pending_job_card/',
		data: { mode : "generate_report",product_id:product_id,vender_id:vender_id},
		success: function(data){
			//console.log(data);
			//alert(data);
			$('#table_data').html(data);		 
			Unloading();
		}		 
	}); 
}
