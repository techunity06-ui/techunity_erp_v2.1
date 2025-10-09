$(document).ready(function() {
	 cust_so_detail_report();
});

function cust_so_detail_report() {
	var rep_date=$("#rep_date").val();
	//var cust_id=$("#cust_id").val();
	//, cust_id: cust_id
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + crm_domain +'app/quotation_detail_report/',
		data: { mode : "cust_so_detail_report", date: rep_date},		
	   success: function(response)
		{
			if(response != "") {
				$('#sales_order_detail').html(response);
				Unloading();
			}
		}
	});	
}