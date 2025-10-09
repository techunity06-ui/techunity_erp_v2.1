//var datatable;
$(document).ready(function() {
	generate_customer_sales_summary_data();
});

function generate_customer_sales_summary_data(){
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+finance_root_domain+'app/customerwisesalessummary/',
		data: { mode : 'customer_wise_sales_summary'},
		success: function(response)
		{
			if(response != "") {
				$('#adv-table').html(response);
				Unloading();
			}
		}
	});	
}