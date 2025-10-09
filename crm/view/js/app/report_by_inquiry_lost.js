//var datatable;
$(document).ready(function() {
	// product_load();
	generate_inquiry_lost_report();
});

function generate_inquiry_lost_report(){
	var rep_date = $('#rep_date').val();
	var cust_id = $('#cust_id').val();
	var user_id = $('#user_id').val();
	var reason_id = $('#reason_id').val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/report_by_inquiry_lost/',
		data: { mode : 'generate_inquiry_lost_report', date : rep_date, cust_id: cust_id, user_id:user_id, reason_id:reason_id},
		success: function(response)
		{
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
		}
	});	
}