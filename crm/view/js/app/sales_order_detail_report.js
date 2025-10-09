$(document).ready(function() {
	 cust_so_detail_report();
});

function cust_so_detail_report() {
	var rep_date=$("#rep_date").val();
	var cust_id=$("#cust_id").val();
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/sales_order_detail_report/',
		data: { mode : "cust_so_detail_report", date: rep_date, cust_id: cust_id},		
	   success: function(response)
		{
			if(response != "") {
				$('#sales_order_detail').html(response);
				Unloading();
			}
		}
	});	
}