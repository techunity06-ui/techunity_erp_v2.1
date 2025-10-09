//var datatable;
$(document).ready(function() {
	generate_report();
});	

function generate_report(){
	var date=$('#rep_date').val();
	var cust_id=$('#cust_id').val();
	
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/iso_inquiry_report/',
		data: { mode : "generate_report", date:date, cust_id: cust_id },
		success: function(response)
		{
			var resp=JSON.parse(response);
			$('#adv-table').html(resp.html_resp);
			Unloading();							
		}
	});	
}