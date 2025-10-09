$(document).ready(function() {
	item_so_detail_report();
});


function item_so_detail_report() {
	
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/item_wise_sales_order_without_rate_report/',
		data: { mode : "item_so_detail_report"},		
	   success: function(response)
		{
			if(response != "") {
				$('#item-so-detail-table').html(response);
				Unloading();
			}
		}
	});	
}