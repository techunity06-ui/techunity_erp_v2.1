//var datatable;
$(document).ready(function() {
	show_data();
});

function reload_data()
{
	//datatable.fnReloadAjax();
	show_data();
}	

function show_data() {
	var date=$('#rep_date').val();
	var branch_id=$('#branch_id').val();
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/pending_job_card_new/',
		data: { mode : "generate_report",date:date,branch_id:branch_id},
		success: function(data){
			//console.log(data);
			//alert(data);
			$('#table_data').html(data);		 
			Unloading();
		}		 
	}); 
}
