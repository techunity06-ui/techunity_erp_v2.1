//var datatable;
$(document).ready(function() {
	generate_customer_sales_order_summary_data();
});

function generate_customer_sales_order_summary_data(){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+crm_domain+'app/customerwisesalesordersummary/',
		data: { mode : 'customer_wise_sales_order_summary'},
		success: function(response)
		{
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
		}
	});	
}